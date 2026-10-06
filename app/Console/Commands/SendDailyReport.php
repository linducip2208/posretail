<?php

namespace App\Console\Commands;

use App\Models\Outlet;
use App\Models\User;
use App\Services\AdvancedReportService;
use App\Services\SimpleXlsxService;
use Filament\Notifications\Notification;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SendDailyReport extends Command
{
    protected $signature = 'pos:send-daily-report {--date= : Tanggal Y-m-d, default kemarin} {--outlet= : ID outlet opsional} {--email= : Kirim file ke email ini}';

    protected $description = 'Kirim ringkasan laporan harian + file XLSX komprehensif (otomatisasi laporan)';

    public function handle(): int
    {
        $date = $this->option('date') ?: now()->subDay()->format('Y-m-d');
        $outletId = $this->option('outlet') ? (int) $this->option('outlet') : null;

        $start = now()->parse($date)->subDays(30)->format('Y-m-d');
        $summary = AdvancedReportService::dailySummary($outletId, $date);

        $outletName = $outletId ? (Outlet::find($outletId)?->name ?? "Outlet #{$outletId}") : 'Semua Outlet';
        $lines = [
            "*Laporan Harian {$summary['date']} — {$outletName}*",
            "Transaksi: {$summary['trx']}",
            'Omzet: Rp '.number_format($summary['omzet'], 0, ',', '.'),
            'Diskon: Rp '.number_format($summary['diskon'], 0, ',', '.'),
            'Hutang jatuh tempo: '.$summary['overduePayables'],
            'Produk stok menipis: '.$summary['lowStock'],
        ];
        if ($summary['top']->isNotEmpty()) {
            $lines[] = 'Top 3: '.$summary['top']->map(fn ($t) => "{$t->nama} ({$t->qty})")->join(', ');
        }
        $message = implode("\n", $lines);

        // 1. Notifikasi database ke owner/manager.
        $recipients = User::whereIn('role', ['owner', 'manager', 'admin'])->get();
        foreach ($recipients as $user) {
            Notification::make()->title("Laporan Harian {$summary['date']}")->body($message)->success()->sendToDatabase($user);
        }

        // 2. Build file XLSX komprehensif 6 sheet.
        $file = SimpleXlsxService::buildMulti("laporan-komprehensif-{$start}-sd-{$date}.xlsx", [
            ['name' => 'Ringkasan', 'headers' => ['Metrik', 'Nilai'], 'rows' => [
                ['Tanggal', $summary['date']], ['Transaksi', $summary['trx']],
                ['Omzet', $summary['omzet']], ['Diskon', $summary['diskon']],
                ['Hutang jatuh tempo', $summary['overduePayables']], ['Stok menipis', $summary['lowStock']],
            ], 'numericColumns' => ['B']],
            ['name' => 'Profit Produk', 'headers' => AdvancedReportService::profitHeaders(), 'rows' => AdvancedReportService::profitRows($start, $date, $outletId), 'numericColumns' => AdvancedReportService::profitNumericColumns()],
            ['name' => 'Arus Kas', 'headers' => AdvancedReportService::cashflowHeaders(), 'rows' => AdvancedReportService::cashflowRows($start, $date, $outletId), 'numericColumns' => AdvancedReportService::cashflowNumericColumns()],
            ['name' => 'Hutang Supplier', 'headers' => AdvancedReportService::payableHeaders(), 'rows' => AdvancedReportService::payableRows($outletId), 'numericColumns' => AdvancedReportService::payableNumericColumns()],
            ['name' => 'Pelanggan RFM', 'headers' => AdvancedReportService::rfmHeaders(), 'rows' => AdvancedReportService::rfmRows($start, $date, $outletId), 'numericColumns' => AdvancedReportService::rfmNumericColumns()],
            ['name' => 'Efek Diskon', 'headers' => AdvancedReportService::promoHeaders(), 'rows' => AdvancedReportService::promoRows($start, $date, $outletId), 'numericColumns' => AdvancedReportService::promoNumericColumns()],
        ]);

        // 3. Kirim email jika diminta.
        if ($email = $this->option('email')) {
            try {
                Mail::raw($message, function ($m) use ($email, $file, $date) {
                    $m->to($email)->subject("Laporan Harian {$date}")->attach($file);
                });
                $this->info("Emailed to {$email}");
            } catch (\Throwable $e) {
                Log::warning('Daily report email failed: '.$e->getMessage());
                $this->warn('Email gagal: '.$e->getMessage());
            }
        }

        $this->info("Daily report {$date}: trx={$summary['trx']} omzet={$summary['omzet']} file={$file} notif=".count($recipients));

        return self::SUCCESS;
    }
}
