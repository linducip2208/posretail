<?php

namespace App\Filament\Resources\Roles\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class RolesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('slug')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('description')
                    ->limit(50)
                    ->searchable(),

                TextColumn::make('permissions_count')
                    ->label('Total Permission')
                    ->counts('permissions')
                    ->alignCenter()
                    ->sortable(),

                TextColumn::make('users_count')
                    ->label('Total User')
                    ->counts('users')
                    ->alignCenter()
                    ->sortable(),

                TextColumn::make('is_system')
                    ->label('System')
                    ->badge()
                    ->color(fn (bool $state): string => $state ? 'warning' : 'gray')
                    ->formatStateUsing(fn (bool $state): string => $state ? 'Default' : 'Kustom')
                    ->alignCenter(),
            ])
            ->filters([])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->hidden(fn () => !auth()->user()?->hasPermission('delete-sistem')),
                ]),
            ]);
    }
}
