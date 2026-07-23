<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('receipts', function (Blueprint $table) {
            if (!Schema::hasColumn('receipts', 'type')) {
                $table->enum('type', ['in', 'out'])->default('in')->after('id');
            }
            if (!Schema::hasColumn('receipts', 'amount')) {
                $table->decimal('amount', 14, 2)->default(0)->after('type');
            }
            if (!Schema::hasColumn('receipts', 'recipient_name')) {
                $table->string('recipient_name', 255)->nullable()->after('amount');
            }
            if (!Schema::hasColumn('receipts', 'description')) {
                $table->text('description')->nullable()->after('recipient_name');
            }
        });
    }

    public function down(): void
    {
        Schema::table('receipts', function (Blueprint $table) {
            $table->dropColumn(['type', 'amount', 'recipient_name', 'description']);
        });
    }
};
