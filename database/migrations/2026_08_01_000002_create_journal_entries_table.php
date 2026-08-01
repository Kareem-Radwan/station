<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('journal_entries', function (Blueprint $table) {
            $table->id();

            // Unique sequential reference (e.g. JE-2026-00001)
            $table->string('entry_no', 30)->unique();

            // Date of economic event (may differ from created_at)
            $table->date('date')->index();

            $table->string('description', 500)->nullable();

            // Polymorphic pointer to the source business record
            $table->string('reference_type', 80)->nullable()->index();
            $table->unsignedBigInteger('reference_id')->nullable();
            $table->index(['reference_type', 'reference_id'], 'je_reference_idx');

            // Lifecycle
            $table->enum('status', ['draft', 'posted', 'voided'])->default('posted')->index();

            $table->unsignedBigInteger('created_by')->nullable();
            $table->foreign('created_by')->references('id')->on('users')->nullOnDelete();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('journal_entries');
    }
};
