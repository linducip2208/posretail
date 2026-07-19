<?php

namespace App\Filament\Resources\BinLocations\Pages;

use App\Filament\Resources\BinLocations\BinLocationResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditBinLocation extends EditRecord
{
    protected static string $resource = BinLocationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
