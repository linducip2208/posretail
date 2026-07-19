<?php

namespace App\Filament\Resources\Assets\Tables;

use App\Filament\Resources\Assets\CompanyAssetResource;
use App\Models\CompanyAsset;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class AssetsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('asset_code')
                    ->label('Kode Aset')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('name')
                    ->label('Nama Aset')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('outlet.name')
                    ->label('Outlet')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('category')
                    ->label('Kategori')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'equipment' => 'info',
                        'furniture' => 'warning',
                        'vehicle' => 'success',
                        'building' => 'primary',
                        'it' => 'gray',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'equipment' => 'Peralatan',
                        'furniture' => 'Furniture',
                        'vehicle' => 'Kendaraan',
                        'building' => 'Bangunan',
                        'it' => 'IT',
                        default => $state,
                    }),

                TextColumn::make('purchase_value')
                    ->label('Nilai Pembelian')
                    ->money('IDR')
                    ->sortable(),

                TextColumn::make('current_value')
                    ->label('Nilai Saat Ini')
                    ->money('IDR')
                    ->sortable(),

                TextColumn::make('monthly_depreciation')
                    ->label('Penyusutan/Bulan')
                    ->money('IDR')
                    ->sortable(),

                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'active' => 'success',
                        'disposed' => 'gray',
                        'maintenance' => 'warning',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'active' => 'Aktif',
                        'disposed' => 'Dihapus',
                        'maintenance' => 'Perbaikan',
                        default => $state,
                    }),

                TextColumn::make('assignedUser.name')
                    ->label('Ditugaskan Ke')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('location')
                    ->label('Lokasi')
                    ->searchable()
                    ->toggleable(),

                TextColumn::make('created_at')
                    ->label('Dibuat')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('category')
                    ->label('Kategori')
                    ->options([
                        'equipment' => 'Peralatan',
                        'furniture' => 'Furniture',
                        'vehicle' => 'Kendaraan',
                        'building' => 'Bangunan',
                        'it' => 'IT',
                    ]),

                SelectFilter::make('status')
                    ->label('Status')
                    ->options([
                        'active' => 'Aktif',
                        'disposed' => 'Dihapus',
                        'maintenance' => 'Perbaikan',
                    ]),

                SelectFilter::make('outlet')
                    ->relationship('outlet', 'name')
                    ->label('Outlet'),
            ])
            ->recordActions([
                Action::make('dispose')
                    ->label('Hapus Aset')
                    ->icon('heroicon-o-trash')
                    ->color('danger')
                    ->visible(fn (CompanyAsset $record): bool => $record->status === 'active')
                    ->requiresConfirmation()
                    ->modalHeading('Hapus Aset?')
                    ->modalDescription('Aset akan ditandai sebagai dihapus.')
                    ->action(function (CompanyAsset $record) {
                        $record->update(['status' => 'disposed']);
                        Notification::make()
                            ->title('Aset berhasil dihapus')
                            ->success()
                            ->send();
                    }),

                Action::make('recordMaintenance')
                    ->label('Catat Perbaikan')
                    ->icon('heroicon-o-wrench-screwdriver')
                    ->color('warning')
                    ->visible(fn (CompanyAsset $record): bool => in_array($record->status, ['active', 'maintenance']))
                    ->url(fn (CompanyAsset $record): string => CompanyAssetResource::getUrl('edit', ['record' => $record->id])),

                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
