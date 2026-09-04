<?php

namespace App\Http\Controllers;

use App\Models\Contributor;
use App\Models\ContributorPayment;
use App\Models\Customer;
use App\Models\CustomerPayment;
use App\Models\Employee;
use App\Models\EmployeeBorrow;
use App\Models\Equipment;
use App\Models\Expense;
use App\Models\LandRent;
use App\Models\LandRentPayment;
use App\Models\Order;
use App\Models\Receipt;
use App\Models\RentalContract;
use App\Models\Supplier;
use App\Models\SupplierPayment;
use App\Models\SupplierPurchase;
use App\Models\TreasuryTransaction;
use App\Services\TreasuryService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TreasuryController extends Controller
{
    public function __construct(private TreasuryService $treasuryService) {}

    // ─── Index ─────────────────────────────────────────────────────────────────

    public function index(Request $request)
    {
        $transactions = TreasuryTransaction::query()
            ->when($request->type,      fn($q,$v) => $q->where('type',$v))
            ->when($request->from_date, fn($q,$v) => $q->where('transaction_date','>=',$v))
            ->when($request->to_date,   fn($q,$v) => $q->where('transaction_date','<=',$v))
            ->latest('transaction_date')
            ->latest('id')
            ->paginate(30)
            ->withQueryString();

        // Current balance from the latest transaction by date+id order
        $currentBalance = TreasuryTransaction::orderBy('transaction_date', 'desc')
                            ->orderBy('id', 'desc')
                            ->value('balance_after') ?? 0;
        
        // Total incoming for ALL time
        $totalIncoming = (float) TreasuryTransaction::where('type', 'in')->sum('amount');
            
        // Total outgoing for ALL time
        $totalOutgoing = (float) TreasuryTransaction::where('type', 'out')->sum('amount');

        return view('treasury.index', compact('transactions','currentBalance','totalIncoming','totalOutgoing'));
    }

    // ─── Recalculate Balances (for SQL imports) ───────────────────────────────

    public function recalculateBalances()
    {
        DB::transaction(function () {
            $this->treasuryService->recalculateBalances();
        });

        return redirect()->route('treasury.index')->with('success', 'تم إعادة حساب الأرصدة بنجاح');
    }
    
    // ─── Debug Treasury Data ──────────────────────────────────────────────────
    
    public function debugData()
    {
        $allTransactions = TreasuryTransaction::orderBy('transaction_date')->get();
        
        $summary = [
            'total_transactions' => $allTransactions->count(),
            'total_in' => TreasuryTransaction::where('type', 'in')->sum('amount'),
            'total_out' => TreasuryTransaction::where('type', 'out')->sum('amount'),
            'date_range' => [
                'earliest' => $allTransactions->first()?->transaction_date?->format('Y-m-d'),
                'latest' => $allTransactions->last()?->transaction_date?->format('Y-m-d'),
            ],
            'current_month_check' => [
                'month_start' => now()->startOfMonth()->format('Y-m-d'),
                'month_end' => now()->endOfMonth()->format('Y-m-d'),
                'count_in_current_month' => TreasuryTransaction::whereDate('transaction_date', '>=', now()->startOfMonth())
                    ->whereDate('transaction_date', '<=', now()->endOfMonth())
                    ->count(),
                'incoming_current_month' => TreasuryTransaction::where('type', 'in')
                    ->whereDate('transaction_date', '>=', now()->startOfMonth())
                    ->whereDate('transaction_date', '<=', now()->endOfMonth())
                    ->sum('amount'),
                'outgoing_current_month' => TreasuryTransaction::where('type', 'out')
                    ->whereDate('transaction_date', '>=', now()->startOfMonth())
                    ->whereDate('transaction_date', '<=', now()->endOfMonth())
                    ->sum('amount'),
            ],
            'monthly_breakdown' => TreasuryTransaction::selectRaw("DATE_FORMAT(transaction_date, '%Y-%m') as month, type, COUNT(*) as count, SUM(amount) as total")
                ->groupBy('month', 'type')
                ->orderBy('month')
                ->get(),
        ];
        
        return response()->json($summary);
    }

    // ─── Create ────────────────────────────────────────────────────────────────

    public function create()
    {
        $customers       = Customer::active()->orderBy('name')->get(['id','name','phone']);
        $suppliers       = Supplier::active()->orderBy('name')->get(['id','name','phone']);
        $employees       = Employee::active()->orderBy('name')->get(['id','name','position']);
        $contributors    = Contributor::where('is_active', true)->orderBy('name')->get(['id','name']);
        $equipment       = Equipment::orderBy('name')->get(['id','name','type']);
        $rentalContracts = RentalContract::where('status','active')->orderBy('equipment_name')->get(['id','equipment_name','supplier_id']);
        $landRents       = LandRent::orderBy('due_date','desc')->get(['id','description','annual_amount','due_date']);
        $orders          = Order::with('customer:id,name')
                            ->whereIn('status',['pending','scheduled','delivered'])
                            ->orderBy('delivery_date','desc')
                            ->limit(100)
                            ->get(['id','customer_id','delivery_date','total_amount','status']);

        return view('treasury.create', compact(
            'customers','suppliers','employees','contributors',
            'equipment','rentalContracts','landRents','orders'
        ));
    }

    // ─── Store ─────────────────────────────────────────────────────────────────

    public function store(Request $request)
    {
        $validated = $request->validate([
            'type'             => 'required|in:in,out',
            'category'         => 'required|string|max:100',
            'custom_category'  => 'nullable|string|max:100',
            'amount'           => 'required|numeric|min:0.01',
            'transaction_date' => 'required|date',
            'description'      => 'nullable|string|max:1000',
            'reference_type'   => 'nullable|string|max:100',
            'reference_id'     => 'nullable|integer',
            'payment_method'   => 'required|in:cash,bank_transfer,check',
        ]);

        // Resolve actual category key
        $category = $validated['category'] === 'other'
            ? ($validated['custom_category'] ?? 'other')
            : $validated['category'];

        $amount          = (float) $validated['amount'];
        $txDate          = $validated['transaction_date'];
        $description     = $validated['description'] ?? '';
        $refType         = $validated['reference_type'] ?? null;
        $refId           = isset($validated['reference_id']) ? (int) $validated['reference_id'] : null;
        $type            = $validated['type'];
        $paymentMethod   = $validated['payment_method'];

        DB::transaction(function () use ($category, $amount, $txDate, $description, $refType, $refId, $type, $paymentMethod) {

            // ── 1. Create the domain-level record so reports stay consistent ──────

            switch ($category) {

                // ─── Customer Payment ────────────────────────────────────────────
                case 'customer_payment':
                case 'receipt_in':
                case 'receipt_out':
                case 'credit_payment':
                    $signedAmount = $type === 'out' ? -$amount : $amount;
                    if ($refId && $refType === 'customer') {
                        $cp = CustomerPayment::create([
                            'customer_id'    => $refId,
                            'order_id'       => null,
                            'payment_date'   => $txDate,
                            'amount'         => $signedAmount,
                            'payment_method' => $paymentMethod,
                            'notes'          => $description ?: ($type === 'out' ? 'سداد ديون (صادر)' : null),
                            'recorded_by'    => auth()->id(),
                        ]);
                        $domainRef = ['type' => 'customer_payment', 'id' => $cp->id];
                    } elseif ($refId && $refType === 'order') {
                        $order = Order::find($refId);
                        $cp = CustomerPayment::create([
                            'customer_id'    => $order?->customer_id,
                            'order_id'       => $refId,
                            'payment_date'   => $txDate,
                            'amount'         => $signedAmount,
                            'payment_method' => $paymentMethod,
                            'notes'          => $description ?: null,
                            'recorded_by'    => auth()->id(),
                        ]);
                        $domainRef = ['type' => 'customer_payment', 'id' => $cp->id];
                    } else {
                        $domainRef = null;
                    }
                    break;

                // ─── Supplier Payment ────────────────────────────────────────────
                case 'supplier_payment':
                case 'inventory_purchase':
                case 'material_cost':
                    if ($refId && $refType === 'supplier') {
                        $sp = SupplierPayment::create([
                            'supplier_id'          => $refId,
                            'supplier_purchase_id' => null,
                            'payment_date'         => $txDate,
                            'amount'               => $amount,
                            'payment_method'       => $paymentMethod,
                            'notes'                => $description ?: null,
                            'recorded_by'          => auth()->id(),
                        ]);
                        Supplier::find($refId)?->decrement('balance', $amount);
                        $domainRef = ['type' => 'supplier_payment', 'id' => $sp->id];
                    } else {
                        $domainRef = null;
                    }
                    break;

                // ─── Contributor Payment ─────────────────────────────────────────
                case 'contributor_payment':
                    if ($refId && $refType === 'contributor') {
                        $cp = ContributorPayment::create([
                            'contributor_id' => $refId,
                            'amount'         => $amount,
                            'payment_date'   => $txDate,
                            'payment_method' => $paymentMethod,
                            'notes'          => $description ?: null,
                        ]);
                        $domainRef = ['type' => 'contributor_payment', 'id' => $cp->id];
                    } else {
                        $domainRef = null;
                    }
                    break;

                // ─── Employee Borrow (advance) ───────────────────────────────────
                case 'employee_borrow':
                    if ($refId && $refType === 'employee') {
                        $borrow = EmployeeBorrow::create([
                            'employee_id'      => $refId,
                            'amount'           => $amount,
                            'remaining_amount' => $amount,
                            'borrow_date'      => $txDate,
                            'reason'           => $description ?: null,
                            'status'           => 'active',
                            'recorded_by'      => auth()->id(),
                        ]);
                        $domainRef = ['type' => EmployeeBorrow::class, 'id' => $borrow->id];
                    } else {
                        $domainRef = null;
                    }
                    break;

                // ─── Land Rent Payment ───────────────────────────────────────────
                case 'land_rent':
                    if ($refId && $refType === 'land_rent') {
                        $lrp = LandRentPayment::create([
                            'land_rent_id' => $refId,
                            'payment_date' => $txDate,
                            'amount'       => $amount,
                            'notes'        => $description ?: null,
                            'recorded_by'  => auth()->id(),
                        ]);
                        $domainRef = ['type' => 'land_rent_payment', 'id' => $lrp->id];
                    } else {
                        $domainRef = null;
                    }
                    break;

                // ─── General Expense & Employee & Equipment & Rental categories ──
                case 'rental':
                case 'rental_maintenance':
                case 'vehicle_equipment':
                case 'plant_maintenance':
                case 'salary':
                case 'overtime':
                case 'employee_borrow_repayment':
                case 'employee_borrow_return':
                case 'employee_deductions':
                case 'inventory_sale':
                case 'expense':
                    $expense = Expense::create([
                        'category'     => $category,
                        'amount'       => $amount,
                        'expense_date' => $txDate,
                        'description'  => $description ?: $category,
                        'reference_type' => $refType,
                        'reference_id'   => $refId,
                        'recorded_by'  => auth()->id(),
                    ]);
                    $domainRef = ['type' => 'expense', 'id' => $expense->id];
                    break;

                // ─── All other categories: treasury-only ─────────────────────────
                default:
                    $domainRef = null;
                    break;
            }

            // ── 2. Record the treasury transaction ─────────────────────────────
            $finalRefType = $domainRef['type']  ?? $refType;
            $finalRefId   = $domainRef['id']    ?? $refId;

            if ($type === 'in') {
                $this->treasuryService->record(
                    type: 'in',
                    amount: $amount,
                    category: $category,
                    description: $description,
                    transactionDate: $txDate,
                    referenceType: $finalRefType,
                    referenceId: $finalRefId,
                );
            } else {
                $this->treasuryService->record(
                    type: 'out',
                    amount: $amount,
                    category: $category,
                    description: $description,
                    transactionDate: $txDate,
                    referenceType: $finalRefType,
                    referenceId: $finalRefId,
                );
            }
        });

        return redirect()->route('treasury.index')->with('success', 'تم تسجيل الحركة في الخزينة وربطها بالسجلات المعنية بنجاح');
    }

    // ─── Edit ──────────────────────────────────────────────────────────────────

    public function edit(TreasuryTransaction $treasury)
    {
        $customers       = Customer::active()->orderBy('name')->get(['id','name','phone']);
        $suppliers       = Supplier::active()->orderBy('name')->get(['id','name','phone']);
        $employees       = Employee::active()->orderBy('name')->get(['id','name','position']);
        $contributors    = Contributor::where('is_active', true)->orderBy('name')->get(['id','name']);
        $equipment       = Equipment::orderBy('name')->get(['id','name','type']);
        $rentalContracts = RentalContract::where('status','active')->orderBy('equipment_name')->get(['id','equipment_name','supplier_id']);
        $landRents       = LandRent::orderBy('due_date','desc')->get(['id','description','annual_amount','due_date']);
        $orders          = Order::with('customer:id,name')
                            ->whereIn('status',['pending','scheduled','delivered'])
                            ->orderBy('delivery_date','desc')
                            ->limit(100)
                            ->get(['id','customer_id','delivery_date','total_amount','status']);

        return view('treasury.edit', compact(
            'treasury',
            'customers','suppliers','employees','contributors',
            'equipment','rentalContracts','landRents','orders'
        ));
    }

    // ─── Update ────────────────────────────────────────────────────────────────

    public function update(Request $request, TreasuryTransaction $treasury)
    {
        $validated = $request->validate([
            'type'             => 'required|in:in,out',
            'category'         => 'required|string|max:100',
            'amount'           => 'required|numeric|min:0.01',
            'transaction_date' => 'required|date',
            'description'      => 'nullable|string|max:1000',
        ]);

        DB::transaction(function () use ($treasury, $validated) {
            $oldAmount = (float)$treasury->amount;
            $newAmount = (float)$validated['amount'];

            $treasury->update([
                'type'             => $validated['type'],
                'category'         => $validated['category'],
                'amount'           => $newAmount,
                'transaction_date' => $validated['transaction_date'],
                'description'      => $validated['description'] ?? '',
            ]);

            // Sync update to related domain record if present
            $this->updateRelatedDomainRecord($treasury, $oldAmount, $newAmount, $validated);

            // Recalculate all balances after update
            $this->treasuryService->recalculateBalances();
        });

        return redirect()->route('treasury.index')->with('success', 'تم تحديث الحركة في الخزينة بنجاح');
    }

    // ─── Destroy ───────────────────────────────────────────────────────────────

    public function destroy(TreasuryTransaction $treasury)
    {
        DB::transaction(function () use ($treasury) {
            // Delete the related domain record if it exists
            $this->deleteRelatedDomainRecord($treasury);

            // Delete the treasury transaction
            $treasury->delete();

            // Recalculate all balances
            $this->treasuryService->recalculateBalances();
        });

        return redirect()->route('treasury.index')->with('success', 'تم حذف الحركة من الخزينة بنجاح');
    }

    // ─── Helper: Update Related Domain Record ─────────────────────────────────

    private function updateRelatedDomainRecord(TreasuryTransaction $treasury, float $oldAmount, float $newAmount, array $data): void
    {
        if (!$treasury->reference_type || !$treasury->reference_id) {
            return;
        }

        switch ($treasury->reference_type) {
            case 'customer_payment':
                $payment = CustomerPayment::find($treasury->reference_id);
                if ($payment) {
                    $signedNewAmount = $treasury->type === 'out' ? -$newAmount : $newAmount;
                    $payment->update([
                        'amount'       => $signedNewAmount,
                        'payment_date' => $data['transaction_date'],
                        'notes'        => $data['description'] ?? $payment->notes,
                    ]);
                }
                break;

            case 'supplier_payment':
                $payment = SupplierPayment::find($treasury->reference_id);
                if ($payment) {
                    $diff = $newAmount - $oldAmount;
                    if ($payment->supplier) {
                        if ($payment->payment_type === 'payment') {
                            $payment->supplier->decrement('balance', $diff);
                        } else {
                            $payment->supplier->increment('balance', $diff);
                        }
                    }
                    $payment->update([
                        'amount'       => $newAmount,
                        'payment_date' => $data['transaction_date'],
                        'notes'        => $data['description'] ?? $payment->notes,
                    ]);
                }
                break;

            case 'supplier_purchase':
            case 'purchase':
                $purchase = SupplierPurchase::find($treasury->reference_id);
                if ($purchase) {
                    $diff = $newAmount - $oldAmount;
                    if ($purchase->supplier) {
                        $purchase->supplier->increment('balance', $diff);
                    }
                    $purchase->update([
                        'total_amount'  => $newAmount,
                        'purchase_date' => $data['transaction_date'],
                        'notes'         => $data['description'] ?? $purchase->notes,
                    ]);
                }
                break;

            case 'contributor_payment':
                $payment = ContributorPayment::find($treasury->reference_id);
                if ($payment) {
                    $diff = $newAmount - $oldAmount;
                    if ($payment->contributor) {
                        $payment->contributor->decrement('share_amount', $diff);
                    }
                    $payment->update([
                        'amount'       => $newAmount,
                        'payment_date' => $data['transaction_date'],
                        'notes'        => $data['description'] ?? $payment->notes,
                    ]);
                }
                break;

            case 'land_rent_payment':
                $payment = LandRentPayment::find($treasury->reference_id);
                if ($payment) {
                    $diff = $newAmount - $oldAmount;
                    if ($payment->landRent) {
                        $payment->landRent->increment('paid_amount', $diff);
                    }
                    $payment->update([
                        'amount'       => $newAmount,
                        'payment_date' => $data['transaction_date'],
                        'notes'        => $data['description'] ?? $payment->notes,
                    ]);
                }
                break;

            case 'expense':
                $expense = Expense::find($treasury->reference_id);
                if ($expense) {
                    $expense->update([
                        'amount'       => $newAmount,
                        'expense_date' => $data['transaction_date'],
                        'description'  => $data['description'] ?? $expense->description,
                    ]);
                }
                break;

            case 'receipt':
                $receipt = Receipt::find($treasury->reference_id);
                if ($receipt) {
                    $receipt->update([
                        'amount'       => $newAmount,
                        'total_amount' => $newAmount,
                        'receipt_date' => $data['transaction_date'],
                        'description'  => $data['description'] ?? $receipt->description,
                    ]);
                }
                break;

            case EmployeeBorrow::class:
            case 'employee_borrow':
                $borrow = EmployeeBorrow::find($treasury->reference_id);
                if ($borrow) {
                    $diff = $newAmount - $oldAmount;
                    $borrow->update([
                        'amount'           => $newAmount,
                        'remaining_amount' => max(0, $borrow->remaining_amount + $diff),
                        'borrow_date'      => $data['transaction_date'],
                        'reason'           => $data['description'] ?? $borrow->reason,
                    ]);
                }
                break;
        }
    }

    // ─── Helper: Delete Related Domain Record ─────────────────────────────────

    private function deleteRelatedDomainRecord(TreasuryTransaction $treasury): void
    {
        if (!$treasury->reference_type || !$treasury->reference_id) {
            return;
        }

        switch ($treasury->reference_type) {
            case 'customer_payment':
                $payment = CustomerPayment::find($treasury->reference_id);
                if ($payment) {
                    // No stored balance column on customers table — balance is computed dynamically.
                    // Simply delete the payment record; the outstanding balance recalculates automatically.
                    $payment->delete();
                }
                break;

            case 'supplier_payment':
                $payment = SupplierPayment::find($treasury->reference_id);
                if ($payment) {
                    if ($payment->supplier) {
                        if ($payment->payment_type === 'payment') {
                            $payment->supplier->increment('balance', $payment->amount);
                        } else {
                            $payment->supplier->decrement('balance', $payment->amount);
                        }
                    }
                    $payment->delete();
                }
                break;

            case 'supplier_purchase':
            case 'purchase':
                $purchase = SupplierPurchase::find($treasury->reference_id);
                if ($purchase) {
                    if ($purchase->supplier) {
                        $purchase->supplier->decrement('balance', $purchase->total_amount);
                    }
                    $purchase->delete();
                }
                break;

            case 'contributor_payment':
                $payment = ContributorPayment::find($treasury->reference_id);
                if ($payment) {
                    if ($payment->contributor) {
                        $payment->contributor->increment('share_amount', $payment->amount);
                    }
                    $payment->delete();
                }
                break;

            case EmployeeBorrow::class:
            case 'employee_borrow':
                $borrow = EmployeeBorrow::find($treasury->reference_id);
                if ($borrow) {
                    $borrow->delete();
                }
                break;

            case 'land_rent_payment':
                $payment = LandRentPayment::find($treasury->reference_id);
                if ($payment) {
                    if ($payment->landRent) {
                        $payment->landRent->decrement('paid_amount', $payment->amount);
                    }
                    $payment->delete();
                }
                break;

            case 'expense':
                $expense = Expense::find($treasury->reference_id);
                if ($expense) {
                    $expense->delete();
                }
                break;

            case 'receipt':
                $receipt = Receipt::find($treasury->reference_id);
                if ($receipt) {
                    $receipt->delete();
                }
                break;

            case 'neighboring_station_transaction':
                $nst = \App\Models\NeighboringStationTransaction::find($treasury->reference_id);
                if ($nst) {
                    $nst->delete();
                }
                break;

            case 'order_expense':
                $oe = \App\Models\OrderExpense::find($treasury->reference_id);
                if ($oe) {
                    $oe->delete();
                }
                break;
        }
    }
}
