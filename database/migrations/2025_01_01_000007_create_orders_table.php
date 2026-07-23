<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->constrained()->restrictOnDelete();
            $table->foreignId('concrete_mix_id')->nullable()->constrained()->nullOnDelete();
            $table->enum('concrete_type', ['operational', 'complete']);
            $table->decimal('quantity_m3', 10, 3);
            $table->decimal('cement_deducted', 10, 3)->nullable();
            $table->string('location')->nullable();
            $table->date('delivery_date');
            $table->time('delivery_time')->nullable();
            $table->enum('status', ['pending', 'scheduled', 'delivered', 'cancelled'])->default('pending');
            $table->text('notes')->nullable();
            $table->decimal('unit_price', 10, 2)->nullable();
            $table->decimal('total_amount', 14, 2)->nullable();
            $table->enum('payment_type', ['cash', 'credit', 'mixed'])->nullable();
            $table->decimal('cash_amount', 14, 2)->nullable();
            $table->decimal('credit_amount', 14, 2)->nullable();
            $table->date('credit_due_date')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index('delivery_date');
            $table->index('status');
            $table->index('customer_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
