<?php

namespace App\Http\Controllers;

use App\Models\Contributor;
use App\Models\ContributorPayment;
use App\Models\TreasuryTransaction;
use App\Services\TreasuryService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ContributorPaymentController extends Controller
{
    // ─── Create ────────────────────────────────────────────────────────────────

    public function create(Request $request)
    {
        $contributor = null;
        if ($request->filled('contributor_id')) {
            $contributor = Contributor::findOrFail($request->contributor_id);
        }

        $contributors = Contributor::where('is_active', true)->orderBy('name')->get();

        return view('contributors.payments.create', compact('contributors', 'contributor'));
    }

    // ─── Store ─────────────────────────────────────────────────────────────────

    public function store(Request $request)
    {
        $validated = $request->validate([
            'contributor_id'   => 'required|exists:contributors,id',
            'amount'           => 'required|numeric|min:0.01',
            'payment_date'     => 'required|date',
            'payment_method'   => 'required|in:cash,bank_transfer,check',
            'reference_number' => 'nullable|string|max:100',
            'notes'            => 'nullable|string',
        ]);

        DB::transaction(function () use ($validated) {
            $contributor = Contributor::lockForUpdate()->findOrFail($validated['contributor_id']);

            // Validate that payment doesn't exceed share amount
            if ($validated['amount'] > $contributor->share_amount) {
                throw new \Exception('المبلغ المدفوع لا يمكن أن يكون أكبر من رصيد المساهم (' . number_format($contributor->share_amount, 2) . ')');
            }

            // Create contributor payment record
            $payment = ContributorPayment::create([
                'contributor_id'        => $validated['contributor_id'],
                'amount'                => $validated['amount'],
                'payment_date'          => $validated['payment_date'],
                'payment_method'        => $validated['payment_method'],
                'reference_number'      => $validated['reference_number'] ?? null,
                'notes'                 => $validated['notes'] ?? null,
                'treasury_transaction_id' => null,
            ]);

            // Create treasury OUT transaction using TreasuryService
            app(TreasuryService::class)->recordOutgoing(
                amount: (float) $validated['amount'],
                category: 'contributor_payment_out',
                description: 'دفعة لمساهم: ' . $contributor->name,
                referenceType: 'contributor_payment',
                referenceId: $payment->id,
                transactionDate: $validated['payment_date']
            );

            // Deduct the payment amount from contributor's share_amount
            $contributor->decrement('share_amount', $validated['amount']);
        });

        $redirectContributorId = $validated['contributor_id'];

        return redirect()->route('contributors.show', $redirectContributorId)
            ->with('success', 'تم تسجيل الدفعة بنجاح');
    }

    // ─── Edit ──────────────────────────────────────────────────────────────────

    public function edit(ContributorPayment $contributorPayment)
    {
        $contributors = Contributor::where('is_active', true)->orderBy('name')->get();
        return view('contributors.payments.edit', compact('contributorPayment', 'contributors'));
    }

    // ─── Update ────────────────────────────────────────────────────────────────

    public function update(Request $request, ContributorPayment $contributorPayment)
    {
        $validated = $request->validate([
            'amount'           => 'required|numeric|min:0.01',
            'payment_date'     => 'required|date',
            'payment_method'   => 'required|in:cash,bank_transfer,check',
            'reference_number' => 'nullable|string|max:100',
            'notes'            => 'nullable|string',
        ]);

        DB::transaction(function () use ($validated, $contributorPayment) {
            $contributor = Contributor::lockForUpdate()->findOrFail($contributorPayment->contributor_id);
            
            $oldAmount = $contributorPayment->amount;
            $newAmount = $validated['amount'];
            
            // Revert old amount effect
            $contributor->increment('share_amount', $oldAmount);
            
            // Validate new amount doesn't exceed share_amount
            if ($newAmount > $contributor->share_amount) {
                throw new \Exception('المبلغ المدفوع لا يمكن أن يكون أكبر من رصيد المساهم');
            }
            
            // Apply new amount
            $contributor->decrement('share_amount', $newAmount);
            
            // Update payment record
            $contributorPayment->update($validated);
            
            // Update treasury transaction
            app(TreasuryService::class)->updateTransaction('contributor_payment', $contributorPayment->id, [
                'amount' => $newAmount,
                'transaction_date' => $validated['payment_date'],
                'description' => 'دفعة لمساهم: ' . $contributor->name,
            ]);
        });

        return redirect()->route('contributors.show', $contributorPayment->contributor_id)
            ->with('success', 'تم تحديث الدفعة بنجاح');
    }

    // ─── Destroy ───────────────────────────────────────────────────────────────

    public function destroy(ContributorPayment $contributorPayment)
    {
        DB::transaction(function () use ($contributorPayment) {
            $contributor = Contributor::lockForUpdate()->findOrFail($contributorPayment->contributor_id);
            
            // Add back the payment amount to contributor's share_amount
            $contributor->increment('share_amount', $contributorPayment->amount);
            
            // Delete treasury transactions via TreasuryService
            app(TreasuryService::class)->deleteTransaction('contributor_payment', $contributorPayment->id);
            
            // Remove linked treasury transaction if it exists
            if ($contributorPayment->treasury_transaction_id) {
                app(TreasuryService::class)->deleteTransactionById($contributorPayment->treasury_transaction_id);
            }
            
            $contributorPayment->delete();
        });

        return back()->with('success', 'تم حذف الدفعة');
    }

}
