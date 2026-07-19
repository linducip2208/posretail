<?php

namespace App\Filament\Resources\JournalEntries\Pages;

use App\Filament\Resources\JournalEntries\JournalEntryResource;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;

class EditJournalEntry extends EditRecord
{
    protected static string $resource = JournalEntryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('post')
                ->label('Posting')
                ->icon('heroicon-o-check-circle')
                ->color('success')
                ->visible(fn (): bool => $this->getRecord()->status === 'draft')
                ->requiresConfirmation()
                ->modalHeading('Posting Jurnal')
                ->modalDescription('Posting jurnal ini? Status tidak dapat dikembalikan ke draft.')
                ->action(function (): void {
                    $this->getRecord()->update([
                        'status' => 'posted',
                        'posted_by' => auth()->id(),
                        'posted_at' => now(),
                    ]);

                    Notification::make()
                        ->title('Jurnal berhasil diposting')
                        ->success()
                        ->send();
                }),

            Action::make('void')
                ->label('Void')
                ->icon('heroicon-o-x-circle')
                ->color('danger')
                ->visible(fn (): bool => $this->getRecord()->status === 'posted')
                ->requiresConfirmation()
                ->modalHeading('Void Jurnal')
                ->modalDescription('Void jurnal ini? Status akan berubah menjadi void.')
                ->action(function (): void {
                    $this->getRecord()->update([
                        'status' => 'voided',
                    ]);

                    Notification::make()
                        ->title('Jurnal berhasil di-void')
                        ->success()
                        ->send();
                }),

            DeleteAction::make(),
        ];
    }

    protected function beforeSave(): void
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
