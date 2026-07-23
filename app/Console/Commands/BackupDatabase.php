<?php

namespace App\Console\Commands;

use App\Services\BackupService;
use Illuminate\Console\Command;

class BackupDatabase extends Command
{
    protected $signature   = 'db:backup';
    protected $description = 'Create a daily MySQL database backup';

    public function handle(BackupService $backupService): void
    {
        try {
            $path = $backupService->backup();
            $this->info("✅ تم إنشاء النسخة الاحتياطية: {$path}");
        } catch (\Exception $e) {
            $this->error("❌ فشل النسخ الاحتياطي: " . $e->getMessage());
        }
    }
}
