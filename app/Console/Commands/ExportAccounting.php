<?php

namespace App\Console\Commands;

use App\Models\JournalEntry;
use App\Models\JournalEntryItem;
use Illuminate\Console\Command;

class ExportAccounting extends Command
{
    protected $signature = 'pos:export-accounting {format=accurate : accurate|jurnal_id}';

    protected $description = 'Export journal entries to CSV for Accurate Online or Jurnal.id';

    public function handle(): int
    {
        $format = strtolower($this->argument('format'));

        if (!in_array($format, ['accurate', 'jurnal_id'])) {
            $this->error('Format tidak valid. Gunakan: accurate atau jurnal_id');
            return self::FAILURE;
        }

        $exportDir = storage_path('app/exports/accounting');
        if (!is_dir($exportDir)) {
            mkdir($exportDir, 0755, true);
        }

        $filename = "journal-export-{$format}-" . now()->format('Y-m-d-H-i-s') . '.csv';
        $filepath = $exportDir . DIRECTORY_SEPARATOR . $filename;

        $handle = fopen($filepath, 'w');

        if ($format === 'accurate') {
            fputcsv($handle, ['date', 'account_code', 'account_name', 'description', 'debit', 'credit']);
        } else {
            fputcsv($handle, ['transaction_date', 'account_code', 'account_name', 'description', 'debit', 'credit']);
        }

        $journalEntries = JournalEntry::with(['items.account'])
            ->where('status', 'posted')
            ->orderBy('journal_date')
            ->orderBy('created_at')
            ->get();

        $count = 0;

        foreach ($journalEntries as $journal) {
            foreach ($journal->items as $item) {
                $account = $item->account;
                $row = [
                    $journal->journal_date->format('Y-m-d'),
                    $account?->code ?? '',
                    $account?->name ?? '',
                    $journal->description . ' (' . $journal->journal_number . ')',
                    $item->debit > 0 ? (string) $item->debit : '0',
                    $item->credit > 0 ? (string) $item->credit : '0',
                ];
                fputcsv($handle, $row);
                $count++;
            }
        }

        fclose($handle);

        $this->info("Export selesai: {$filename}");
        $this->info("Format: " . ($format === 'accurate' ? 'Accurate Online' : 'Jurnal.id'));
        $this->info("Total baris: {$count}");
        $this->info("Lokasi: {$filepath}");

        return self::SUCCESS;
    }
}
