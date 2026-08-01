<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('journal_entry_lines', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('journal_entry_id');
            $table->foreign('journal_entry_id')
                  ->references('id')->on('journal_entries')
                  ->cascadeOnDelete();

            $table->unsignedBigInteger('account_id');
            $table->foreign('account_id')
                  ->references('id')->on('accounts')
                  ->restrictOnDelete();

            // One of debit or credit must be non-zero; the other must be zero.
            // Enforced at the application layer (JournalEntryService).
            $table->decimal('debit', 15, 2)->default(0);
            $table->decimal('credit', 15, 2)->default(0);

            $table->string('description', 300)->nullable();

            $table->timestamps();

            // Performance indexes — Trial Balance reads SUM(debit), SUM(credit) grouped by account_id
            $table->index('account_id');
            $table->index('journal_entry_id');
            $table->index(['account_id', 'debit', 'credit'], 'jel_aggregation_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('journal_entry_lines');
    }
};
