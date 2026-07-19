<?php

namespace App\Filament\Resources\BankStatements;

use App\Filament\Resources\BankStatements\Pages\ListBankStatements;
use App\Filament\Resources\BankStatements\Tables\BankStatementsTable;
use App\Models\BankStatement;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class BankStatementResource extends Resource
{
    protected static string|\UnitEnum|null $navigationGroup = '💳 Keuangan';

    protected static ?int $navigationSort = 5;

    protected static ?string $model = BankStatement::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBanknotes;

    protected static ?string $navigationLabel = 'Rekonsiliasi Bank';

    protected static ?string $label = 'Rekonsiliasi Bank';

    protected static ?string $pluralLabel = 'Rekonsiliasi Bank';

    protected static ?string $recordTitleAttribute = 'bank_name';

    public static function table(Table $table): Table
    {
        return BankStatementsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        $outletIds = auth()->user()?->getAccessibleOutletIds() ?? [];
        if (empty($outletIds)) {
            return parent::getEloquentQuery();
        }
        return parent::getEloquentQuery()->where(function ($q) use ($outletIds) {
            $q->whereIn('outlet_id', $outletIds)
              ->orWhereNull('outlet_id');
        });
    }

    public static function getPages(): array
    {
        return [
            'index' => ListBankStatements::route('/'),
        ];
    }
}
