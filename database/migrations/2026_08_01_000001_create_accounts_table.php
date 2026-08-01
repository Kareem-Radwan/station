<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('accounts', function (Blueprint $table) {
            $table->id();

            // Hierarchy
            $table->unsignedBigInteger('parent_id')->nullable()->index();
            $table->foreign('parent_id')->references('id')->on('accounts')->nullOnDelete();
            $table->unsignedTinyInteger('level')->default(1)->comment('1=root, 2=group, 3=postable');

            // Identity
            $table->string('account_number', 20)->unique();
            $table->string('account_name', 120);

            // Classification
            $table->enum('account_type', ['asset', 'liability', 'equity', 'revenue', 'expense']);
            $table->enum('normal_balance', ['debit', 'credit']);

            // Behaviour
            $table->boolean('is_postable')->default(false)->comment('Only postable accounts can receive journal lines');
            $table->boolean('is_active')->default(true);

            $table->timestamps();

            // Composite indexes for report queries
            $table->index(['account_type', 'is_postable']);
            $table->index(['is_postable', 'is_active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('accounts');
    }
};
