<?php

namespace App\Filament\Resources\KitchenTickets;

use App\Filament\Resources\KitchenTickets\Pages\EditKitchenTicket;
use App\Filament\Resources\KitchenTickets\Pages\ListKitchenTickets;
use App\Filament\Resources\KitchenTickets\Schemas\KitchenTicketForm;
use App\Filament\Resources\KitchenTickets\Tables\KitchenTicketsTable;
use App\Models\KitchenTicket;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Filament\Support\Icons\Heroicon;

class KitchenTicketResource extends Resource
{
    protected static bool $shouldRegisterNavigation = false;
    protected static string|\UnitEnum|null $navigationGroup = '🔄 Operasional';

    protected static ?int $navigationSort = 1;

    protected static ?string $model = KitchenTicket::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedFire;

    protected static ?string $navigationLabel = 'Tiket Dapur';

    protected static ?string $label = 'Tiket Dapur';

    protected static ?string $recordTitleAttribute = 'ticket_number';

    public static function form(Schema $schema): Schema
    {
        return KitchenTicketForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return KitchenTicketsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListKitchenTickets::route('/'),
            'edit' => EditKitchenTicket::route('/{record}/edit'),
        ];
    }
}
