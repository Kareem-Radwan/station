<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('equipment_tools', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // اسم الأداة (شاكوش، مفك، زيت، إلخ)
            $table->string('unit'); // الوحدة (حبة، برميل، علبة، إلخ)
            $table->decimal('quantity', 15, 2)->default(0); // الكمية المتاحة
            $table->decimal('price_per_unit', 15, 2)->default(0); // سعر الوحدة
            $table->decimal('total_value', 15, 2)->default(0); // القيمة الإجمالية (quantity * price_per_unit)
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('equipment_tool_movements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('equipment_tool_id')->constrained('equipment_tools')->cascadeOnDelete();
            $table->enum('type', ['in', 'out']); // إدخال أو إخراج
            $table->decimal('quantity', 15, 2); // الكمية
            $table->decimal('price_per_unit', 15, 2); // السعر لكل وحدة
            $table->decimal('total_cost', 15, 2); // التكلفة الإجمالية
            $table->decimal('balance_after', 15, 2); // الرصيد بعد العملية
            $table->foreignId('treasury_transaction_id')->nullable()->constrained('treasury_transactions');
            $table->text('notes')->nullable();
            $table->timestamp('movement_date');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('equipment_tool_movements');
        Schema::dropIfExists('equipment_tools');
    }
};
