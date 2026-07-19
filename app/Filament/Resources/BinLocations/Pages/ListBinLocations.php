<?php

namespace App\Filament\Resources\BinLocations\Pages;

use App\Filament\Resources\BinLocations\BinLocationResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListBinLocations extends ListRecords
{
    protected static string $resource = BinLocationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
