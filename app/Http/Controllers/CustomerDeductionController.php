<?php

namespace App\Http\Controllers;

use App\Models\CustomerDeduction;
use App\Models\Customer;
use App\Services\TreasuryService;
use Illuminate\Http\Request;

class CustomerDeductionController extends Controller
{
    public function __construct(private TreasuryService $treasuryService) {}

    public function store(Request $request)
    {
        $request->validate([
            'customer_id'    => 'required|exists:customers,id',
            'deduction_date' => 'required|date',
            'amount'         => 'required|numeric|min:0.01',
            'reason'         => 'nullable|string',
            'notes'          => 'nullable|string',
        ]);

        $deduction = CustomerDeduction::create($request->all() + ['recorded_by' => auth()->id()]);
        $customer = Customer::findOrFail($request->customer_id);

        // Record as outgoing transaction (negative value in treasury)
        $this->treasuryService->recordOutgoing(
            amount: (float)$request->amount,
            category: 'customer_deduction',
            description: 'خصم من العميل: ' . $customer->name . ($request->reason ? ' - ' . $request->reason : ''),
            referenceType: 'customer_deduction',
            referenceId: $deduction->id,
            transactionDate: $request->deduction_date
        );

        return back()->with('success', 'تم تسجيل الخصم بنجاح');
    }

    public function destroy(CustomerDeduction $customerDeduction)
    {
        \Illuminate\Support\Facades\DB::transaction(function () use ($customerDeduction) {
            // Delete treasury transactions and recalculate balance
            $this->treasuryService->deleteTransaction('customer_deduction', $customerDeduction->id);
            
            // Delete the deduction itself
            $customerDeduction->delete();
        });

        return back()->with('success', 'تم حذف الخصم');
    }
}
