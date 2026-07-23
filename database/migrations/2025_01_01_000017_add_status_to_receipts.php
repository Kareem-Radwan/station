<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('receipts', function (Blueprint $table) {
            if (!Schema::hasColumn('receipts', 'status')) {
                $table->enum('status', ['pending', 'done'])->default('pending')->after('description');
            }
            if (!Schema::hasColumn('receipts', 'signed_image_path')) {
                $table->string('signed_image_path', 500)->nullable()->after('status');
            }
        });
    }

    public function down(): void
    {
        Schema::table('receipts', function (Blueprint $table) {
            $table->dropColumn(['status', 'signed_image_path']);
        });
    }
};
