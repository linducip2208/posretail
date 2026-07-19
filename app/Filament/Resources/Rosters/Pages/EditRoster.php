<?php

namespace App\Filament\Resources\Rosters\Pages;

use App\Filament\Resources\Rosters\RosterResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditRoster extends EditRecord
{
    protected static string $resource = RosterResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
