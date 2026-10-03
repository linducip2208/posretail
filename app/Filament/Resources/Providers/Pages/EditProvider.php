<?php

namespace App\Filament\Resources\Providers\Pages;

use App\Filament\Resources\Providers\ProviderResource;
use Filament\Resources\Pages\EditRecord;

class EditProvider extends EditRecord
{
    protected static string $resource = ProviderResource::class;

    protected function mutateFormDataBeforeSave(array $data): array
    {
        if (! empty($data['webhook_secret'])) {
            $data['webhook_secret_encrypted'] = encrypt($data['webhook_secret']);
        }
        unset($data['webhook_secret']);
        return $data;
    }
}
