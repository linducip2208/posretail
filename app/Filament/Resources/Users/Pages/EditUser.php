<?php

namespace App\Filament\Resources\Users\Pages;

use App\Filament\Resources\Users\UserResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Resources\Pages\EditRecord;

class EditUser extends EditRecord
{
    protected static string $resource = UserResource::class;

    protected function getHeaderActions(): array
    {
        $protected = fn ($record): bool => $record
            && ((int) $record->id === (int) auth()->id()
                || ($record->isSuperAdmin() && \App\Models\User::otherActiveSuperAdmins($record)->doesntExist()));

        return [
            DeleteAction::make()->hidden(fn ($record) => $protected($record)),
            ForceDeleteAction::make()->hidden(fn ($record) => $protected($record)),
            RestoreAction::make(),
        ];
    }
}
