<?php

namespace App\Filament\Resources\Rosters;

use App\Filament\Resources\Rosters\Pages\CreateRoster;
use App\Filament\Resources\Rosters\Pages\EditRoster;
use App\Filament\Resources\Rosters\Pages\ListRosters;
use App\Filament\Resources\Rosters\Schemas\RosterForm;
use App\Filament\Resources\Rosters\Tables\RostersTable;
use App\Models\Roster;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class RosterResource extends Resource
{
    protected static string|\UnitEnum|null $navigationGroup = '👨‍💼 Pegawai';

    protected static ?int $navigationSort = 3;

    protected static ?string $model = Roster::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCalendarDays;

    protected static ?string $navigationLabel = 'Jadwal Shift';

    protected static ?string $label = 'Jadwal Shift';

    protected static ?string $pluralLabel = 'Jadwal Shift';

    protected static ?string $recordTitleAttribute = 'user.name';

    public static function form(Schema $schema): Schema
    {
        return RosterForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return RostersTable::configure($table);
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
        return parent::getEloquentQuery()->whereIn('outlet_id', $outletIds);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListRosters::route('/'),
            'create' => CreateRoster::route('/create'),
            'edit' => EditRoster::route('/{record}/edit'),
        ];
    }
}
