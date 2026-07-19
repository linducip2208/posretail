<?php

namespace App\Filament\Resources\CustomerDeposits\Pages;

use App\Filament\Resources\CustomerDeposits\CustomerDepositResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditCustomerDeposit extends EditRecord
{
    protected static string $resource = CustomerDepositResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
