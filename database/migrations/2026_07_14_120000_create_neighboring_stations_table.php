<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('neighboring_stations', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('contact_person')->nullable();
            $table->string('phone')->nullable();
            $table->text('address')->nullable();
            $table->text('notes')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('neighboring_station_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('neighboring_station_id')->constrained()->cascadeOnDelete();
            $table->enum('transaction_type', ['rent_equipment', 'rent_vehicle', 'borrow_material', 'borrow_inventory', 'sell_concrete', 'service']);
            $table->enum('direction', ['incoming', 'outgoing']); // incoming = we receive payment, outgoing = we pay
            $table->date('transaction_date');
            $table->decimal('amount', 12, 2);
            $table->text('description');
            $table->string('reference_number')->nullable();
            $table->text('notes')->nullable();
            $table->enum('payment_status', ['paid', 'pending', 'partial'])->default('pending');
            $table->decimal('paid_amount', 12, 2)->default(0);
            $table->foreignId('recorded_by')->nullable()->constrained('users');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('neighboring_station_transactions');
        Schema::dropIfExists('neighboring_stations');
    }
};
