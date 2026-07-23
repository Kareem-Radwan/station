<?php

namespace App\Http\Controllers;

use App\Models\SupplierPayment;
use App\Models\Supplier;
use App\Models\SupplierPurchase;
use App\Services\TreasuryService;
use Illuminate\Http\Request;

class SupplierPaymentController extends Controller
{
    public function __construct(private TreasuryService $treasuryService) {}

    public function index()
    {
        $payments = SupplierPayment::with(['supplier','purchase'])->latest('payment_date')->paginate(20);
        return view('supplier-payments.index', compact('payments'));
    }

    public function create()
    {
        $suppliers = Supplier::active()->orderBy('name')->get();
        return view('supplier-payments.create', compact('suppliers'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'supplier_id'          => 'required|exists:suppliers,id',
            'supplier_purchase_id' => 'nullable|exists:supplier_purchases,id',
            'payment_date'         => 'required|date',
            'amount'               => 'required|numeric|min:0.01',
            'payment_method'       => 'required|in:cash,bank_transfer,check',
            'payment_type'         => 'required|in:payment,deduction',
            'notes'                => 'nullable|string',
        ]);

        $payment = SupplierPayment::create($request->all() + ['recorded_by' => null]);
        $supplier = Supplier::findOrFail($request->supplier_id);
        
        if ($request->payment_type === 'payment') {
            // Payment: we give them money, balance decreases (we owe less)
            $supplier->decrement('balance', $request->amount);
            
            // Record treasury outgoing for actual payments
            $this->treasuryService->recordOutgoing(
                amount: (float)$request->amount,
                category: 'supplier_payment',
                description: 'دفعة للمورد: ' . $supplier->name,
                referenceType: 'supplier_payment',
                referenceId: $payment->id
            );
        } else {
            // Deduction: we take money back from them, balance increases (we owe more / they owe us)
            $supplier->increment('balance', $request->amount);
        }

        $message = $request->payment_type === 'deduction' ? 'تم تسجيل الخصم بنجاح' : 'تم تسجيل الدفعة بنجاح';
        return redirect()->route('supplier-payments.index')->with('success', $message);
    }

    public function destroy(SupplierPayment $supplierPayment)
    {
        \Illuminate\Support\Facades\DB::transaction(function () use ($supplierPayment) {
            // Revert supplier balance
            if ($supplierPayment->supplier) {
                if ($supplierPayment->payment_type === 'payment') {
                    // Reverse payment: add back the amount we paid
                    $supplierPayment->supplier->increment('balance', $supplierPayment->amount);
                } else {
                    // Reverse deduction: subtract the amount we took back
                    $supplierPayment->supplier->decrement('balance', $supplierPayment->amount);
                }
            }
            
            // Delete treasury transactions only for payments, not deductions
            if ($supplierPayment->payment_type === 'payment') {
                $this->treasuryService->deleteTransaction('supplier_payment', $supplierPayment->id);
            }
            
            // Delete the payment itself
            $supplierPayment->delete();
        });

        $message = $supplierPayment->payment_type === 'deduction' ? 'تم حذف الخصم' : 'تم حذف الدفعة';
        return redirect()->route('supplier-payments.index')->with('success', $message);
    }
}
