<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('expenses', function (Blueprint $table) {
            $table->id();
            $table->enum('category', [
                'rental', 'rental_maintenance', 'vehicle_equipment',
                'plant_maintenance', 'salaries', 'overtime',
                'employee_deductions', 'land_rent'
            ]);
            $table->decimal('amount', 14, 2);
            $table->date('expense_date');
            $table->text('description')->nullable();
            $table->string('reference_type', 50)->nullable();
            $table->unsignedBigInteger('reference_id')->nullable();
            $table->foreignId('recorded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index('expense_date');
            $table->index('category');
        });

        Schema::create('land_rents', function (Blueprint $table) {
            $table->id();
            $table->string('description');
            $table->decimal('annual_amount', 12, 2);
            $table->date('due_date');
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('land_rent_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('land_rent_id')->constrained()->cascadeOnDelete();
            $table->date('payment_date');
            $table->decimal('amount', 12, 2);
            $table->text('notes')->nullable();
            $table->foreignId('recorded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('land_rent_payments');
        Schema::dropIfExists('land_rents');
        Schema::dropIfExists('expenses');
    }
};
