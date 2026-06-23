<?php

namespace App\Filament\Resources\TableAreas\Pages;

use App\Filament\Resources\TableAreas\TableAreaResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Resources\Pages\EditRecord;

class EditTableArea extends EditRecord
{
    protected static string $resource = TableAreaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
            ForceDeleteAction::make(),
            RestoreAction::make(),
        ];
    }
}
