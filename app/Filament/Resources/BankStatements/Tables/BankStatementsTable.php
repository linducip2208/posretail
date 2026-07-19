<?php

namespace App\Filament\Resources\BankStatements\Tables;

use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class BankStatementsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('transaction_date')
                    ->label('Tanggal')
                    ->date()
                    ->sortable(),
                TextColumn::make('bank_name')
                    ->label('Bank')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('description')
                    ->label('Deskripsi')
                    ->searchable()
                    ->limit(40),
                TextColumn::make('debit')
                    ->label('Debit')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('credit')
                    ->label('Kredit')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('balance')
                    ->label('Saldo')
                    ->numeric()
                    ->sortable(),
                IconColumn::make('is_matched')
                    ->label('Tercocokkan')
                    ->boolean()
                    ->sortable(),
                TextColumn::make('reference')
                    ->label('Referensi')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('created_at')
                    ->label('Dibuat Pada')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('bank_name')
                    ->options(fn () => \App\Models\BankStatement::distinct()->pluck('bank_name', 'bank_name'))
                    ->label('Bank'),
                SelectFilter::make('outlet_id')
                    ->relationship('outlet', 'name')
                    ->label('Outlet'),
                SelectFilter::make('is_matched')
                    ->options([
                        '1' => 'Tercocokkan',
                        '0' => 'Belum Tercocokkan',
                    ])
                    ->label('Status Cocok'),
            ])
            ->defaultSort('transaction_date', 'desc');
    }
}
