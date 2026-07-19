<?php

namespace App\Filament\Resources\SupplierRatings\Pages;

use App\Filament\Resources\SupplierRatings\SupplierRatingResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListSupplierRatings extends ListRecords
{
    protected static string $resource = SupplierRatingResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
