<?php

namespace App\Filament\Resources\VolumePricings\Pages;

use App\Filament\Resources\VolumePricings\VolumePricingResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditVolumePricing extends EditRecord
{
    protected static string $resource = VolumePricingResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
