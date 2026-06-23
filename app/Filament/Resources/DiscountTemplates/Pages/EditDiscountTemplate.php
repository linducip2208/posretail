<?php

namespace App\Filament\Resources\DiscountTemplates\Pages;

use App\Filament\Resources\DiscountTemplates\DiscountTemplateResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Resources\Pages\EditRecord;

class EditDiscountTemplate extends EditRecord
{
    protected static string $resource = DiscountTemplateResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
            ForceDeleteAction::make(),
            RestoreAction::make(),
        ];
    }
}
