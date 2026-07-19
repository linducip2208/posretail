<?php

namespace App\Filament\Resources\Deliveries\Pages;

use App\Filament\Resources\Deliveries\DeliveryResource;
use Filament\Resources\Pages\CreateRecord;

class CreateDelivery extends CreateRecord
{
    protected static string $resource = DeliveryResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['delivery_number'] = 'DLV-' . date('Ymd') . '-' . str_pad(
            \App\Models\Delivery::whereDate('created_at', today())->count() + 1,
            4, '0', STR_PAD_LEFT
        );
        $data['status'] = 'pending';

        return $data;
    }
}
