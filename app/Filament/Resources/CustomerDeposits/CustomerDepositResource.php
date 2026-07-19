<?php

namespace App\Filament\Resources\CustomerDeposits;

use App\Filament\Resources\CustomerDeposits\Pages\CreateCustomerDeposit;
use App\Filament\Resources\CustomerDeposits\Pages\EditCustomerDeposit;
use App\Filament\Resources\CustomerDeposits\Pages\ListCustomerDeposits;
use App\Filament\Resources\CustomerDeposits\Schemas\CustomerDepositForm;
use App\Filament\Resources\CustomerDeposits\Tables\CustomerDepositsTable;
use App\Models\CustomerDeposit;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class CustomerDepositResource extends Resource
{
    protected static string|\UnitEnum|null $navigationGroup = '👥 Customer';

    protected static ?int $navigationSort = 6;

    protected static ?string $model = CustomerDeposit::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedWallet;

    protected static ?string $navigationLabel = 'Deposit Pelanggan';

    protected static ?string $recordTitleAttribute = 'reference';

    public static function form(Schema $schema): Schema
    {
        return CustomerDepositForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return CustomerDepositsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListCustomerDeposits::route('/'),
            'create' => CreateCustomerDeposit::route('/create'),
            'edit' => EditCustomerDeposit::route('/{record}/edit'),
        ];
    }
}
