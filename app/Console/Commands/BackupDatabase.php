<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class BackupDatabase extends Command
{
    protected $signature = 'pos:backup-database';

    protected $description = 'Backup database to storage/backups';

    public function handle(): int
    {
        $connection = config('database.default');
        $config = config("database.connections.{$connection}");

        $backupDir = storage_path('backups');
        if (! is_dir($backupDir)) {
            mkdir($backupDir, 0755, true);
        }

        $filename = 'backup-' . now()->format('Y-m-d-H-i-s') . '.sql';
        $filepath = $backupDir . DIRECTORY_SEPARATOR . $filename;

        $command = sprintf(
            'mysqldump --user=%s --host=%s --port=%s %s > %s',
            escapeshellarg($config['username']),
            escapeshellarg($config['host']),
            escapeshellarg($config['port'] ?? '3306'),
            escapeshellarg($config['database']),
            escapeshellarg($filepath)
        );

        if (! empty($config['password'])) {
            $command = sprintf(
                'set MYSQL_PWD=%s && %s',
                escapeshellarg($config['password']),
                $command
            );
        }

        exec($command, $output, $exitCode);

        if ($exitCode === 0 && file_exists($filepath)) {
            $size = round(filesize($filepath) / 1024 / 1024, 2);
            $this->info("Backup berhasil: {$filename} ({$size} MB)");

            $this->cleanOldBackups($backupDir);
        } else {
            $this->error("Backup gagal dengan exit code: {$exitCode}");
        }

        return $exitCode === 0 ? self::SUCCESS : self::FAILURE;
    }

    protected function cleanOldBackups(string $backupDir): void
    {
        $files = glob($backupDir . DIRECTORY_SEPARATOR . 'backup-*.sql');
        rsort($files);

        $maxBackups = 30;
        foreach (array_slice($files, $maxBackups) as $file) {
            unlink($file);
            $this->line("Deleted old backup: " . basename($file));
        }
    }
}
