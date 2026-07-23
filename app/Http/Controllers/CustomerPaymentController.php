<?php

namespace App\Http\Controllers;

use App\Models\CustomerPayment;
use App\Models\Customer;
use App\Models\Order;
use App\Services\TreasuryService;
use Illuminate\Http\Request;

class CustomerPaymentController extends Controller
{
    public function __construct(private TreasuryService $treasuryService) {}

    public function index()
    {
        $payments = CustomerPayment::with(['customer','order'])->latest('payment_date')->paginate(20);
        return view('customer-payments.index', compact('payments'));
    }

    public function create()
    {
        $customers = Customer::active()->orderBy('name')->get();
        return view('customer-payments.create', compact('customers'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'customer_id'    => 'required|exists:customers,id',
            'order_id'       => 'nullable|exists:orders,id',
            'payment_date'   => 'required|date',
            'amount'         => 'required|numeric|min:0.01',
            'payment_method' => 'required|in:cash,bank_transfer,check',
            'notes'          => 'nullable|string',
        ]);

        $payment = CustomerPayment::create($request->all() + ['recorded_by' => null]);
        $customer = Customer::findOrFail($request->customer_id);

        $this->treasuryService->recordIncoming(
            amount: (float)$request->amount,
            category: 'customer_payment',
            description: 'دفعة من العميل: ' . $customer->name,
            referenceType: 'customer_payment',
            referenceId: $payment->id
        );

        return redirect()->route('customer-payments.index')->with('success', 'تم تسجيل الدفعة بنجاح');
    }

    public function destroy(CustomerPayment $customerPayment)
    {
        \Illuminate\Support\Facades\DB::transaction(function () use ($customerPayment) {
            // Delete treasury transactions and recalculate balance
            $this->treasuryService->deleteTransaction('customer_payment', $customerPayment->id);
            
            // Delete the payment itself
            $customerPayment->delete();
        });

        return redirect()->route('customer-payments.index')->with('success', 'تم حذف الدفعة');
    }
}
