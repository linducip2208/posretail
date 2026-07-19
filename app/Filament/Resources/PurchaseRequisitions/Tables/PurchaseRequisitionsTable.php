<?php

namespace App\Filament\Resources\PurchaseRequisitions\Tables;

use App\Models\PurchaseRequisition;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class PurchaseRequisitionsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('pr_number')
                    ->searchable()
                    ->label('No. PR'),
                TextColumn::make('date_needed')
                    ->date()
                    ->sortable()
                    ->label('Tgl Dibutuhkan'),
                TextColumn::make('outlet.name')
                    ->searchable()
                    ->label('Outlet'),
                TextColumn::make('requester.name')
                    ->searchable()
                    ->label('Pemohon'),
                TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'draft' => 'gray',
                        'submitted' => 'warning',
                        'approved' => 'success',
                        'rejected' => 'danger',
                        'ordered' => 'info',
                        default => 'gray',
                    })
                    ->sortable()
                    ->label('Status'),
                TextColumn::make('approver.name')
                    ->searchable()
                    ->label('Disetujui Oleh'),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->label('Dibuat Pada'),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->label('Diperbarui Pada'),
            ])
            ->filters([
                SelectFilter::make('outlet_id')
                    ->relationship('outlet', 'name')
                    ->label('Outlet'),
                SelectFilter::make('status')
                    ->options([
                        'draft' => 'Draft',
                        'submitted' => 'Diajukan',
                        'approved' => 'Disetujui',
                        'rejected' => 'Ditolak',
                        'ordered' => 'Diorder',
                    ])
                    ->label('Status'),
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
