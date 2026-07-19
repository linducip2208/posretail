<?php

namespace App\Console\Commands;

use App\Models\AuditLog;
use App\Models\Notification;
use Illuminate\Console\Command;

class ArchiveOldData extends Command
{
    protected $signature = 'pos:archive-data {--days=180 : Hapus data lebih tua dari N hari}';

    protected $description = 'Archive/purge old audit logs and read notifications';

    public function handle(): int
    {
        $days = (int) $this->option('days');
        $cutoff = now()->subDays($days);

        $auditCount = AuditLog::where('created_at', '<', $cutoff)->count();
        if ($auditCount > 0) {
            AuditLog::where('created_at', '<', $cutoff)->delete();
            $this->info("Purged {$auditCount} audit logs older than {$days} days.");
        }

        $notifCount = Notification::where('created_at', '<', $cutoff)
            ->whereNotNull('read_at')
            ->count();
        if ($notifCount > 0) {
            Notification::where('created_at', '<', $cutoff)
                ->whereNotNull('read_at')
                ->delete();
            $this->info("Purged {$notifCount} read notifications older than {$days} days.");
        }

        if ($auditCount === 0 && $notifCount === 0) {
            $this->info('Nothing to archive.');
        }

        return self::SUCCESS;
    }
}
