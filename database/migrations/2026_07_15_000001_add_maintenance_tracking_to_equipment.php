<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('equipment', function (Blueprint $table) {
            $table->enum('tracking_type', ['hours', 'days'])->default('days')->after('status');
            $table->integer('maintenance_threshold')->nullable()->after('tracking_type')->comment('Maintenance threshold in hours or days');
            $table->decimal('current_hours', 10, 2)->default(0)->after('maintenance_threshold')->comment('Current accumulated hours');
            $table->integer('current_days')->default(0)->after('current_hours')->comment('Current accumulated days');
            $table->decimal('last_maintenance_at_hours', 10, 2)->nullable()->after('current_days')->comment('Hours value at last maintenance');
            $table->integer('last_maintenance_at_days')->nullable()->after('last_maintenance_at_hours')->comment('Days value at last maintenance');
        });

        Schema::table('equipment_fuel_logs', function (Blueprint $table) {
            $table->decimal('hours_logged', 10, 2)->nullable()->after('total_cost')->comment('Hours logged for this fuel entry');
            $table->integer('days_logged')->nullable()->after('hours_logged')->comment('Days logged for this fuel entry');
        });

        Schema::table('equipment_maintenance', function (Blueprint $table) {
            $table->decimal('hours_at_maintenance', 10, 2)->nullable()->after('cost')->comment('Total hours when maintenance was done');
            $table->integer('days_at_maintenance')->nullable()->after('hours_at_maintenance')->comment('Total days when maintenance was done');
        });
    }

    public function down(): void
    {
        Schema::table('equipment_maintenance', function (Blueprint $table) {
            $table->dropColumn(['hours_at_maintenance', 'days_at_maintenance']);
        });

        Schema::table('equipment_fuel_logs', function (Blueprint $table) {
            $table->dropColumn(['hours_logged', 'days_logged']);
        });

        Schema::table('equipment', function (Blueprint $table) {
            $table->dropColumn([
                'tracking_type',
                'maintenance_threshold',
                'current_hours',
                'current_days',
                'last_maintenance_at_hours',
                'last_maintenance_at_days'
            ]);
        });
    }
};
