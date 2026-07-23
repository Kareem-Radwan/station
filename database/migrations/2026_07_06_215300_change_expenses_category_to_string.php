<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('expenses', function (Blueprint $table) {
            $table->string('category', 255)->change();
            
            if (!Schema::hasColumn('expenses', 'notes')) {
                $table->text('notes')->nullable()->after('description');
            }
        });
    }

    public function down(): void
    {
        Schema::table('expenses', function (Blueprint $table) {
            $table->enum('category', [
                'rental', 'rental_maintenance', 'vehicle_equipment',
                'plant_maintenance', 'salaries', 'overtime',
                'employee_deductions', 'land_rent'
            ])->change();
        });
    }
};
