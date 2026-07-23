<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('customers', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('phone', 20)->nullable();
            $table->text('address')->nullable();
            $table->string('location')->nullable();
            $table->text('notes')->nullable();
            $table->enum('concrete_type', ['operational', 'complete'])->default('operational');
            $table->enum('payment_type', ['cash', 'credit', 'mixed'])->default('cash');
            $table->decimal('cement_balance', 12, 3)->default(0);
            $table->integer('concrete_strength')->nullable();
            $table->decimal('cement_content', 8, 3)->nullable();
            $table->boolean('is_active')->default(true);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index('concrete_type');
            $table->index('is_active');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('customers');
    }
};
