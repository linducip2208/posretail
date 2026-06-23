<?php

namespace App\Filament\Resources\TableAreas\Pages;

use App\Filament\Resources\TableAreas\TableAreaResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListTableAreas extends ListRecords
{
    protected static string $resource = TableAreaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
