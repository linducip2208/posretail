<?php

namespace App\Filament\Resources\SupplierContracts\Tables;

use App\Models\SupplierContract;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class SupplierContractsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('contract_number')
                    ->label('No. Kontrak')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('supplier.name')
                    ->label('Supplier')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('start_date')
                    ->label('Tanggal Mulai')
                    ->date()
                    ->sortable(),

                TextColumn::make('end_date')
                    ->label('Tanggal Berakhir')
                    ->date()
                    ->sortable(),

                TextColumn::make('value')
                    ->label('Nilai Kontrak')
                    ->money('IDR')
                    ->sortable(),

                TextColumn::make('payment_terms')
                    ->label('Ketentuan Bayar')
                    ->searchable(),

                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'active' => 'success',
                        'expired' => 'warning',
                        'terminated' => 'danger',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'active' => 'Aktif',
                        'expired' => 'Kadaluarsa',
                        'terminated' => 'Dihentikan',
                        default => $state,
                    }),

                TextColumn::make('created_at')
                    ->label('Dibuat')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label('Status')
                    ->options([
                        'active' => 'Aktif',
                        'expired' => 'Kadaluarsa',
                        'terminated' => 'Dihentikan',
                    ]),

                SelectFilter::make('supplier')
                    ->relationship('supplier', 'name')
                    ->label('Supplier'),
            ])
            ->recordActions([
                Action::make('terminate')
                    ->label('Hentikan Kontrak')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->visible(fn (SupplierContract $record): bool => $record->status === 'active')
                    ->requiresConfirmation()
                    ->modalHeading('Hentikan Kontrak?')
                    ->modalDescription('Kontrak akan ditandai sebagai dihentikan.')
                    ->action(function (SupplierContract $record) {
                        $record->update(['status' => 'terminated']);
                        Notification::make()
                            ->title('Kontrak dihentikan')
                            ->success()
                            ->send();
                    }),

                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
