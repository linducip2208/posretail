<?php

namespace App\Filament\Resources\SupplierReturns\Pages;

use App\Filament\Resources\SupplierReturns\SupplierReturnResource;
use App\Models\SupplierReturn;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;

class CreateSupplierReturn extends CreateRecord
{
    protected static string $resource = SupplierReturnResource::class;

    protected function handleRecordCreation(array $data): Model
    {
        $data['return_number'] = SupplierReturn::generateNumber();
        $data['user_id'] = auth()->id();
        $data['total_amount'] = 0;

        return static::getModel()::create($data);
    }
}
