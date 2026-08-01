<?php

namespace App\Accounting\Models;

use App\Accounting\Enums\JournalStatus;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Builder;

/**
 * Journal Entry header (equivalent to SAP document header).
 *
 * @property int           $id
 * @property string        $entry_no
 * @property \Carbon\Carbon $date
 * @property string|null   $description
 * @property string|null   $reference_type
 * @property int|null      $reference_id
 * @property JournalStatus $status
 * @property int|null      $created_by
 */
class JournalEntry extends Model
{
    protected $table = 'journal_entries';

    protected $fillable = [
        'entry_no',
        'date',
        'description',
        'reference_type',
        'reference_id',
        'status',
        'created_by',
    ];

    protected $casts = [
        'date'   => 'date',
        'status' => JournalStatus::class,
    ];

    // ─── Relationships ──────────────────────────────────────────────────────────

    public function lines(): HasMany
    {
        return $this->hasMany(JournalEntryLine::class, 'journal_entry_id');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    // ─── Scopes ─────────────────────────────────────────────────────────────────

    public function scopePosted(Builder $query): Builder
    {
        return $query->where('status', JournalStatus::Posted->value);
    }

    public function scopeForReference(Builder $query, string $type, int $id): Builder
    {
        return $query->where('reference_type', $type)->where('reference_id', $id);
    }

    // ─── Helpers ────────────────────────────────────────────────────────────────

    /**
     * Check that this entry is balanced (sum of debits equals sum of credits).
     */
    public function isBalanced(): bool
    {
        $lines      = $this->lines;
        $totalDebit  = $lines->sum('debit');
        $totalCredit = $lines->sum('credit');

        return abs($totalDebit - $totalCredit) < 0.001;
    }

    public function totalDebit(): float
    {
        return (float) $this->lines->sum('debit');
    }

    public function totalCredit(): float
    {
        return (float) $this->lines->sum('credit');
    }

    public function getStatusLabel(): string
    {
        return $this->status->label();
    }
}
