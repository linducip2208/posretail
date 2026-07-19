<?php

namespace App\Filament\Resources\CustomerDeposits\Pages;

use App\Filament\Resources\CustomerDeposits\CustomerDepositResource;
use App\Models\Customer;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\DB;

class CreateCustomerDeposit extends CreateRecord
{
    protected static string $resource = CustomerDepositResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['user_id'] = auth()->id();

        $customer = Customer::findOrFail($data['customer_id']);

        if ($data['type'] === 'topup') {
            $customer->deposit_balance += $data['amount'];
        } elseif (in_array($data['type'], ['deduct', 'refund'])) {
            $customer->deposit_balance -= $data['amount'];
        }

        $customer->save();
        $data['balance_after'] = $customer->deposit_balance;

        return $data;
    }
}
