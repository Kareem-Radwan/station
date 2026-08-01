<?php

namespace App\Accounting\Exceptions;

use RuntimeException;

/**
 * Thrown when a journal entry's debits do not equal its credits.
 * This is a programming error — it should never reach production.
 */
class AccountingImbalanceException extends RuntimeException
{
    public function __construct(float $totalDebit, float $totalCredit, string $context = '')
    {
        $msg = sprintf(
            'Journal entry imbalance: debit=%.2f, credit=%.2f, diff=%.2f%s',
            $totalDebit,
            $totalCredit,
            abs($totalDebit - $totalCredit),
            $context ? " | context: {$context}" : ''
        );

        parent::__construct($msg);
    }
}
