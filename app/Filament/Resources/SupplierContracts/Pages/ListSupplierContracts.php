<?php

namespace App\Filament\Resources\SupplierContracts\Pages;

use App\Filament\Resources\SupplierContracts\SupplierContractResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListSupplierContracts extends ListRecords
{
    protected static string $resource = SupplierContractResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
