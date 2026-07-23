<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('employee_borrows', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained()->cascadeOnDelete();
            $table->decimal('amount', 12, 2);
            $table->decimal('remaining_amount', 12, 2);
            $table->date('borrow_date');
            $table->text('reason')->nullable();
            $table->enum('status', ['active', 'paid'])->default('active');
            $table->foreignId('recorded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['employee_id', 'status']);
            $table->index('borrow_date');
        });

        Schema::create('employee_borrow_deductions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('borrow_id')->constrained('employee_borrows')->cascadeOnDelete();
            $table->foreignId('payroll_id')->constrained('payroll')->cascadeOnDelete();
            $table->decimal('amount', 12, 2);
            $table->date('deduction_date');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('employee_borrow_deductions');
        Schema::dropIfExists('employee_borrows');
    }
};
