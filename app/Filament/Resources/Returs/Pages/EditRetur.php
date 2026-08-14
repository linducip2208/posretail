<?php

namespace App\Filament\Resources\Returs\Pages;

use App\Filament\Resources\Returs\ReturResource;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;

class EditRetur extends EditRecord
{
    protected static string $resource = ReturResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('complete')
                ->label('Selesaikan Retur')
                ->icon('heroicon-o-check-circle')
                ->color('success')
                ->requiresConfirmation()
                ->visible(fn (): bool => in_array($this->record->status, ['pending', 'approved'], true))
                ->action(function () {
                    $this->record->loadMissing('returnItems.product');
                    $this->record->update(['status' => 'completed']);
                    $this->record->applyReturn();

                    Notification::make()
                        ->title('Retur diselesaikan')
                        ->body('Stok, jurnal, dan serial sudah dikembalikan.')
                        ->success()
                        ->send();

                    $this->redirect($this->getResource()::getUrl('edit', ['record' => $this->record]));
                }),
            DeleteAction::make(),
        ];
    }
}
