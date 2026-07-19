<?php

namespace App\Filament\Resources\CashDrawer;

use App\Filament\Resources\CashDrawer\Pages\ListCashDrawerTransactions;
use App\Models\CashDrawerTransaction;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Illuminate\Database\Eloquent\Builder;

class CashDrawerTransactionResource extends Resource
{
    protected static string|\UnitEnum|null $navigationGroup = '💰 Penjualan';

    protected static ?int $navigationSort = 4;

    protected static ?string $model = CashDrawerTransaction::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBanknotes;

    protected static ?string $navigationLabel = 'Riwayat Transaksi';

    protected static ?string $label = 'Riwayat Kas';

    protected static ?string $recordTitleAttribute = 'created_at';

    public static function form(Schema $schema): Schema
    {
        return $schema;
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('shift.outlet.name')->label('Outlet')->sortable()->searchable(),
                TextColumn::make('shift.user.name')->label('Kasir')->sortable()->searchable(),
                TextColumn::make('type')->label('Tipe')->badge()
                    ->color(fn ($state) => match ($state) {
                        'sale' => 'success',
                        'cash_in' => 'info',
                        'cash_out' => 'danger',
                        'refund' => 'warning',
                        default => 'gray',
                    }),
                TextColumn::make('amount')->label('Jumlah')->money('IDR')->sortable(),
                TextColumn::make('payment_method')->label('Metode'),
                TextColumn::make('notes')->label('Keterangan')->limit(40),
                TextColumn::make('created_at')->label('Waktu')->dateTime('d M Y H:i')->sortable(),
            ])
            ->filters([
                SelectFilter::make('type')->options([
                    'sale' => 'Penjualan',
                    'cash_in' => 'Kas Masuk',
                    'cash_out' => 'Kas Keluar',
                    'refund' => 'Refund',
                ]),
            ])
            ->defaultSort('created_at', 'desc')
            ->defaultPaginationPageOption(50);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListCashDrawerTransactions::route('/'),
        ];
    }
}
