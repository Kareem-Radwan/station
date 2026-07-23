<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('weekly_schedules', function (Blueprint $table) {
            $table->id();
            $table->date('week_start');
            $table->date('week_end');
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->enum('status', ['draft', 'published', 'completed'])->default('draft');
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index('week_start');
        });

        Schema::create('weekly_schedule_entries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('weekly_schedule_id')->constrained()->cascadeOnDelete();
            $table->foreignId('order_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('customer_id')->constrained()->restrictOnDelete();
            $table->string('site_location');
            $table->decimal('quantity_m3', 10, 3);
            $table->date('delivery_date');
            $table->time('delivery_time')->nullable();
            $table->text('engineer_notes')->nullable();
            $table->enum('status', ['pending', 'completed', 'cancelled'])->default('pending');
            $table->timestamps();

            $table->index('delivery_date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('weekly_schedule_entries');
        Schema::dropIfExists('weekly_schedules');
    }
};
