<?php

namespace App\Filament\Resources\GiftCards\Pages;

use App\Filament\Resources\GiftCards\GiftCardResource;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Pages\ListRecords;

class ListGiftCards extends ListRecords
{
    protected static string $resource = GiftCardResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
            Action::make('batchGenerate')
                ->label('Generate Massal')
                ->icon('heroicon-o-queue-list')
                ->color('success')
                ->form([
                    TextInput::make('count')
                        ->required()
                        ->numeric()
                        ->minValue(1)
                        ->maxValue(100)
                        ->default(10)
                        ->label('Jumlah Voucher'),
                ])
                ->action(function (array $data) {
                    return redirect()->route('filament.admin.resources.gift-cards.create', [
                        'batch_count' => $data['count'],
                    ]);
                }),
        ];
    }
}
