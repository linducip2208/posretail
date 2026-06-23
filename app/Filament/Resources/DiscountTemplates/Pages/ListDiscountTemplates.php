<?php

namespace App\Filament\Resources\DiscountTemplates\Pages;

use App\Filament\Resources\DiscountTemplates\DiscountTemplateResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListDiscountTemplates extends ListRecords
{
    protected static string $resource = DiscountTemplateResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
