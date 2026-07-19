<?php

namespace App\Filament\Resources\Assets\Pages;

use App\Filament\Resources\Assets\CompanyAssetResource;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;

class EditAsset extends EditRecord
{
    protected static string $resource = CompanyAssetResource::class;

    protected function getHeaderActions(): array
    {
        return [
            $this->getDisposeAction(),
            DeleteAction::make(),
        ];
    }

    protected function getDisposeAction(): Action
    {
        return Action::make('dispose')
            ->label('Hapus Aset')
            ->icon('heroicon-o-trash')
            ->color('danger')
            ->visible(fn (): bool => $this->record->status === 'active')
            ->requiresConfirmation()
            ->modalHeading('Hapus Aset?')
            ->modalDescription('Aset ini akan ditandai sebagai dihapus dan tidak dapat digunakan kembali.')
            ->action(function () {
                $this->record->update(['status' => 'disposed']);

                Notification::make()
                    ->title('Aset berhasil dihapus')
                    ->success()
                    ->send();

                $this->redirect($this->getResource()::getUrl('edit', ['record' => $this->record]));
            });
    }
}
