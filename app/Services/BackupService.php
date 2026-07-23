<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;

class BackupService
{
    public function backup(): string
    {
        $dbName = config('database.connections.mysql.database');
        $dbUser = config('database.connections.mysql.username');
        $dbPass = config('database.connections.mysql.password');
        $dbHost = config('database.connections.mysql.host');
        $dbPort = config('database.connections.mysql.port', 3306);

        $fileName = 'backup_' . now()->format('Y-m-d_H-i-s') . '.sql';
        $backupDir = storage_path('backups');
        $filePath  = $backupDir . DIRECTORY_SEPARATOR . $fileName;

        if (!is_dir($backupDir)) {
            mkdir($backupDir, 0755, true);
        }

        $passArg = $dbPass ? '--password=' . escapeshellarg($dbPass) : '';

        $command = sprintf(
            'mysqldump --host=%s --port=%s --user=%s %s %s > %s 2>&1',
            escapeshellarg($dbHost),
            escapeshellarg($dbPort),
            escapeshellarg($dbUser),
            $passArg,
            escapeshellarg($dbName),
            escapeshellarg($filePath)
        );

        exec($command, $output, $returnCode);

        if ($returnCode !== 0) {
            Log::error('Database backup failed', compact('output', 'returnCode'));
            throw new \RuntimeException('فشل النسخ الاحتياطي: ' . implode("\n", $output));
        }

        $this->cleanOldBackups(30);
        Log::info("Backup created: {$fileName}");

        return $filePath;
    }

    private function cleanOldBackups(int $keepDays): void
    {
        $files     = glob(storage_path('backups') . '/backup_*.sql') ?: [];
        $cutoff    = now()->subDays($keepDays)->timestamp;

        foreach ($files as $file) {
            if (filemtime($file) < $cutoff) {
                unlink($file);
            }
        }
    }
}
