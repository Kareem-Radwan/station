<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::statement("
            ALTER TABLE equipment
            MODIFY COLUMN type ENUM(
                'loader',
                'mixer',
                'service_vehicle',
                'pump',
                'generator'
            ) NOT NULL
        ");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("
            ALTER TABLE equipment
            MODIFY COLUMN type ENUM(
                'loader',
                'mixer',
                'service_vehicle',
                'pump'
            ) NOT NULL
        ");
    }
};
