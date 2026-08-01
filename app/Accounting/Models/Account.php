<?php

namespace App\Accounting\Models;

use App\Accounting\Enums\AccountType;
use App\Accounting\Enums\NormalBalance;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Builder;

/**
 * Chart of Accounts entry.
 *
 * @property int         $id
 * @property int|null    $parent_id
 * @property int         $level
 * @property string      $account_number
 * @property string      $account_name
 * @property AccountType $account_type
 * @property NormalBalance $normal_balance
 * @property bool        $is_postable
 * @property bool        $is_active
 */
class Account extends Model
{
    protected $table = 'accounts';

    protected $fillable = [
        'parent_id',
        'level',
        'account_number',
        'account_name',
        'account_type',
        'normal_balance',
        'is_postable',
        'is_active',
    ];

    protected $casts = [
        'account_type'   => AccountType::class,
        'normal_balance' => NormalBalance::class,
        'is_postable'    => 'boolean',
        'is_active'      => 'boolean',
        'level'          => 'integer',
    ];

    // ─── Relationships ──────────────────────────────────────────────────────────

    public function parent(): BelongsTo
    {
        return $this->belongsTo(Account::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(Account::class, 'parent_id');
    }

    public function journalLines(): HasMany
    {
        return $this->hasMany(JournalEntryLine::class, 'account_id');
    }

    // ─── Scopes ─────────────────────────────────────────────────────────────────

    public function scopePostable(Builder $query): Builder
    {
        return $query->where('is_postable', true)->where('is_active', true);
    }

    public function scopeByType(Builder $query, AccountType|string $type): Builder
    {
        $value = $type instanceof AccountType ? $type->value : $type;
        return $query->where('account_type', $value);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    // ─── Helpers ────────────────────────────────────────────────────────────────

    /**
     * Returns the net balance for this account from the journal_entry_lines table.
     * Sign is positive when the balance is on the normal side.
     */
    public function netBalance(?string $fromDate = null, ?string $toDate = null): float
    {
        $query = $this->journalLines()
            ->join('journal_entries', 'journal_entries.id', '=', 'journal_entry_lines.journal_entry_id')
            ->where('journal_entries.status', 'posted');

        if ($fromDate) {
            $query->where('journal_entries.date', '>=', $fromDate);
        }
        if ($toDate) {
            $query->where('journal_entries.date', '<=', $toDate);
        }

        $debit  = (float) $query->sum('journal_entry_lines.debit');
        $credit = (float) $query->sum('journal_entry_lines.credit');

        return $this->normal_balance === NormalBalance::Debit
            ? $debit - $credit
            : $credit - $debit;
    }

    public function getTypeLabel(): string
    {
        return $this->account_type->label();
    }
}
