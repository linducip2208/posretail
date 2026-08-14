<?php

namespace App\Filament\Resources\SupplierReturns\Pages;

use App\Filament\Resources\SupplierReturns\SupplierReturnResource;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;

class EditSupplierReturn extends EditRecord
{
    protected static string $resource = SupplierReturnResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('receive')
                ->label('Terima Retur')
                ->icon('heroicon-o-check-circle')
                ->color('success')
                ->requiresConfirmation()
                ->visible(fn (): bool => in_array($this->record->status, ['draft', 'submitted']))
                ->action(function () {
                    $this->record->load('items');
                    $this->record->markReceived();

                    Notification::make()
                        ->title('Retur diterima')
                        ->success()
                        ->send();
                }),
            DeleteAction::make(),
        ];
    }
}
