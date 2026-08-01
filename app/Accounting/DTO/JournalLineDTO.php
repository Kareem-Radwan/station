<?php

namespace App\Accounting\DTO;

/**
 * Immutable value object representing a single line in a journal entry.
 *
 * Rules (enforced by JournalEntryService):
 *  - Exactly one of $debit / $credit must be > 0.
 *  - The other must be 0.
 *  - $accountId must reference a postable account.
 */
readonly class JournalLineDTO
{
    public function __construct(
        public int    $accountId,
        public float  $debit       = 0.0,
        public float  $credit      = 0.0,
        public string $description = '',
    ) {}

    /** Convenience constructor for a debit line */
    public static function debit(int $accountId, float $amount, string $description = ''): self
    {
        return new self(
            accountId:   $accountId,
            debit:       round($amount, 2),
            credit:      0.0,
            description: $description,
        );
    }

    /** Convenience constructor for a credit line */
    public static function credit(int $accountId, float $amount, string $description = ''): self
    {
        return new self(
            accountId:   $accountId,
            debit:       0.0,
            credit:      round($amount, 2),
            description: $description,
        );
    }

    public function isValid(): bool
    {
        return ($this->debit > 0 && $this->credit === 0.0)
            || ($this->credit > 0 && $this->debit === 0.0);
    }
}
