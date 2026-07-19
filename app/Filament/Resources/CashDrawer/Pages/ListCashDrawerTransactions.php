<?php

namespace App\Filament\Resources\CashDrawer\Pages;

use App\Filament\Resources\CashDrawer\CashDrawerTransactionResource;
use Filament\Resources\Pages\ListRecords;

class ListCashDrawerTransactions extends ListRecords
{
    protected static string $resource = CashDrawerTransactionResource::class;
}
