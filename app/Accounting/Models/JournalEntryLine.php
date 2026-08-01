<?php

namespace App\Accounting\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * A single debit or credit line within a journal entry.
 *
 * @property int    $id
 * @property int    $journal_entry_id
 * @property int    $account_id
 * @property float  $debit
 * @property float  $credit
 * @property string $description
 */
class JournalEntryLine extends Model
{
    protected $table = 'journal_entry_lines';

    protected $fillable = [
        'journal_entry_id',
        'account_id',
        'debit',
        'credit',
        'description',
    ];

    protected $casts = [
        'debit'  => 'decimal:2',
        'credit' => 'decimal:2',
    ];

    // ─── Relationships ──────────────────────────────────────────────────────────

    public function entry(): BelongsTo
    {
        return $this->belongsTo(JournalEntry::class, 'journal_entry_id');
    }

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class, 'account_id');
    }
}
