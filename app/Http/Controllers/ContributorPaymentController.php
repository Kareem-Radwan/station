<?php

namespace App\Http\Controllers;

use App\Models\Contributor;
use App\Models\ContributorPayment;
use App\Models\TreasuryTransaction;
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

            // Create treasury OUT transaction (صادر - we are paying out)
            $treasury = TreasuryTransaction::create([
                'type'             => 'out',
                'amount'           => $validated['amount'],
                'category'         => 'contributor_payment_out',
                'description'      => 'دفعة لمساهم: ' . $contributor->name,
                'transaction_date' => $validated['payment_date'],
                'balance_after'    => $this->calculateBalanceAfter($validated['amount'], 'out'),
                'recorded_by'      => auth()->id(),
            ]);

            // Create contributor payment record
            ContributorPayment::create([
                'contributor_id'        => $validated['contributor_id'],
                'amount'                => $validated['amount'],
                'payment_date'          => $validated['payment_date'],
                'payment_method'        => $validated['payment_method'],
                'reference_number'      => $validated['reference_number'] ?? null,
                'notes'                 => $validated['notes'] ?? null,
                'treasury_transaction_id' => null, // Don't link to avoid confusion with IN payments
            ]);

            // Deduct the payment amount from contributor's share_amount
            $contributor->decrement('share_amount', $validated['amount']);
        });

        $redirectContributorId = $validated['contributor_id'];

        return redirect()->route('contributors.show', $redirectContributorId)
            ->with('success', 'تم تسجيل الدفعة بنجاح');
    }

    // ─── Destroy ───────────────────────────────────────────────────────────────

    public function destroy(ContributorPayment $contributorPayment)
    {
        DB::transaction(function () use ($contributorPayment) {
            $contributor = Contributor::lockForUpdate()->findOrFail($contributorPayment->contributor_id);
            
            // Add back the payment amount to contributor's share_amount
            $contributor->increment('share_amount', $contributorPayment->amount);
            
            // Delete the treasury OUT transaction for this payment
            TreasuryTransaction::where('category', 'contributor_payment_out')
                ->where('amount', $contributorPayment->amount)
                ->where('transaction_date', $contributorPayment->payment_date)
                ->where('description', 'LIKE', '%' . $contributor->name . '%')
                ->delete();
            
            // Remove linked treasury transaction if it exists (for share increases)
            if ($contributorPayment->treasury_transaction_id) {
                TreasuryTransaction::find($contributorPayment->treasury_transaction_id)?->delete();
            }
            
            $contributorPayment->delete();
        });

        return back()->with('success', 'تم حذف الدفعة');
    }

    // ─── Private Helpers ───────────────────────────────────────────────────────

    /**
     * Calculate the running balance after this transaction.
     * Mirrors the pattern used by TreasuryController.
     */
    private function calculateBalanceAfter(float $amount, string $type): float
    {
        $lastBalance = (float) TreasuryTransaction::orderBy('id', 'desc')
            ->value('balance_after') ?? 0;

        return $type === 'in'
            ? $lastBalance + $amount
            : $lastBalance - $amount;
    }
}
