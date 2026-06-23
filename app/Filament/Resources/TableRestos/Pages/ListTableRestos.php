<?php

namespace App\Filament\Resources\TableRestos\Pages;

use App\Filament\Resources\TableRestos\TableRestoResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListTableRestos extends ListRecords
{
    protected static string $resource = TableRestoResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
