<?php

namespace App\Filament\Resources\VolumePricings\Pages;

use App\Filament\Resources\VolumePricings\VolumePricingResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListVolumePricings extends ListRecords
{
    protected static string $resource = VolumePricingResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
