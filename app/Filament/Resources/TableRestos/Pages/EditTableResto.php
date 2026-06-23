<?php

namespace App\Filament\Resources\TableRestos\Pages;

use App\Filament\Resources\TableRestos\TableRestoResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Resources\Pages\EditRecord;

class EditTableResto extends EditRecord
{
    protected static string $resource = TableRestoResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
            ForceDeleteAction::make(),
            RestoreAction::make(),
        ];
    }
}
