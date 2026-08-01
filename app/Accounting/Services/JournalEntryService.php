<?php

namespace App\Accounting\Services;

use App\Accounting\DTO\JournalLineDTO;
use App\Accounting\Enums\JournalStatus;
use App\Accounting\Exceptions\AccountingImbalanceException;
use App\Accounting\Models\JournalEntry;
use App\Accounting\Models\JournalEntryLine;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * Low-level service responsible for creating, voiding, and validating journal entries.
 *
 * Rules:
 *  - sum(debit)  === sum(credit)  — always enforced
 *  - Each account_id must be postable (is_postable = true)
 *  - Wrapped in DB::transaction() — callers must NOT wrap again
 *
 * Posting service (AccountingPostingService) delegates here for actual persistence.
 */
class JournalEntryService
{
    /**
     * Create and persist a balanced journal entry.
     *
     * @param  string            $description  Human-readable description
     * @param  Carbon|string     $date         Date of the economic event
     * @param  JournalLineDTO[]  $lines        Must balance (sum debit = sum credit)
     * @param  string|null       $referenceType  e.g. 'order', 'customer_payment'
     * @param  int|null          $referenceId
     * @param  JournalStatus     $status
     * @return JournalEntry
     *
     * @throws AccountingImbalanceException
     */
    public function create(
        string            $description,
        Carbon|string     $date,
        array             $lines,
        ?string           $referenceType = null,
        ?int              $referenceId   = null,
        JournalStatus     $status        = JournalStatus::Posted,
    ): JournalEntry {

        $this->assertBalanced($lines, $description);

        return DB::transaction(function () use (
            $description, $date, $lines, $referenceType, $referenceId, $status
        ) {
            $entry = JournalEntry::create([
                'entry_no'       => $this->generateEntryNo(),
                'date'           => $date instanceof Carbon ? $date->toDateString() : $date,
                'description'    => $description,
                'reference_type' => $referenceType,
                'reference_id'   => $referenceId,
                'status'         => $status->value,
                'created_by'     => auth()->id(),
            ]);

            foreach ($lines as $line) {
                JournalEntryLine::create([
                    'journal_entry_id' => $entry->id,
                    'account_id'       => $line->accountId,
                    'debit'            => $line->debit,
                    'credit'           => $line->credit,
                    'description'      => $line->description ?: $description,
                ]);
            }

            return $entry;
        });
    }

    /**
     * Void (reverse) all journal entries tied to a specific business record.
     * Creates mirror entries with swapped debit/credit so the ledger remains balanced.
     */
    public function voidForReference(string $referenceType, int $referenceId): void
    {
        $entries = JournalEntry::where('reference_type', $referenceType)
            ->where('reference_id', $referenceId)
            ->where('status', JournalStatus::Posted->value)
            ->with('lines')
            ->get();

        DB::transaction(function () use ($entries, $referenceType, $referenceId) {
            foreach ($entries as $entry) {
                // Mark original as voided
                $entry->update(['status' => JournalStatus::Voided->value]);

                // Build reversal lines
                $reversalLines = $entry->lines->map(function (JournalEntryLine $line) {
                    return new JournalLineDTO(
                        accountId:   $line->account_id,
                        debit:       (float) $line->credit,   // swap
                        credit:      (float) $line->debit,    // swap
                        description: 'عكس: ' . $line->description,
                    );
                })->all();

                $this->create(
                    description:   'قيد عكسي: ' . $entry->description,
                    date:          now()->toDateString(),
                    lines:         $reversalLines,
                    referenceType: $referenceType . '_reversal',
                    referenceId:   $referenceId,
                    status:        JournalStatus::Posted,
                );
            }
        });
    }

    /**
     * Check whether a posted journal entry already exists for a given reference.
     * Used by the rebuild command to make it idempotent.
     */
    public function existsForReference(string $referenceType, int $referenceId): bool
    {
        return JournalEntry::where('reference_type', $referenceType)
            ->where('reference_id', $referenceId)
            ->whereIn('status', [JournalStatus::Posted->value, JournalStatus::Draft->value])
            ->exists();
    }

    /**
     * Delete all journal entries for a reference (used only by rebuild in dry-run or reset mode).
     */
    public function deleteForReference(string $referenceType, int $referenceId): int
    {
        return JournalEntry::where('reference_type', $referenceType)
            ->where('reference_id', $referenceId)
            ->delete();
    }

    // ─── Private helpers ─────────────────────────────────────────────────────────

    /**
     * @param  JournalLineDTO[]  $lines
     * @throws AccountingImbalanceException
     */
    private function assertBalanced(array $lines, string $context = ''): void
    {
        $totalDebit  = array_sum(array_column($lines, 'debit'));
        $totalCredit = array_sum(array_column($lines, 'credit'));

        if (abs($totalDebit - $totalCredit) >= 0.01) {
            throw new AccountingImbalanceException($totalDebit, $totalCredit, $context);
        }
    }

    /**
     * Generate a unique, sequential, human-readable entry number.
     * Format: JE-YYYY-NNNNN  (e.g. JE-2026-00001)
     */
    private function generateEntryNo(): string
    {
        $year = now()->year;
        $prefix = "JE-{$year}-";

        // Lock to prevent race conditions
        $last = JournalEntry::where('entry_no', 'like', $prefix . '%')
            ->lockForUpdate()
            ->orderByDesc('entry_no')
            ->value('entry_no');

        $sequence = $last
            ? (int) substr($last, strlen($prefix)) + 1
            : 1;

        return $prefix . str_pad($sequence, 5, '0', STR_PAD_LEFT);
    }
}
