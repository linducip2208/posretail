<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;

class CloudBackup extends Command
{
    protected $signature = 'pos:cloud-backup {--disk=s3 : Storage disk to upload to}';

    protected $description = 'Backup database and upload to cloud storage (S3/DigitalOcean Spaces)';

    public function handle(): int
    {
        $disk = $this->option('disk');

        try {
            Storage::disk($disk)->response();
        } catch (\Exception $e) {
            $this->error("Disk '{$disk}' not configured or unreachable.");
            return self::FAILURE;
        }

        $filename = 'backup-' . now()->format('Ymd-His') . '.sql';
        $tmpPath = storage_path('app/' . $filename);

        // Dump database
        $db = config('database.connections.mysql');
        $command = sprintf(
            'mysqldump -u%s -p%s -h%s -P%d %s > "%s" 2>&1',
            escapeshellarg($db['username']),
            escapeshellarg($db['password']),
            escapeshellarg($db['host']),
            $db['port'] ?? 3306,
            escapeshellarg($db['database']),
            $tmpPath
        );

        $output = null;
        $exitCode = 0;
        exec($command, $output, $exitCode);

        if ($exitCode !== 0) {
            $this->error('mysqldump failed: ' . implode("\n", $output));
            return self::FAILURE;
        }

        $this->info("Database dumped to {$tmpPath} (" . round(filesize($tmpPath) / 1024 / 1024, 2) . " MB)");

        // Compress
        $zipPath = $tmpPath . '.gz';
        $gz = gzopen($zipPath, 'wb9');
        gzwrite($gz, file_get_contents($tmpPath));
        gzclose($gz);
        unlink($tmpPath);

        $this->info("Compressed to {$zipPath} (" . round(filesize($zipPath) / 1024, 2) . " KB)");

        // Upload
        $stream = fopen($zipPath, 'r');
        Storage::disk($disk)->put('backups/' . $filename . '.gz', $stream);
        fclose($stream);
        unlink($zipPath);

        // Clean old backups (keep last 7)
        $files = collect(Storage::disk($disk)->files('backups'))
            ->sort()
            ->reverse()
            ->values();

        foreach ($files->slice(7) as $old) {
            Storage::disk($disk)->delete($old);
            $this->line("Deleted old backup: {$old}");
        }

        $this->info("Backup uploaded to {$disk}: backups/{$filename}.gz");
        $this->info("Total backups stored: " . min($files->count(), 7));

        return self::SUCCESS;
    }
}
