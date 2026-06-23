<?php

namespace App\Filament\Resources\Providers\Tables;

use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ProvidersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')->label('Nama')->searchable()->sortable(),
                TextColumn::make('type')->label('Tipe')->badge()->sortable(),
                TextColumn::make('api_format')->label('Format')->badge()->sortable(),
                TextColumn::make('base_url')->label('Base URL')->limit(30),
                IconColumn::make('is_active')->label('Aktif')->boolean(),
                IconColumn::make('is_default')->label('Default')->boolean(),
                TextColumn::make('created_at')->label('Dibuat')->dateTime('d M Y')->sortable(),
            ])
            ->actions([
                EditAction::make(),
            ])
            ->defaultSort('created_at', 'desc');
    }
}
