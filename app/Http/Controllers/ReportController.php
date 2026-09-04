<?php

namespace App\Http\Controllers;

use App\Services\ReportService;
use App\Models\Contributor;
use App\Models\Order;
use App\Exports\ContributorBalanceExport;
use App\Exports\CustomerBalanceExport;
use App\Exports\SupplierBalanceExport;
use App\Exports\MonthlyProfitExport;
use App\Exports\AnnualProfitExport;
use App\Exports\InventoryStatusExport;
use App\Exports\InventoryMovementsExport;
use App\Exports\PayrollExport;
use App\Exports\CreditDueExport;
use App\Exports\EquipmentCostExport;
use App\Exports\TreasuryExport;
use App\Exports\RentalShiftsExport;
use App\Exports\OrdersExport;
use App\Exports\SchedulesExport;
use App\Exports\GeneralReportExport;
use App\Exports\NeighboringStationsExport;
use App\Exports\TrialBalanceExport;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    public function __construct(private ReportService $reportService) {}

    public function index()
    {
        return view('reports.index');
    }

    public function customerBalance(Request $request)
    {
        if ($request->export === 'excel') {
            return $this->exportCustomerBalance($request);
        }

        if ($request->customer_id) {
            $customer = \App\Models\Customer::findOrFail($request->customer_id);
            $fromDate = $request->from_date ?? now()->startOfMonth()->toDateString();
            $toDate   = $request->to_date ?? now()->toDateString();

            // Filtered data for the selected period (exclude cancelled orders)
            $orders = $customer->orders()
                ->where('status', '!=', 'cancelled')
                ->whereBetween('delivery_date', [$fromDate, $toDate])
                ->orderBy('delivery_date')
                ->get();

            $payments = $customer->payments()
                ->whereBetween('payment_date', [$fromDate, $toDate])
                ->orderBy('payment_date')
                ->get();

            // Direct treasury transactions for customer (not linked through customer_payment)
            $directTreasuryTxs = \App\Models\TreasuryTransaction::where('reference_type', 'customer')
                ->where('reference_id', $customer->id)
                ->whereBetween('transaction_date', [$fromDate, $toDate])
                ->orderBy('transaction_date')
                ->get();

            // Get paid credits for this customer in the period
            $paidCredits = \App\Models\Credit::where('creditable_type', 'customer')
                ->where('creditable_id', $customer->id)
                ->where('status', 'paid')
                ->whereBetween('paid_date', [$fromDate, $toDate])
                ->orderBy('paid_date')
                ->get();

            // Calculate totals
            $totalOrders = $orders->sum('total_amount');
            $totalCashOrders = $orders->sum('cash_amount');
            $totalCreditPayments = $paidCredits->sum('amount');
            
            $incomingPaymentsSum = $payments->where('amount', '>', 0)->sum('amount') 
                                 + $directTreasuryTxs->where('type', 'in')->sum('amount') 
                                 + $totalCreditPayments;
                                 
            $outgoingPaymentsSum = abs($payments->where('amount', '<', 0)->sum('amount')) 
                                 + $directTreasuryTxs->where('type', 'out')->sum('amount');

            $totalPayments = $incomingPaymentsSum + $totalCashOrders;
            $filteredBalance = ($totalOrders - $totalCashOrders) + $outgoingPaymentsSum - $incomingPaymentsSum;

            // Build chronological event list
            $events = collect();

            foreach ($orders as $order) {
                $events->push((object)[
                    'date'            => $order->delivery_date,
                    'type'            => 'order',
                    'model'           => $order,
                    'description'     => 'طلب #' . $order->id . ' - ' . ($order->concrete_mix?->name ?? $order->concrete_type_label),
                    'total_amount'    => (float)($order->total_amount ?? 0),
                    'cash_amount'     => (float)($order->cash_amount ?? 0),
                    'quantity_m3'     => (float)($order->quantity_m3 ?? 0),
                    'unit_price'      => (float)($order->unit_price ?? 0),
                    'cement_deducted' => (float)($order->cement_deducted ?? 0),
                ]);
            }

            foreach ($payments as $payment) {
                $amt = (float)$payment->amount;
                if ($amt < 0) {
                    $events->push((object)[
                        'date'            => $payment->payment_date,
                        'type'            => 'outgoing_payment',
                        'model'           => $payment,
                        'description'     => 'سداد ديون (صادر) - ' . ($payment->notes ?: $payment->payment_method),
                        'amount'          => abs($amt),
                    ]);
                } else {
                    $events->push((object)[
                        'date'            => $payment->payment_date,
                        'type'            => 'payment',
                        'model'           => $payment,
                        'description'     => $payment->notes ?: ('دفعة ' . $payment->payment_method),
                        'amount'          => $amt,
                    ]);
                }
            }

            foreach ($directTreasuryTxs as $tx) {
                if ($tx->type === 'out') {
                    $events->push((object)[
                        'date'            => $tx->transaction_date,
                        'type'            => 'outgoing_payment',
                        'model'           => $tx,
                        'description'     => '' . ($tx->description ?: 'سداد ديون'),
                        'amount'          => (float)$tx->amount,
                    ]);
                } else {
                    $events->push((object)[
                        'date'            => $tx->transaction_date,
                        'type'            => 'payment',
                        'model'           => $tx,
                        'description'     => '' . ($tx->description ?: 'دفعة عميل'),
                        'amount'          => (float)$tx->amount,
                    ]);
                }
            }

            foreach ($paidCredits as $paidCredit) {
                $events->push((object)[
                    'date'            => $paidCredit->paid_date,
                    'type'            => 'credit_payment',
                    'model'           => $paidCredit,
                    'description'     => 'سداد آجل - ' . ($paidCredit->notes ?? 'دفعة آجلة'),
                    'amount'          => (float)$paidCredit->amount,
                ]);
            }

            // Sort chronologically
            $sortedEvents = $events->sortBy(fn($e) => \Carbon\Carbon::parse($e->date)->timestamp)->values();

            // Compute running balance
            $transactions = collect();
            $runningBalance = 0;
            $runningCementBalance = (float)$customer->cement_balance + $orders->sum('cement_deducted');
            $cementBalanceStart = $runningCementBalance;

            foreach ($sortedEvents as $ev) {
                if ($ev->type === 'order') {
                    $creditAmount = $ev->total_amount - $ev->cash_amount;
                    $runningBalance += $creditAmount;
                    $runningCementBalance -= $ev->cement_deducted;

                    $transactions->push((object)[
                        'date'            => $ev->date,
                        'description'     => $ev->description,
                        'debit'           => $ev->total_amount,
                        'cash_paid'       => $ev->cash_amount,
                        'order_price'     => $creditAmount,
                        'credit'          => 0,
                        'running_balance' => $runningBalance,
                        'quantity_m3'     => $ev->quantity_m3,
                        'unit_price'      => $ev->unit_price,
                        'cement_deducted' => $ev->cement_deducted,
                        'running_cement'  => $runningCementBalance,
                        'type'            => 'order',
                    ]);
                } elseif ($ev->type === 'outgoing_payment') {
                    $runningBalance += $ev->amount;

                    $transactions->push((object)[
                        'date'            => $ev->date,
                        'description'     => $ev->description,
                        'debit'           => $ev->amount,
                        'cash_paid'       => 0,
                        'order_price'     => $ev->amount,
                        'credit'          => 0,
                        'running_balance' => $runningBalance,
                        'quantity_m3'     => 0,
                        'unit_price'      => 0,
                        'cement_deducted' => 0,
                        'running_cement'  => $runningCementBalance,
                        'type'            => 'outgoing_payment',
                    ]);
                } else { // payment or credit_payment
                    $runningBalance -= $ev->amount;

                    $transactions->push((object)[
                        'date'            => $ev->date,
                        'description'     => $ev->description,
                        'debit'           => 0,
                        'cash_paid'       => 0,
                        'order_price'     => 0,
                        'credit'          => $ev->amount,
                        'running_balance' => $runningBalance,
                        'quantity_m3'     => 0,
                        'unit_price'      => 0,
                        'cement_deducted' => 0,
                        'running_cement'  => $runningCementBalance,
                        'type'            => $ev->type,
                    ]);
                }
            }

            $totalQuantityM3 = $orders->sum('quantity_m3');
            $totalCementDeducted = $orders->sum('cement_deducted');

            return view('reports.customer-balance', compact('customer', 'transactions', 'totalOrders', 'totalPayments', 'filteredBalance', 'fromDate', 'toDate', 'totalQuantityM3', 'totalCementDeducted', 'cementBalanceStart'));

        }

        $data = $this->reportService->customerBalanceReport($request->from_date, $request->to_date);
        return view('reports.customer-balance', compact('data'));
    }

    public function supplierBalance(Request $request)
    {
        // Handle single supplier detailed report with export
        if ($request->supplier_id) {
            $supplier = \App\Models\Supplier::findOrFail($request->supplier_id);
            $fromDate = $request->from_date ?? now()->startOfMonth()->toDateString();
            $toDate   = $request->to_date ?? now()->toDateString();

            // Check if export requested
            if ($request->export === 'excel') {
                return Excel::download(
                    new SupplierBalanceExport($supplier->id, $fromDate, $toDate),
                    "supplier-balance-{$supplier->name}-" . now()->format('Y-m-d') . '.xlsx'
                );
            }

            // Filtered data for the selected period
            $purchases = $supplier->purchases()
                ->with(['items.inventoryItem'])
                ->whereBetween('purchase_date', [$fromDate, $toDate])
                ->orderBy('purchase_date')
                ->get();

            $payments = $supplier->payments()
                ->whereBetween('payment_date', [$fromDate, $toDate])
                ->orderBy('payment_date')
                ->get();

            // Get deductions for this supplier
            $deductions = $supplier->payments()
                ->where('payment_type', 'deduction')
                ->whereBetween('payment_date', [$fromDate, $toDate])
                ->orderBy('payment_date')
                ->get();

            // Get rental shifts for cars belonging to this supplier
            $rentalShifts = \App\Models\RentalShift::whereHas('contract', function($q) use ($supplier) {
                $q->where('supplier_id', $supplier->id);
            })
            ->whereBetween('shift_date', [$fromDate, $toDate])
            ->with('contract')
            ->orderBy('shift_date')
            ->get();

            // Get paid credits for this supplier in the period
            $paidCredits = \App\Models\Credit::where('creditable_type', 'supplier')
                ->where('creditable_id', $supplier->id)
                ->where('status', 'paid')
                ->whereBetween('paid_date', [$fromDate, $toDate])
                ->orderBy('paid_date')
                ->get();

            // Get stock-in movements for this supplier in the period
            $stockInMovements = \App\Models\InventoryMovement::where('supplier_id', $supplier->id)
                ->where('type', 'in')
                ->whereBetween('movement_date', [$fromDate, $toDate])
                ->with('item')
                ->orderBy('movement_date')
                ->get();

            // Summary totals for the filtered period
            $totalPurchases = $purchases->sum('total_amount');
            $totalPaymentsOnly = $payments->where('payment_type', '!=', 'deduction')->sum('amount');
            $totalDeductions = $deductions->sum('amount');
            $totalRentalShifts = $rentalShifts->sum('total_cost');
            $totalCashPurchases = $purchases->sum('cash_amount');  // Cash paid during purchases
            $totalCreditPayments = $paidCredits->sum('amount');  // Paid credits
            $totalStockIn = $stockInMovements->sum('total_cost');  // Stock-in total
            $totalPayments = $totalPaymentsOnly + $totalCashPurchases + $totalCreditPayments;  // Include cash and credit payments
            
            // Balance calculation:
            if ($totalRentalShifts > 0) {
                // For suppliers with cars: ورديات السيارات - (الخصومات + إجمالي المدفوعات)
                $filteredBalance = $totalRentalShifts - ($totalDeductions + $totalPayments);
            } else {
                // For suppliers without cars:
                // + purchases (we owe them)
                // + stock-in (we owe them for materials)
                // - cash purchases (paid immediately)
                // - payments (we paid them)
                // + deductions (we took money back, increases what we owe or they owe us)
                $filteredBalance = $totalPurchases + $totalStockIn - $totalCashPurchases - $totalPaymentsOnly - $totalCreditPayments + $totalDeductions;
            }

            $transactions = collect();
            $runningBalance = 0;

            // Group stock-in movements by purchase/invoice if relation exists
            $stockInGrouped = $stockInMovements->groupBy(function ($stockIn) {
                return $stockIn->purchase_id ?? null;
            });

            foreach ($purchases as $purchase) {

                $creditAmount = $purchase->total_amount - $purchase->cash_amount;
                $runningBalance += $creditAmount;

                // Get stock items belonging to this invoice
                $invoiceStock = $stockInGrouped->get($purchase->id, collect());

                $transactions->push((object)[
                    'date'            => $purchase->purchase_date,
                    'description'     => 'مشتريات إذن ' . ($purchase->invoice_number ?? $purchase->id),
                    'debit'           => $purchase->cash_amount,
                    'credit'          => $purchase->total_amount,
                    'running_balance' => $runningBalance,
                    'tx_type'         => 'purchase',
                    'purchase_items'  => $purchase->items,
                    'invoice_stock'   => $invoiceStock,
                    'has_stock'       => $invoiceStock->count() > 0 || ($purchase->items && $purchase->items->count() > 0),
                ]);
            }

            foreach ($payments as $payment) {
                if ($payment->payment_type === 'deduction') {
                    continue; // Skip deductions here, handle separately
                }
                $runningBalance -= $payment->amount;  // Subtract payment
                $transactions->push((object)[
                    'date'            => $payment->payment_date,
                    'description'     => 'دفعة للمورد - ' . $payment->payment_method,
                    'debit'           => $payment->amount,
                    'credit'          => 0,
                    'running_balance' => $runningBalance,
                    'tx_type'         => 'payment',
                ]);
            }

            foreach ($deductions as $deduction) {
                $runningBalance += $deduction->amount;  // Add deduction (taking money back increases balance)
                $transactions->push((object)[
                    'date'            => $deduction->payment_date,
                    'description'     => 'خصم من المورد - ' . ($deduction->notes ?? ''),
                    'debit'           => 0,
                    'credit'          => $deduction->amount,
                    'running_balance' => $runningBalance,
                    'tx_type'         => 'deduction',
                ]);
            }

            // Add paid credits as payment transactions
            foreach ($paidCredits as $paidCredit) {
                $runningBalance -= $paidCredit->amount;  // Subtract credit payment
                $transactions->push((object)[
                    'date'            => $paidCredit->paid_date,
                    'description'     => 'سداد آجل للمورد - ' . ($paidCredit->notes ?? 'دفعة آجلة'),
                    'debit'           => $paidCredit->amount,
                    'credit'          => 0,
                    'running_balance' => $runningBalance,
                    'tx_type'         => 'credit_payment',
                ]);
            }

            // Add rental shifts but DON'T affect running balance
            foreach ($rentalShifts as $shift) {
                $transactions->push((object)[
                    'date'            => $shift->shift_date,
                    'description'     => 'وردية سيارة: ' . $shift->contract->equipment_name . ' (' . ($shift->contract->car_number ?? '') . ') - ' . $shift->hours . ' ساعة',
                    'debit'           => 0,
                    'credit'          => $shift->total_cost,
                    'running_balance' => null,  // No balance for rental shifts
                    'tx_type'         => 'rental_shift',
                    'shift_details'   => $shift,
                ]);
            }

            $transactions = $transactions->sortBy('date')->values();

            return view('reports.supplier-balance', compact('supplier', 'transactions', 'totalPurchases', 'totalPayments', 'totalDeductions', 'totalRentalShifts', 'totalStockIn', 'filteredBalance', 'fromDate', 'toDate'));
        }

        // All suppliers summary
        if ($request->export === 'excel') {
            return $this->exportSupplierBalance($request);
        }

        $data = $this->reportService->supplierBalanceReport($request->from_date, $request->to_date);
        return view('reports.supplier-balance', compact('data'));
    }

    public function inventoryStatus(Request $request)
    {
        if ($request->export === 'excel') {
            return $this->exportInventory();
        }

        $data = \App\Models\InventoryItem::orderBy('name_ar')->get();
        return view('reports.inventory', compact('data'));
    }

    public function treasury(Request $request)
    {
        if ($request->export === 'excel') {
            return $this->exportTreasury($request);
        }

        // Use full month by default to match monthly profit report
        $from = $request->from_date ?? now()->startOfMonth()->toDateString();
        $to   = $request->to_date ?? now()->endOfMonth()->toDateString();

        // Check if we should show order-related transactions (default is unchecked/false)
        $showOrderRelated = $request->boolean('show_order_related', false);

        // Get category filter
        $categoryFilter = $request->category;

        $query = \App\Models\TreasuryTransaction::query()
            ->whereBetween('transaction_date', [$from, $to])
            ->when($request->type, fn($q, $v) => $q->where('type', $v))
            ->when(!$showOrderRelated, function($q) {
                // Exclude order-related transactions: tax provisions and material costs
                $q->where(function($query) {
                    $query->where('description', 'NOT LIKE', '(أخرى) مخصص ضرائب%')
                            ->where('category', '!=', 'material_cost');
                });
            })
            ->when($categoryFilter && $categoryFilter !== 'all', function($q) use ($categoryFilter) {
                // Check if this category exists in the expenses table
                $isExpenseCategory = \App\Models\Expense::where('category', $categoryFilter)->exists();
                
                if ($isExpenseCategory) {
                    // Filter by expense reference with matching category OR description starting with category
                    $q->where(function($query) use ($categoryFilter) {
                        $query->where(function($subQuery) use ($categoryFilter) {
                            // Option 1: Has expense reference with matching category
                            $subQuery->where('reference_type', 'expense')
                                     ->whereHas('expense', function($expenseQuery) use ($categoryFilter) {
                                         $expenseQuery->where('category', $categoryFilter);
                                     });
                        })->orWhere(function($subQuery) use ($categoryFilter) {
                            // Option 2: Description starts with "category:"
                            $subQuery->where('description', 'LIKE', $categoryFilter . ':%');
                        });
                    });
                } else {
                    // Direct category match for treasury-only categories
                    $q->where('category', $categoryFilter);
                }
            });

        // Total incoming and outgoing for the filtered period
        $totalIn = (float) (clone $query)->where('type', 'in')->sum('amount');
        $totalOut = (float) (clone $query)->where('type', 'out')->sum('amount');
        
        // Current balance - calculate from the filtered period ONLY
        $currentBalance = $totalIn - $totalOut;
        
        $transactions = $query->orderBy('id')->paginate(25)->withQueryString();

        // Get all available categories for the dropdown
        $baseQuery = \App\Models\TreasuryTransaction::query()
            ->when(!$showOrderRelated, function($q) {
                $q->where(function($query) {
                    $query->where('description', 'NOT LIKE', '(أخرى) مخصص ضرائب%')
                            ->where('category', '!=', 'material_cost');
                });
            });

        // Get distinct treasury categories (direct categories from treasury_transactions table)
        $treasuryCategories = (clone $baseQuery)
            ->select('category')
            ->distinct()
            ->pluck('category')
            ->filter()
            ->toArray();

        // Get ALL expense categories from the expenses table (not just those linked to treasury)
        // This ensures custom categories like "ايجار مضخة 42" appear in the dropdown
        $expenseCategories = \App\Models\Expense::select('category')
            ->distinct()
            ->pluck('category')
            ->filter()
            ->toArray();

        // Merge treasury categories and expense categories
        $allCategories = collect(array_merge($treasuryCategories, $expenseCategories))
            ->unique()
            ->sort()
            ->mapWithKeys(function($cat) {
                // Get label from expense categories first (for both predefined and custom)
                $expenseCategoryList = \App\Models\Expense::categoryList();
                if (isset($expenseCategoryList[$cat])) {
                    return [$cat => $expenseCategoryList[$cat]];
                }
                
                // For custom expense categories, return as-is (Arabic text)
                $expenseExists = \App\Models\Expense::where('category', $cat)->exists();
                if ($expenseExists) {
                    return [$cat => $cat]; // Custom category, use the text as-is
                }
                
                // Otherwise get from treasury transaction predefined labels
                $dummyTransaction = new \App\Models\TreasuryTransaction(['category' => $cat]);
                return [$cat => $dummyTransaction->category_label];
            })
            ->toArray();

        return view('reports.treasury', compact('transactions', 'from', 'to', 'totalIn', 'totalOut', 'currentBalance', 'showOrderRelated', 'allCategories', 'categoryFilter'));
    }

    public function expenses(Request $request)
    {
        if ($request->export === 'excel') {
            return $this->exportExpenses($request);
        }

        $fromDate = $request->from_date ?? now()->startOfMonth()->toDateString();
        $toDate   = $request->to_date ?? now()->toDateString();

        // Get all categories
        $allCategories = \App\Models\ExpenseCategory::getAllCategories();

        $query = \App\Models\Expense::query()
            ->whereBetween('expense_date', [$fromDate, $toDate])
            ->when($request->category, fn($q, $v) => $q->where('category', $v));

        // Calculate totals
        $totalAmount = (float) (clone $query)->sum('amount');
        
        // Group by category for summary
        $categoryTotals = (clone $query)
            ->selectRaw('category, SUM(amount) as total')
            ->groupBy('category')
            ->get()
            ->mapWithKeys(function ($item) {
                $expense = new \App\Models\Expense(['category' => $item->category]);
                return [$expense->category_label => (float)$item->total];
            });

        $expenses = $query->with(['recordedBy', 'contributor'])
            ->orderBy('expense_date', 'desc')
            ->orderBy('id', 'desc')
            ->paginate(25)
            ->withQueryString();

        return view('reports.expenses', compact('expenses', 'fromDate', 'toDate', 'totalAmount', 'categoryTotals', 'allCategories'));
    }

    public function equipment(Request $request)
    {
        if ($request->export === 'excel') {
            return $this->exportEquipment($request);
        }

        $fromDate = $request->from_date ?? now()->startOfMonth()->toDateString();
        $toDate   = $request->to_date ?? now()->toDateString();

        $equipments = \App\Models\Equipment::when($request->equipment_id, fn($q, $v) => $q->where('id', $v))->get();

        $equipmentSummary = [];
        $totalFuel = 0;
        $totalMaint = 0;
        $totalRental = 0;
        $totalRentalMaint = 0;

        // Add owned equipment
        foreach ($equipments as $eq) {
            $fuelCost = (float) $eq->fuelLogs()
                ->whereBetween('log_date', [$fromDate, $toDate])
                ->sum('total_cost');

            $maintCost = (float) $eq->maintenance()
                ->whereBetween('maintenance_date', [$fromDate, $toDate])
                ->sum('cost');

            $equipmentSummary[] = [
                'name'         => $eq->name,
                'type'         => $eq->type_label ?? $eq->type,
                'category'     => 'مملوكة',
                'fuel'         => $fuelCost,
                'maint'        => $maintCost,
                'rental_fee'   => 0,
                'rental_maint' => 0,
                'total'        => $fuelCost + $maintCost,
            ];

            $totalFuel += $fuelCost;
            $totalMaint += $maintCost;
        }

        // Add rental cars/equipment — get all non-cancelled, filter by shift dates below
        $rentalContracts = \App\Models\RentalContract::with('supplier')
            ->where('status', '!=', 'cancelled')
            ->get();

        foreach ($rentalContracts as $rental) {
            // Use actual shift costs recorded in the period
            $rentalShiftCost = (float) \DB::table('rental_shifts')
                ->where('rental_contract_id', $rental->id)
                ->whereBetween('shift_date', [$fromDate, $toDate])
                ->sum('total_cost');

            // Get rental maintenance costs for the period
            $rentalMaintCost = (float) $rental->maintenance()
                ->whereBetween('maintenance_date', [$fromDate, $toDate])
                ->sum('cost');

            if ($rentalShiftCost > 0 || $rentalMaintCost > 0) {
                $equipmentSummary[] = [
                    'name'         => $rental->equipment_name . ($rental->car_number ? ' (' . $rental->car_number . ')' : ''),
                    'type'         => 'سيارة مستأجرة',
                    'category'     => 'مستأجرة',
                    'fuel'         => 0,
                    'maint'        => 0,
                    'rental_fee'   => $rentalShiftCost,
                    'rental_maint' => $rentalMaintCost,
                    'total'        => $rentalShiftCost + $rentalMaintCost,
                ];

                $totalRental += $rentalShiftCost;
                $totalRentalMaint += $rentalMaintCost;
            }
        }

        $grandTotal = $totalFuel + $totalMaint + $totalRental + $totalRentalMaint;

        return view('reports.equipment', compact('equipmentSummary', 'totalFuel', 'totalMaint', 'totalRental', 'totalRentalMaint', 'grandTotal', 'fromDate', 'toDate'));
    }

    public function payroll(Request $request)
    {
        if ($request->export === 'excel') {
            return $this->exportPayroll($request);
        }

        $month = $request->month ?? now()->month;
        $year  = $request->year ?? now()->year;

        $query = \App\Models\Payroll::with(['employee', 'borrowDeductions.borrow'])
            ->when($request->month, fn($q, $v) => $q->where('period_month', $v))
            ->when($request->year, fn($q, $v) => $q->where('period_year', $v))
            ->when($request->employee_id, fn($q, $v) => $q->where('employee_id', $v));

        // Calculate totals only for PAID payrolls
        $totals = [
            'base'       => (float) (clone $query)->where('status', 'paid')->sum('base_salary'),
            'overtime'   => (float) (clone $query)->where('status', 'paid')->sum('overtime_pay'),
            'deductions' => (float) (clone $query)->where('status', 'paid')->sum('total_deductions'),
            'net'        => (float) (clone $query)->where('status', 'paid')->sum('net_salary'),
        ];

        $payrolls = $query->paginate(25)->withQueryString();

        return view('reports.payroll', compact('payrolls', 'totals', 'month', 'year'));
    }

    public function credits(Request $request)
    {
        if ($request->export === 'excel') {
            return $this->exportCredits($request);
        }

        \App\Models\Credit::checkAndMarkOverdue();

        $query = \App\Models\Credit::with('creditable')
            ->when($request->creditable_type, function ($q, $v) {
                // Filter by creditable type (customer, supplier, or both)
                if ($v === 'customer') {
                    $q->where('creditable_type', 'customer');
                } elseif ($v === 'supplier') {
                    $q->where('creditable_type', 'supplier');
                }
                // If 'both' or empty, don't filter by type
            })
            ->when($request->customer_id, function ($q, $v) {
                $q->where('creditable_type', 'customer')->where('creditable_id', $v);
            })
            ->when($request->supplier_id, function ($q, $v) {
                $q->where('creditable_type', 'supplier')->where('creditable_id', $v);
            })
            ->when($request->status, function ($q, $v) {
                if ($v === 'active') {
                    $q->where('status', 'pending');
                } else {
                    $q->where('status', $v);
                }
            });

        $totalAmount = (float) $query->sum('amount');
        $totalPaid   = (float) (clone $query)->where('status', 'paid')->sum('amount');

        $credits = $query->orderBy('due_date')
            ->paginate(25)->withQueryString();

        return view('reports.credits', compact('credits', 'totalAmount', 'totalPaid'));
    }

    public function monthlyProfit(Request $request)
    {
        if ($request->export === 'excel') {
            return $this->exportMonthlyProfit($request);
        }

        $month = $request->month ?? now()->month;
        $year  = $request->year ?? now()->year;

        $start = \Carbon\Carbon::create($year, $month, 1)->startOfMonth()->toDateString();
        $end   = \Carbon\Carbon::create($year, $month, 1)->endOfMonth()->toDateString();

        // ═══ REVENUE (from Treasury IN transactions) ═══
        $treasuryIn = \App\Models\TreasuryTransaction::where('type', 'in')
            ->whereBetween('transaction_date', [$start, $end]);

        // ALL revenue from treasury - don't filter by category
        $totalRevenue = (float) (clone $treasuryIn)->sum('amount');

        // Breakdown for display (main categories)
        $customerPayments = (float) (clone $treasuryIn)->where('category', 'customer_payment')->sum('amount');
        $otherRevenue = $totalRevenue - $customerPayments;

        $revenues = [
            'orders' => $customerPayments,
            'other'  => $otherRevenue,
        ];

        // ═══ EXPENSES (from Treasury OUT transactions) ═══
        $treasuryOut = \App\Models\TreasuryTransaction::where('type', 'out')
            ->whereBetween('transaction_date', [$start, $end]);

        // ALL expenses from treasury - don't filter by category
        $totalExpense = (float) (clone $treasuryOut)->sum('amount');

        // Breakdown for display (main categories)
        $purchaseCost = (float) (clone $treasuryOut)->where('category', 'supplier_payment')->sum('amount');
        $payrollCost = (float) (clone $treasuryOut)->whereIn('category', ['salary', 'overtime'])->sum('amount');
        $rentalsCost = (float) (clone $treasuryOut)->whereIn('category', ['land_rent', 'rental', 'rental_maintenance'])->sum('amount');
        $generalExpenses = $totalExpense - $purchaseCost - $payrollCost - $rentalsCost;

        $expensesData = [
            'purchases' => $purchaseCost,
            'payroll'   => $payrollCost,
            'rentals'   => $rentalsCost,
            'general'   => $generalExpenses,
        ];

        $netProfit = $totalRevenue - $totalExpense;

        return view('reports.monthly-profit', compact('revenues', 'expensesData', 'totalRevenue', 'totalExpense', 'netProfit', 'month', 'year'));
    }

    public function annualProfit(Request $request)
    {
        if ($request->export === 'excel') {
            return $this->exportAnnualProfit($request);
        }

        $year = $request->year ?? now()->year;

        $monthlyData = [];
        $annualRevenue = 0;
        $annualExpense = 0;

        for ($m = 1; $m <= 12; $m++) {
            $start = \Carbon\Carbon::create($year, $m, 1)->startOfMonth()->toDateString();
            $end   = \Carbon\Carbon::create($year, $m, 1)->endOfMonth()->toDateString();

            $rev = (float) \App\Models\TreasuryTransaction::where('type', 'in')
                ->whereBetween('transaction_date', [$start, $end])
                ->sum('amount');

            $exp = (float) \App\Models\TreasuryTransaction::where('type', 'out')
                ->whereBetween('transaction_date', [$start, $end])
                ->sum('amount');

            $monthlyData[] = [
                'month'   => $m,
                'revenue' => $rev,
                'expense' => $exp,
                'profit'  => $rev - $exp,
            ];

            $annualRevenue += $rev;
            $annualExpense += $exp;
        }

        $annualProfit = $annualRevenue - $annualExpense;

        return view('reports.annual-profit', compact('monthlyData', 'annualRevenue', 'annualExpense', 'annualProfit', 'year'));
    }

    public function contributorBalance(Request $request)
    {
        if ($request->export === 'excel') {
            return $this->exportContributorBalance($request);
        }

        if ($request->contributor_id) {

            $contributor = Contributor::findOrFail($request->contributor_id);

            $fromDate = $request->from_date
                ?? now()->startOfMonth()->toDateString();

            $toDate = $request->to_date
                ?? now()->toDateString();

            // Get all contributor payments in the period
            $allPayments = $contributor->payments()
                ->whereBetween('payment_date', [$fromDate, $toDate])
                ->orderBy('payment_date')
                ->get();

            // Separate payments INTO business (contributor pays) vs OUT (we pay contributor)
            $paymentsIn = $allPayments->filter(fn($p) => $p->treasury_transaction_id !== null);
            $paymentsOut = $allPayments->filter(fn($p) => $p->treasury_transaction_id === null);

            $totalPaid = $paymentsOut->sum('amount'); // What we paid to contributor
            $totalReceived = $paymentsIn->sum('amount'); // What contributor paid in

            $shareAmount = $totalReceived; // قيمة الحصة الإجمالية = all contributions (payments in)
            $remaining = $totalReceived - $totalPaid; // المتبقي = contributions - payments out

            $transactions = collect();

            foreach ($allPayments as $payment) {
                $isPaymentOut = $payment->treasury_transaction_id === null;
                
                $transactions->push((object)[
                    'date'             => $payment->payment_date,
                    'description'      => $isPaymentOut ? 'دفعة للمساهم (صادر)' : 'دفعة من المساهم (وارد)',
                    'amount'           => $payment->amount,
                    'type'             => $isPaymentOut ? 'out' : 'in',
                    'method'           => $payment->payment_method,
                    'reference_number' => $payment->reference_number,
                    'notes'            => $payment->notes,
                ]);
            }

            return view(
                'reports.contributor-balance',
                compact(
                    'contributor',
                    'transactions',
                    'totalPaid',
                    'totalReceived',
                    'shareAmount',
                    'remaining',
                    'fromDate',
                    'toDate'
                )
            );
        }

        return view('reports.contributor-balance');
    }

    // ─── Excel Exports ──────────────────────────────────────────────────

    public function exportContributorBalance(Request $request)
    {
        $contributor = Contributor::findOrFail(
            $request->contributor_id
        );

        return Excel::download(
            new ContributorBalanceExport(
                $contributor->id,
                $request->from_date,
                $request->to_date
            ),
            'contributor-balance-' .
                now()->format('Y-m-d') .
                '.xlsx'
        );
    }

    public function exportCustomerBalance(Request $request)
    {
        try {
            // Handle export parameter from form
            if ($request->has('export') && $request->export === 'excel' && $request->customer_id) {
                $customer = \App\Models\Customer::findOrFail($request->customer_id);
                $fromDate = $request->from_date ?? now()->startOfMonth()->toDateString();
                $toDate   = $request->to_date ?? now()->toDateString();

                return Excel::download(
                    new CustomerBalanceExport($customer->id, $fromDate, $toDate),
                    "customer-balance-{$customer->name}-" . now()->format('Y-m-d') . '.xlsx',
                    \Maatwebsite\Excel\Excel::XLSX
                );
            }

            return Excel::download(
                new CustomerBalanceExport(null, $request->from_date, $request->to_date),
                'customer-balance-' . now()->format('Y-m-d') . '.xlsx',
                \Maatwebsite\Excel\Excel::XLSX
            );
        } catch (\Exception $e) {
            return back()->with('error', 'فشل تصدير التقرير: ' . $e->getMessage());
        }
    }

    public function exportSupplierBalance(Request $request)
    {
        try {
            // This method handles all suppliers summary export
            return Excel::download(
                new SupplierBalanceExport(null, $request->from_date, $request->to_date),
                'supplier-balance-' . now()->format('Y-m-d') . '.xlsx',
                \Maatwebsite\Excel\Excel::XLSX
            );
        } catch (\Exception $e) {
            return back()->with('error', 'فشل تصدير التقرير: ' . $e->getMessage());
        }
    }

    public function exportMonthlyProfit(Request $request)
    {
        try {
            $month = $request->month ?? now()->month;
            $year  = $request->year ?? now()->year;
            return Excel::download(
                new MonthlyProfitExport($month, $year),
                "monthly-profit-{$year}-{$month}.xlsx",
                \Maatwebsite\Excel\Excel::XLSX
            );
        } catch (\Exception $e) {
            return back()->with('error', 'فشل تصدير التقرير: ' . $e->getMessage());
        }
    }

    public function exportAnnualProfit(Request $request)
    {
        try {
            $year = $request->year ?? now()->year;
            return Excel::download(
                new AnnualProfitExport($year),
                "annual-profit-{$year}.xlsx",
                \Maatwebsite\Excel\Excel::XLSX
            );
        } catch (\Exception $e) {
            return back()->with('error', 'فشل تصدير التقرير: ' . $e->getMessage());
        }
    }

    public function exportInventory()
    {
        try {
            return Excel::download(
                new InventoryStatusExport(),
                'inventory-status-' . now()->format('Y-m-d') . '.xlsx',
                \Maatwebsite\Excel\Excel::XLSX
            );
        } catch (\Exception $e) {
            return back()->with('error', 'فشل تصدير التقرير: ' . $e->getMessage());
        }
    }

    public function exportInventoryMovements(\App\Models\InventoryItem $item)
    {
        try {
            return Excel::download(
                new InventoryMovementsExport($item),
                'inventory-movements-' . $item->name_en . '-' . now()->format('Y-m-d') . '.xlsx',
                \Maatwebsite\Excel\Excel::XLSX
            );
        } catch (\Exception $e) {
            return back()->with('error', 'فشل تصدير التقرير: ' . $e->getMessage());
        }
    }

    public function exportPayroll(Request $request)
    {
        try {
            // Use filters from request, not just current month/year
            $month = $request->month ?? null;
            $year  = $request->year ?? null;
            $employeeId = $request->employee_id ?? null;

            return Excel::download(
                new PayrollExport($month, $year, $employeeId),
                "payroll-" . ($year ?? 'all') . "-" . ($month ?? 'all') . ".xlsx",
                \Maatwebsite\Excel\Excel::XLSX
            );
        } catch (\Exception $e) {
            return back()->with('error', 'فشل تصدير التقرير: ' . $e->getMessage());
        }
    }

    public function exportCredits(Request $request)
    {
        try {
            return Excel::download(
                new CreditDueExport($request),
                'credits-due-' . now()->format('Y-m-d') . '.xlsx',
                \Maatwebsite\Excel\Excel::XLSX
            );
        } catch (\Exception $e) {
            return back()->with('error', 'فشل تصدير التقرير: ' . $e->getMessage());
        }
    }

    public function exportEquipment(Request $request)
    {
        try {
            return Excel::download(
                new EquipmentCostExport($request->from_date, $request->to_date),
                'equipment-costs-' . now()->format('Y-m-d') . '.xlsx',
                \Maatwebsite\Excel\Excel::XLSX
            );
        } catch (\Exception $e) {
            return back()->with('error', 'فشل تصدير التقرير: ' . $e->getMessage());
        }
    }

    public function exportTreasury(Request $request)
    {
        try {
            // Use full month by default to match treasury report view
            $from = $request->from_date ?? now()->startOfMonth()->toDateString();
            $to   = $request->to_date ?? now()->endOfMonth()->toDateString();
            $type = $request->type ?? null;  // Pass type filter
            $showOrderRelated = $request->boolean('show_order_related', false);  // Pass show_order_related filter
            $category = $request->category && $request->category !== 'all' ? $request->category : null;  // Pass category filter

            return Excel::download(
                new TreasuryExport($from, $to, $type, $showOrderRelated, $category),
                'treasury-' . now()->format('Y-m-d') . '.xlsx',
                \Maatwebsite\Excel\Excel::XLSX
            );
        } catch (\Exception $e) {
            return back()->with('error', 'فشل تصدير التقرير: ' . $e->getMessage());
        }
    }

    public function exportExpenses(Request $request)
    {
        try {
            $fromDate = $request->from_date ?? now()->startOfMonth()->toDateString();
            $toDate   = $request->to_date ?? now()->toDateString();
            $category = $request->category ?? null;

            return Excel::download(
                new \App\Exports\ExpensesExport($fromDate, $toDate, $category),
                'expenses-' . now()->format('Y-m-d') . '.xlsx',
                \Maatwebsite\Excel\Excel::XLSX
            );
        } catch (\Exception $e) {
            return back()->with('error', 'فشل تصدير التقرير: ' . $e->getMessage());
        }
    }

    public function rentalShifts(Request $request)
    {
        if ($request->export === 'excel') {
            return $this->exportRentalShifts($request);
        }

        $fromDate = $request->from_date ?? now()->startOfMonth()->toDateString();
        $toDate   = $request->to_date ?? now()->toDateString();

        // Get all active rental contracts with their shifts
        $contracts = \App\Models\RentalContract::with(['supplier', 'shifts' => function ($query) use ($fromDate, $toDate) {
            $query->whereBetween('shift_date', [$fromDate, $toDate])
                ->orderBy('shift_date');
        }])
            ->where('status', '!=', 'cancelled')
            ->orderBy('equipment_name')
            ->get();

        // Calculate totals per contract and grand totals
        $contractsData = [];
        $grandTotals = [
            'hours' => 0,
            'hours_cost' => 0,
            'gratuities' => 0,
            'cards_cost' => 0,
            'driver_allowance' => 0,
            'fuel_cost' => 0,
            'total_cost' => 0,
        ];

        foreach ($contracts as $contract) {
            if ($contract->shifts->isEmpty()) {
                continue;
            }

            $contractTotals = [
                'hours' => $contract->shifts->sum('hours'),
                'hours_cost' => $contract->shifts->sum('hours_cost'),
                'gratuities' => $contract->shifts->sum('gratuities'),
                'cards_cost' => $contract->shifts->sum('cards_cost'),
                'driver_allowance' => $contract->shifts->sum('driver_allowance'),
                'fuel_cost' => $contract->shifts->sum('fuel_cost'),
                'total_cost' => $contract->shifts->sum('total_cost'),
            ];

            $contractsData[] = [
                'contract' => $contract,
                'shifts' => $contract->shifts,
                'totals' => $contractTotals,
            ];

            // Add to grand totals
            foreach ($contractTotals as $key => $value) {
                $grandTotals[$key] += (float)$value;
            }
        }

        return view('reports.rental-shifts', compact('contractsData', 'grandTotals', 'fromDate', 'toDate'));
    }

    public function exportRentalShifts(Request $request)
    {
        try {
            $fromDate = $request->from_date ?? now()->startOfMonth()->toDateString();
            $toDate   = $request->to_date ?? now()->toDateString();

            return Excel::download(
                new RentalShiftsExport($fromDate, $toDate),
                'rental-shifts-' . now()->format('Y-m-d') . '.xlsx',
                \Maatwebsite\Excel\Excel::XLSX
            );
        } catch (\Exception $e) {
            return back()->with('error', 'فشل تصدير التقرير: ' . $e->getMessage());
        }
    }

    public function orders(Request $request)
    {
        if ($request->export === 'excel') {
            return $this->exportOrders($request);
        }

        $fromDate = $request->from_date ?? now()->startOfMonth()->toDateString();
        $toDate   = $request->to_date ?? now()->toDateString();

        $query = Order::with(['customer', 'concreteMix'])
            ->whereBetween('delivery_date', [$fromDate, $toDate])
            ->when($request->customer_id, fn($q, $v) => $q->where('customer_id', $v))
            ->when($request->status, fn($q, $v) => $q->where('status', $v))
            ->when($request->concrete_type, fn($q, $v) => $q->where('concrete_type', $v))
            ->orderBy('delivery_date', 'desc')
            ->orderBy('id', 'desc');

        // Calculate totals (only for delivered orders to match business logic)
        $totals = [
            'count' => $query->count(),
            'quantity' => (float) (clone $query)->where('status', 'delivered')->sum('quantity_m3'),
            'cement' => (float) (clone $query)->where('status', 'delivered')->sum('cement_deducted'),
            'total_amount' => (float) (clone $query)->where('status', 'delivered')->sum('total_amount'),
            'cash' => (float) (clone $query)->where('status', 'delivered')->sum('cash_amount'),
            'credit' => 0,
        ];
        $totals['credit'] = $totals['total_amount'] - $totals['cash'];

        $orders = $query->paginate(25)->withQueryString();

        return view('reports.orders', compact('orders', 'totals', 'fromDate', 'toDate'));
    }

    public function exportOrders(Request $request)
    {
        try {
            $fromDate = $request->from_date ?? now()->startOfMonth()->toDateString();
            $toDate   = $request->to_date ?? now()->toDateString();

            return Excel::download(
                new OrdersExport(
                    $fromDate,
                    $toDate,
                    $request->customer_id,
                    $request->status,
                    $request->concrete_type
                ),
                'orders-' . now()->format('Y-m-d') . '.xlsx',
                \Maatwebsite\Excel\Excel::XLSX
            );
        } catch (\Exception $e) {
            return back()->with('error', 'فشل تصدير التقرير: ' . $e->getMessage());
        }
    }

    // ─── Schedules Report ──────────────────────────────────────────────

    public function schedules(Request $request)
    {
        if ($request->export === 'excel') {
            $fromDate = $request->from_date ?? now()->startOfMonth()->toDateString();
            $toDate   = $request->to_date ?? now()->toDateString();
            return Excel::download(
                new SchedulesExport(
                    $fromDate,
                    $toDate,
                    $request->customer_id ? (int)$request->customer_id : null,
                    $request->entry_status
                ),
                'schedules-' . now()->format('Y-m-d') . '.xlsx'
            );
        }

        $fromDate    = $request->from_date ?? now()->startOfMonth()->toDateString();
        $toDate      = $request->to_date ?? now()->toDateString();
        $customerId  = $request->customer_id;
        $entryStatus = $request->entry_status;

        $schedules = \App\Models\WeeklySchedule::with(['entries' => function ($q) use ($customerId, $entryStatus) {
                $q->with(['customer', 'order.concreteMix']);
                if ($customerId) $q->where('customer_id', $customerId);
                if ($entryStatus) $q->where('status', $entryStatus);
            }, 'createdBy'])
            ->where('week_start', '<=', $toDate)
            ->where('week_end', '>=', $fromDate)
            ->orderBy('week_start')
            ->get()
            ->filter(fn($s) => $s->entries->isNotEmpty());

        $allEntries = $schedules->flatMap(fn($s) => $s->entries);

        $totals = [
            'total_entries' => $allEntries->count(),
            'total_m3'      => $allEntries->sum('quantity_m3'),
            'completed'     => $allEntries->where('status', 'completed')->count(),
            'pending'       => $allEntries->where('status', 'pending')->count(),
            'cancelled'     => $allEntries->where('status', 'cancelled')->count(),
        ];

        return view('reports.schedules', compact('schedules', 'totals', 'fromDate', 'toDate'));
    }

    // ─── General Manager Report ────────────────────────────────────────

    public function generalReport(Request $request)
    {
        if ($request->export === 'excel') {
            $fromDate = $request->from_date ?? now()->startOfMonth()->toDateString();
            $toDate   = $request->to_date ?? now()->toDateString();
            return Excel::download(
                new GeneralReportExport($fromDate, $toDate),
                'general-report-' . now()->format('Y-m-d') . '.xlsx'
            );
        }

        $fromDate = $request->from_date ?? now()->startOfMonth()->toDateString();
        $toDate   = $request->to_date ?? now()->toDateString();

        // Financial summary
        $orders = Order::whereBetween('delivery_date', [$fromDate, $toDate])
            ->where('status', '!=', 'cancelled')->get();

        $cashIn  = DB::table('treasury_transactions')->where('type', 'in')
            ->whereBetween('transaction_date', [$fromDate, $toDate])->sum('amount');
        $cashOut = DB::table('treasury_transactions')->where('type', 'out')
            ->whereBetween('transaction_date', [$fromDate, $toDate])->sum('amount');
        $treasuryBalance = DB::table('treasury_transactions')
            ->orderBy('transaction_date', 'desc')->orderBy('id', 'desc')
            ->value('balance_after') ?? 0;

        $financials = [
            'revenue'          => (float)$orders->sum('total_amount'),
            'expenses'         => (float)$cashOut,
            'cash_in'          => (float)$cashIn,
            'net_profit'       => (float)$cashIn - (float)$cashOut,
            'treasury_balance' => (float)$treasuryBalance,
        ];

        // Treasury IN (وارد) transactions for the period — detail rows
        $treasuryInRows = DB::table('treasury_transactions')
            ->where('type', 'in')
            ->whereBetween('transaction_date', [$fromDate, $toDate])
            ->orderBy('transaction_date')
            ->orderBy('id')
            ->get();

        // Treasury OUT (صادر) transactions for the period — detail rows
        $treasuryOutRows = DB::table('treasury_transactions')
            ->where('type', 'out')
            ->whereBetween('transaction_date', [$fromDate, $toDate])
            ->orderBy('transaction_date')
            ->orderBy('id')
            ->get();

        // Orders summary
        $ordersSummary = [
            'count'         => $orders->count(),
            'total_m3'      => (float)$orders->sum('quantity_m3'),
            'total_amount'  => (float)$orders->sum('total_amount'),
            'cash_collected'=> (float)$orders->sum('cash_amount'),
            'outstanding'   => (float)$orders->sum('total_amount') - (float)$orders->sum('cash_amount'),
        ];

        // Customers summary
        $customersSummary = $this->reportService->customerBalanceReport($fromDate, $toDate);

        // Suppliers summary
        $suppliers = $this->reportService->supplierBalanceReport($fromDate, $toDate);

        // Inventory
        $inventory = $this->reportService->inventoryStatusReport();

        // Equipment
        $equipment = $this->reportService->equipmentCostReport($fromDate, $toDate);

        // Payroll (current month)
        $payrollRecords = \App\Models\Payroll::where('period_month', now()->month)
            ->where('period_year', now()->year)->get();
        $payroll = [
            'count'          => $payrollRecords->count(),
            'net_total'      => (float)$payrollRecords->sum('net_salary'),
            'base_total'     => (float)$payrollRecords->sum('base_salary'),
            'overtime_total' => (float)$payrollRecords->sum('overtime_pay'),
        ];

        // Due credits
        $credits = $this->reportService->dueCreditReport();

        // Schedule summary
        $scheduleData = \App\Models\WeeklySchedule::with('entries')
            ->where('week_start', '<=', $toDate)
            ->where('week_end', '>=', $fromDate)
            ->get();
        $allSchedEntries = $scheduleData->flatMap(fn($s) => $s->entries);
        $scheduleSummary = [
            'count'         => $scheduleData->count(),
            'total_entries' => $allSchedEntries->count(),
            'total_m3'      => (float)$allSchedEntries->sum('quantity_m3'),
            'completed'     => $allSchedEntries->where('status', 'completed')->count(),
            'pending'       => $allSchedEntries->where('status', 'pending')->count(),
        ];

        // Contributors
        $contributors = \App\Models\Contributor::with('payments')->where('is_active', true)->get();

        return view('reports.general', compact(
            'fromDate', 'toDate',
            'financials', 'ordersSummary', 'customersSummary',
            'suppliers', 'inventory', 'equipment',
            'payroll', 'credits', 'scheduleSummary', 'contributors',
            'treasuryInRows', 'treasuryOutRows'
        ));
    }

    // ─── Neighboring Stations Report ───────────────────────────────────

    public function neighboringStations(Request $request)
    {
        if ($request->export === 'excel') {
            return $this->exportNeighboringStations($request);
        }

        $fromDate = $request->from_date ?? now()->startOfMonth()->toDateString();
        $toDate   = $request->to_date ?? now()->toDateString();
        $stationId = $request->station_id;

        $stations = \App\Models\NeighboringStation::orderBy('name')->get();

        if ($stationId) {
            // Single station detailed report
            $station = \App\Models\NeighboringStation::findOrFail($stationId);
            
            $transactions = $station->transactions()
                ->with('recordedBy')
                ->whereBetween('transaction_date', [$fromDate, $toDate])
                ->orderBy('transaction_date')
                ->get();

            $totalIncoming = $transactions->where('direction', 'incoming')->sum('amount');
            $totalOutgoing = $transactions->where('direction', 'outgoing')->sum('amount');
            $totalPaidIncoming = $transactions->where('direction', 'incoming')->sum('paid_amount');
            $totalPaidOutgoing = $transactions->where('direction', 'outgoing')->sum('paid_amount');
            $totalPaid = $totalPaidIncoming + $totalPaidOutgoing;
            $balance = ($totalIncoming - $totalPaidIncoming) - ($totalOutgoing - $totalPaidOutgoing);

            return view('reports.neighboring-stations', compact(
                'stations', 'station', 'transactions', 
                'totalIncoming', 'totalOutgoing', 'totalPaid', 'balance',
                'fromDate', 'toDate'
            ));
        }

        // All stations summary
        $stationsData = [];
        $grandTotalIncoming = 0;
        $grandTotalOutgoing = 0;
        $grandTotalPaid = 0;
        $grandBalance = 0;

        foreach ($stations as $station) {
            $transactions = $station->transactions()
                ->whereBetween('transaction_date', [$fromDate, $toDate])
                ->get();

            if ($transactions->isEmpty()) {
                continue;
            }

            $totalIncoming = $transactions->where('direction', 'incoming')->sum('amount');
            $totalOutgoing = $transactions->where('direction', 'outgoing')->sum('amount');
            $totalPaidIncoming = $transactions->where('direction', 'incoming')->sum('paid_amount');
            $totalPaidOutgoing = $transactions->where('direction', 'outgoing')->sum('paid_amount');
            $balance = ($totalIncoming - $totalPaidIncoming) - ($totalOutgoing - $totalPaidOutgoing);

            $stationsData[] = [
                'station' => $station,
                'transaction_count' => $transactions->count(),
                'total_incoming' => $totalIncoming,
                'total_outgoing' => $totalOutgoing,
                'total_paid' => $totalPaidIncoming + $totalPaidOutgoing,
                'balance' => $balance,
            ];

            $grandTotalIncoming += $totalIncoming;
            $grandTotalOutgoing += $totalOutgoing;
            $grandTotalPaid += $totalPaidIncoming + $totalPaidOutgoing;
            $grandBalance += $balance;
        }

        return view('reports.neighboring-stations', compact(
            'stations', 'stationsData',
            'grandTotalIncoming', 'grandTotalOutgoing', 'grandTotalPaid', 'grandBalance',
            'fromDate', 'toDate'
        ));
    }

    public function exportNeighboringStations(Request $request)
    {
        try {
            $fromDate = $request->from_date ?? now()->startOfMonth()->toDateString();
            $toDate   = $request->to_date ?? now()->toDateString();
            $stationId = $request->station_id ?? null;

            return Excel::download(
                new NeighboringStationsExport($stationId, $fromDate, $toDate),
                'neighboring-stations-' . now()->format('Y-m-d') . '.xlsx',
                \Maatwebsite\Excel\Excel::XLSX
            );
        } catch (\Exception $e) {
            return back()->with('error', 'فشل تصدير التقرير: ' . $e->getMessage());
        }
    }

    // ─── Trial Balance ─────────────────────────────────────────────────

    public function trialBalance(Request $request)
    {
        // Direct download — no HTML view needed, just export immediately
        try {
            $fromDate = $request->from_date ?? now()->startOfYear()->toDateString();
            $toDate   = $request->to_date   ?? now()->toDateString();

            return Excel::download(
                new TrialBalanceExport($fromDate, $toDate),
                'trial-balance-' . now()->format('Y-m-d') . '.xlsx',
                \Maatwebsite\Excel\Excel::XLSX
            );
        } catch (\Exception $e) {
            return back()->with('error', 'فشل إنشاء ميزان المراجعة: ' . $e->getMessage());
        }
    }
}