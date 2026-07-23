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
use App\Models\RentalContract;
use App\Models\Supplier;
use App\Models\SupplierPayment;
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
            ->latest('id')->paginate(30)->withQueryString();

        $currentBalance = TreasuryTransaction::where('type','in')->sum('amount') - TreasuryTransaction::where('type','out')->sum('amount');
        $monthlyIncoming = TreasuryTransaction::where('type','in')
            ->where('transaction_date','>=',now()->startOfMonth()->toDateString())->sum('amount');
        $monthlyOutgoing = TreasuryTransaction::where('type','out')
            ->where('transaction_date','>=',now()->startOfMonth()->toDateString())->sum('amount');

        return view('treasury.index', compact('transactions','currentBalance','monthlyIncoming','monthlyOutgoing'));
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
                    if ($refId && $refType === 'customer') {
                        $cp = CustomerPayment::create([
                            'customer_id'    => $refId,
                            'order_id'       => null,
                            'payment_date'   => $txDate,
                            'amount'         => $amount,
                            'payment_method' => $paymentMethod,
                            'notes'          => $description ?: null,
                            'recorded_by'    => auth()->id(),
                        ]);
                        $domainRef = ['type' => 'customer_payment', 'id' => $cp->id];
                    } elseif ($refId && $refType === 'order') {
                        $order = Order::find($refId);
                        $cp = CustomerPayment::create([
                            'customer_id'    => $order?->customer_id,
                            'order_id'       => $refId,
                            'payment_date'   => $txDate,
                            'amount'         => $amount,
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

                // ─── General Expense categories ──────────────────────────────────
                case 'rental':
                case 'rental_maintenance':
                case 'vehicle_equipment':
                case 'plant_maintenance':
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
}
