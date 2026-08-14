<?php

namespace App\Filament\Resources\Returs\Pages;

use App\Filament\Resources\Returs\ReturResource;
use Filament\Resources\Pages\CreateRecord;

class CreateRetur extends CreateRecord
{
    protected static string $resource = ReturResource::class;

    protected function afterCreate(): void
    {
        if ($this->record->status === 'completed') {
            $this->record->applyReturn();
        }
    }
}
