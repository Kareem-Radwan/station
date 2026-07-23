<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('supplier_purchases', function (Blueprint $table) {
            if (!Schema::hasColumn('supplier_purchases', 'invoice_image_path')) {
                $table->string('invoice_image_path', 500)->nullable()->after('notes');
            }
        });
    }

    public function down(): void
    {
        Schema::table('supplier_purchases', function (Blueprint $table) {
            $table->dropColumn('invoice_image_path');
        });
    }
};
