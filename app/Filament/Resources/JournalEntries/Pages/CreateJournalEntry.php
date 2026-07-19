<?php

namespace App\Filament\Resources\JournalEntries\Pages;

use App\Filament\Resources\JournalEntries\JournalEntryResource;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;

class CreateJournalEntry extends CreateRecord
{
    protected static string $resource = JournalEntryResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['journal_number'] = $this->generateJournalNumber();
        $data['created_by'] = auth()->id();
        $data['status'] = 'draft';

        return $data;
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

    protected function generateJournalNumber(): string
    {
        $prefix = 'JRN-' . now()->format('Ymd') . '-';

        $lastNumber = \App\Models\JournalEntry::query()
            ->where('journal_number', 'like', $prefix . '%')
            ->orderBy('journal_number', 'desc')
            ->value('journal_number');

        if ($lastNumber) {
            $sequence = (int) substr($lastNumber, -4) + 1;
        } else {
            $sequence = 1;
        }

        return $prefix . str_pad((string) $sequence, 4, '0', STR_PAD_LEFT);
    }
}
