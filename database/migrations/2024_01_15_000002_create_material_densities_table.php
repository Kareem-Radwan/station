<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('material_densities', function (Blueprint $table) {
            $table->id();
            $table->string('material_name')->unique()->comment('اسم المادة');
            $table->string('material_name_ar')->comment('اسم المادة بالعربي');
            $table->decimal('density_kg_per_m3', 10, 3)->comment('الكثافة (كجم/م³)');
            $table->text('notes')->nullable()->comment('ملاحظات');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('material_densities');
    }
};
