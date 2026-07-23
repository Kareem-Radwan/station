<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mix_recipes', function (Blueprint $table) {
            $table->id();
            $table->integer('cement_per_m3')->unique()->comment('كمية الاسمنت لكل متر مكعب (كجم)');
            $table->decimal('sand_kg', 10, 3)->comment('كمية الرمل بالكيلوغرام');
            $table->decimal('gravel1_kg', 10, 3)->comment('كمية الحصى 1 بالكيلوغرام');
            $table->decimal('gravel2_kg', 10, 3)->comment('كمية الحصى 2 بالكيلوغرام');
            $table->decimal('cement_kg', 10, 3)->comment('كمية الاسمنت بالكيلوغرام');
            $table->decimal('water_m3', 10, 3)->comment('كمية الماء بالمتر المكعب');
            $table->decimal('additives_liter', 10, 3)->comment('كمية المضافات باللتر');
            $table->text('notes')->nullable()->comment('ملاحظات');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mix_recipes');
    }
};
