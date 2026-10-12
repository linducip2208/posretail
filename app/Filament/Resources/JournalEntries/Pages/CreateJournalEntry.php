<?php

namespace App\Filament\Resources\JournalEntries\Pages;

use App\Filament\Resources\JournalEntries\JournalEntryResource;
use App\Models\JournalEntry;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\QueryException;

class CreateJournalEntry extends CreateRecord
{
    protected static string $resource = JournalEntryResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['journal_number'] = JournalEntry::generateNextNumber();
        $data['created_by'] = auth()->id();
        $data['status'] = 'draft';

        return $data;
    }

    /**
     * Retry dengan nomor baru bila terjadi balapan nomor jurnal
     * (dua user simpan bersamaan). SELECT ... FOR UPDATE adalah
     * current-read sehingga melihat row kompetitor yang baru commit.
     */
    protected function handleRecordCreation(array $data): Model
    {
        for ($attempt = 1; $attempt <= 5; $attempt++) {
            try {
                $data['journal_number'] = JournalEntry::generateNextNumber(withLock: true);

                return parent::handleRecordCreation($data);
            } catch (QueryException $e) {
                if (! JournalEntry::isDuplicateKeyException($e) || $attempt >= 5) {
                    throw $e;
                }

                usleep(50000 * $attempt);
            }
        }

        return parent::handleRecordCreation($data);
    }

    protected function beforeCreate(): void
    {
        $items = $this->data['items'] ?? [];

        $totalDebit = collect($items)->sum(function ($item) {
            return (float) ($item['debit'] ?? 0);
        });

        $totalCredit = collect($items)->sum(function ($item) {
            return (float) ($item['credit'] ?? 0);
        });

        if (abs($totalDebit - $totalCredit) > 0.001) {
            Notification::make()
                ->title('Total debit dan kredit harus seimbang')
                ->body('Debit: ' . number_format($totalDebit, 2) . ' | Kredit: ' . number_format($totalCredit, 2))
                ->danger()
                ->send();

            $this->halt();
        }
    }
}
