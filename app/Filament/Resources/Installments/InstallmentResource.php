<?php

namespace App\Filament\Resources\Installments;

use App\Filament\Resources\Installments\Pages\CreateInstallment;
use App\Filament\Resources\Installments\Pages\EditInstallment;
use App\Filament\Resources\Installments\Pages\ListInstallments;
use App\Filament\Resources\Installments\Schemas\InstallmentForm;
use App\Filament\Resources\Installments\Tables\InstallmentsTable;
use App\Models\Installment;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class InstallmentResource extends Resource
{
    protected static string|\UnitEnum|null $navigationGroup = '💳 Keuangan';

    protected static ?int $navigationSort = 3;

    protected static ?string $model = Installment::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCalendarDays;

    protected static ?string $recordTitleAttribute = 'installment_number';

    public static function form(Schema $schema): Schema
    {
        return InstallmentForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return InstallmentsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\InstallmentSchedulesRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListInstallments::route('/'),
            'create' => CreateInstallment::route('/create'),
            'edit' => EditInstallment::route('/{record}/edit'),
        ];
    }
}
