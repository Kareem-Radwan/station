<?php

namespace App\Services;

use App\Models\NeighboringStationTransaction;
use App\Models\TreasuryTransaction;
use Illuminate\Support\Facades\DB;

class NeighboringStationService
{
    public function __construct(private TreasuryService $treasuryService) {}

    public function recordTransaction(array $data): NeighboringStationTransaction
    {
        return DB::transaction(function () use ($data) {
            // Create the transaction
            $transaction = NeighboringStationTransaction::create([
                'neighboring_station_id' => $data['neighboring_station_id'],
                'transaction_type' => $data['transaction_type'],
                'direction' => $data['direction'],
                'transaction_date' => $data['transaction_date'],
                'amount' => $data['amount'],
                'description' => $data['description'],
                'reference_number' => $data['reference_number'] ?? null,
                'notes' => $data['notes'] ?? null,
                'payment_status' => $data['payment_status'] ?? 'pending',
                'paid_amount' => $data['paid_amount'] ?? 0,
                'recorded_by' => auth()->id(),
            ]);

            // If there's a payment, record it in treasury
            if (($data['paid_amount'] ?? 0) > 0) {
                $this->recordTreasuryTransaction($transaction, $data['paid_amount']);
            }

            return $transaction->load('station', 'recordedBy');
        });
    }

    public function updateTransaction(NeighboringStationTransaction $transaction, array $data): NeighboringStationTransaction
    {
        return DB::transaction(function () use ($transaction, $data) {
            $oldPaidAmount = $transaction->paid_amount;

            $transaction->update([
                'transaction_type' => $data['transaction_type'],
                'direction' => $data['direction'],
                'transaction_date' => $data['transaction_date'],
                'amount' => $data['amount'],
                'description' => $data['description'],
                'reference_number' => $data['reference_number'] ?? null,
                'notes' => $data['notes'] ?? null,
                'payment_status' => $data['payment_status'] ?? 'pending',
                'paid_amount' => $data['paid_amount'] ?? 0,
            ]);

            // Handle treasury changes if paid amount changed
            $newPaidAmount = $data['paid_amount'] ?? 0;
            if ($newPaidAmount != $oldPaidAmount) {
                // Delete old treasury transaction
                $this->treasuryService->deleteTransaction('neighboring_station', $transaction->id);
                
                // Create new treasury transaction if payment exists
                if ($newPaidAmount > 0) {
                    $this->recordTreasuryTransaction($transaction, $newPaidAmount);
                }
            }

            return $transaction->load('station', 'recordedBy');
        });
    }

    public function recordPayment(NeighboringStationTransaction $transaction, float $amount): void
    {
        DB::transaction(function () use ($transaction, $amount) {
            $newPaidAmount = $transaction->paid_amount + $amount;
            
            if ($newPaidAmount > $transaction->amount) {
                throw new \Exception('المبلغ المدفوع يتجاوز إجمالي المعاملة');
            }

            $transaction->update([
                'paid_amount' => $newPaidAmount,
                'payment_status' => $newPaidAmount >= $transaction->amount ? 'paid' : 'partial',
            ]);

            // Record in treasury
            $this->recordTreasuryTransaction($transaction, $amount, true);
        });
    }

    private function recordTreasuryTransaction(
        NeighboringStationTransaction $transaction, 
        float $amount, 
        bool $isAdditionalPayment = false
    ): void {
        $station = $transaction->station;
        
        if ($transaction->direction === 'incoming') {
            // We receive money (treasury IN)
            $this->treasuryService->recordIncoming(
                amount: $amount,
                category: 'neighboring_station_incoming',
                description: "دفعة من محطة: {$station->name} - {$transaction->description}",
                transactionDate: $transaction->transaction_date->format('Y-m-d'),
                referenceType: $isAdditionalPayment ? null : 'neighboring_station',
                referenceId: $isAdditionalPayment ? null : $transaction->id
            );
        } else {
            // We pay money (treasury OUT)
            $this->treasuryService->recordOutgoing(
                amount: $amount,
                category: 'neighboring_station_outgoing',
                description: "دفعة لمحطة: {$station->name} - {$transaction->description}",
                transactionDate: $transaction->transaction_date->format('Y-m-d'),
                referenceType: $isAdditionalPayment ? null : 'neighboring_station',
                referenceId: $isAdditionalPayment ? null : $transaction->id
            );
        }
    }

    public function deleteTransaction(NeighboringStationTransaction $transaction): void
    {
        DB::transaction(function () use ($transaction) {
            // Delete treasury transaction if exists
            $this->treasuryService->deleteTransaction('neighboring_station', $transaction->id);
            
            // Delete the transaction
            $transaction->delete();
        });
    }
}
