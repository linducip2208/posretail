<?php

namespace App\Filament\Resources\SupplierRatings\Pages;

use App\Filament\Resources\SupplierRatings\SupplierRatingResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditSupplierRating extends EditRecord
{
    protected static string $resource = SupplierRatingResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
