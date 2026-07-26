<?php

namespace App\Http\Controllers;

use App\Models\Credit;
use App\Services\TreasuryService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CreditController extends Controller
{
    public function __construct(private TreasuryService $treasuryService) {}

    public function index(Request $request)
    {
        Credit::checkAndMarkOverdue();

        $query = Credit::with('creditable')
            ->when($request->type, fn($q,$v) => $q->where('creditable_type',$v))
            ->when($request->status, function ($q, $v) {
                if ($v === 'active') {
                    $q->where('status', 'pending');
                } else {
                    $q->where('status', $v);
                }
            });

        $totalCredits = (float) (clone $query)->sum('amount');
        $totalPaid = (float) (clone $query)->where('status', 'paid')->sum('amount');
        $totalOverdue = (float) (clone $query)->where('status', 'overdue')->sum('amount');

        $credits = $query->orderBy('due_date')
            ->paginate(25)->withQueryString();

        $overdueCount = Credit::where('status','overdue')->count();
        $dueSoonCount = Credit::dueSoon()->count();

        return view('credits.index', compact('credits','overdueCount','dueSoonCount','totalCredits','totalPaid','totalOverdue'));
    }

    public function create()
    {
        $customers = \App\Models\Customer::active()->orderBy('name')->get();
        $suppliers = \App\Models\Supplier::active()->orderBy('name')->get();
        return view('credits.create', compact('customers','suppliers'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'creditable_type' => 'required|in:customer,supplier',
            'creditable_id'   => 'required|integer',
            'amount'          => 'required|numeric|min:0.01',
            'due_date'        => 'required|date',
            'notes'           => 'nullable|string',
        ]);

        Credit::create($validated + ['created_by' => auth()->id()]);

        return redirect()->route('credits.index')->with('success', 'تم إضافة السجل الآجل');
    }

    public function show(Credit $credit)
    {
        $credit->load('creditable');
        return view('credits.show', compact('credit'));
    }

    public function markPaid(Credit $credit)
    {
        if ($credit->status === 'paid') {
            return back()->with('error', 'هذا الدين مسدد مسبقاً');
        }

        DB::transaction(function () use ($credit) {
            // Mark credit as paid
            $credit->update(['status' => 'paid', 'paid_date' => now()->toDateString()]);

            if ($credit->creditable_type === 'customer') {
                // Create customer payment record
                $payment = \App\Models\CustomerPayment::create([
                    'customer_id'    => $credit->creditable_id,
                    'order_id'       => $credit->reference_type === 'order' ? $credit->reference_id : null,
                    'payment_date'   => now()->toDateString(),
                    'amount'         => $credit->amount,
                    'payment_method' => 'cash',
                    'notes'          => 'تسديد دين رقم #' . $credit->id,
                    'recorded_by'    => auth()->id(),
                ]);

                // Record treasury incoming
                $party = \App\Models\Customer::find($credit->creditable_id);
                $this->treasuryService->recordIncoming(
                    amount: (float)$credit->amount,
                    category: 'customer_payment',
                    description: 'تسديد دين من العميل: ' . ($party?->name ?? ''),
                    referenceType: 'customer_payment',
                    referenceId: $payment->id
                );
            } elseif ($credit->creditable_type === 'supplier') {
                // Create supplier payment record
                $payment = \App\Models\SupplierPayment::create([
                    'supplier_id'          => $credit->creditable_id,
                    'supplier_purchase_id' => $credit->reference_type === 'purchase' ? $credit->reference_id : null,
                    'payment_date'         => now()->toDateString(),
                    'amount'               => $credit->amount,
                    'payment_method'       => 'cash',
                    'payment_type'         => 'payment',
                    'notes'                => 'تسديد دين رقم #' . $credit->id,
                    'recorded_by'          => auth()->id(),
                ]);

                // Decrement supplier balance
                $party = \App\Models\Supplier::find($credit->creditable_id);
                if ($party) {
                    $party->decrement('balance', (float)$credit->amount);
                }

                // Record treasury outgoing
                $this->treasuryService->recordOutgoing(
                    amount: (float)$credit->amount,
                    category: 'supplier_payment',
                    description: 'تسديد مستحقات للمورد: ' . ($party?->name ?? ''),
                    referenceType: 'supplier_payment',
                    referenceId: $payment->id
                );
            }
        });

        return back()->with('success', 'تم تأكيد السداد وتسجيله في الخزينة والدفعات');
    }
}
