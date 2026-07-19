<?php

namespace App\Filament\Resources\SupplierContracts\Pages;

use App\Filament\Resources\SupplierContracts\SupplierContractResource;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;

class EditSupplierContract extends EditRecord
{
    protected static string $resource = SupplierContractResource::class;

    protected function getHeaderActions(): array
    {
        return [
            $this->getTerminateAction(),
            DeleteAction::make(),
        ];
    }

    protected function getTerminateAction(): Action
    {
        return Action::make('terminate')
            ->label('Hentikan Kontrak')
            ->icon('heroicon-o-x-circle')
            ->color('danger')
            ->visible(fn (): bool => $this->record->status === 'active')
            ->requiresConfirmation()
            ->modalHeading('Hentikan Kontrak?')
            ->modalDescription('Kontrak ini akan dihentikan secara permanen.')
            ->action(function () {
                $this->record->update(['status' => 'terminated']);

                Notification::make()
                    ->title('Kontrak dihentikan')
                    ->success()
                    ->send();

                $this->redirect($this->getResource()::getUrl('edit', ['record' => $this->record]));
            });
    }
}
