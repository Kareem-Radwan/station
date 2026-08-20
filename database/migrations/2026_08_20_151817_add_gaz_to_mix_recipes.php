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
        Schema::table('mix_recipes', function (Blueprint $table) {
            $table->decimal('gaz_liter', 8, 3)->default(0)->after('additives_liter');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('mix_recipes', function (Blueprint $table) {
            $table->dropColumn('gaz_liter');
        });
    }
};
