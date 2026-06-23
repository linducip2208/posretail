<?php

namespace App\Filament\Resources\Returs\Pages;

use App\Filament\Resources\Returs\ReturResource;
use Filament\Resources\Pages\CreateRecord;

class CreateRetur extends CreateRecord
{
    protected static string $resource = ReturResource::class;

    protected function afterCreate(): void
    {
        $retur = $this->record;

        foreach ($retur->returnItems as $item) {
            $product = $item->product;
            if ($product) {
                $product->increment('current_stock', $item->quantity);
            }
        }
    }
}
