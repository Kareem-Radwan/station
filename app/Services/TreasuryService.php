<?php

namespace App\Services;

use App\Models\TreasuryTransaction;
use Illuminate\Support\Facades\DB;

class TreasuryService
{
    public function record(
        string $type,
        float $amount,
        string $category,
        string $description,
        ?string $transactionDate = null,
        ?string $referenceType = null,
        ?int $referenceId = null
    ): TreasuryTransaction {

        return DB::transaction(function () use (
            $type,
            $amount,
            $category,
            $description,
            $transactionDate,
            $referenceType,
            $referenceId
        ) {

            $transaction = TreasuryTransaction::create([
                'type'             => $type,
                'category'         => $category,
                'amount'           => $amount,
                'balance_after'    => 0,
                'transaction_date' => $transactionDate ?? now()->toDateString(),
                'description'      => $description,
                'reference_type'   => $referenceType,
                'reference_id'     => $referenceId,
                'recorded_by'      => auth()->id(),
            ]);

            $this->recalculateBalances();

            return $transaction;
        });
    }

    public function recordIncoming(
        float $amount,
        string $category,
        string $description,
        ?string $referenceType = null,
        ?int $referenceId = null,
        ?string $transactionDate = null
    ): TreasuryTransaction {
        return $this->record(
            type: 'in',
            amount: $amount,
            category: $category,
            description: $description,
            transactionDate: $transactionDate,
            referenceType: $referenceType,
            referenceId: $referenceId
        );
    }

    public function recordOutgoing(
        float $amount,
        string $category,
        string $description,
        ?string $referenceType = null,
        ?int $referenceId = null,
        ?string $transactionDate = null
    ): TreasuryTransaction {
        return $this->record(
            type: 'out',
            amount: $amount,
            category: $category,
            description: $description,
            transactionDate: $transactionDate,
            referenceType: $referenceType,
            referenceId: $referenceId
        );
    }

    public function recalculateBalances(): void
    {
        $balance = 0;

        $transactions = TreasuryTransaction::orderBy('transaction_date')
            ->orderBy('id')
            ->lockForUpdate()
            ->get();

        foreach ($transactions as $transaction) {

            if ($transaction->type === 'in') {
                $balance += $transaction->amount;
            } else {
                $balance -= $transaction->amount;
            }

            $transaction->update([
                'balance_after' => $balance
            ]);
        }
    }
    
    public function getCurrentBalance(): float
    {
        return (float)(TreasuryTransaction::latest('id')->value('balance_after') ?? 0);
    }

    public function deleteTransaction(string $referenceType, int $referenceId): void
    {
        DB::transaction(function () use ($referenceType, $referenceId) {
            // Delete all treasury transactions with this reference
            TreasuryTransaction::where('reference_type', $referenceType)
                ->where('reference_id', $referenceId)
                ->delete();

            // Recalculate all balances after deletion
            $this->recalculateBalances();
        });
    }
    
    public function deleteTransactionById(int $transactionId): void
    {
        DB::transaction(function () use ($transactionId) {
            $transaction = TreasuryTransaction::find($transactionId);
            
            if ($transaction) {
                $transaction->delete();
                $this->recalculateBalances();
            }
        });
    }

    public function updateTransaction(
        string $referenceType,
        int $referenceId,
        array $data
    ): void {
        DB::transaction(function () use ($referenceType, $referenceId, $data) {
            $transactions = TreasuryTransaction::where('reference_type', $referenceType)
                ->where('reference_id', $referenceId)
                ->get();

            foreach ($transactions as $transaction) {
                $updateData = [];
                if (isset($data['type'])) $updateData['type'] = $data['type'];
                if (isset($data['category'])) $updateData['category'] = $data['category'];
                if (isset($data['amount'])) $updateData['amount'] = $data['amount'];
                if (isset($data['transaction_date'])) $updateData['transaction_date'] = $data['transaction_date'];
                if (isset($data['description'])) $updateData['description'] = $data['description'];

                if (!empty($updateData)) {
                    $transaction->update($updateData);
                }
            }

            $this->recalculateBalances();
        });
    }
}
