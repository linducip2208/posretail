<?php

namespace App\Filament\Resources\Accounts\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class AccountForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(2)
            ->inlineLabel()
            ->components([
                TextInput::make('code')
                    ->label('Kode')
                    ->required()
                    ->maxLength(20)
                    ->unique(ignoreRecord: true),

                TextInput::make('name')
                    ->label('Nama')
                    ->required(),

                Select::make('type')
                    ->label('Tipe')
                    ->options([
                        'asset' => 'Aset',
                        'liability' => 'Kewajiban',
                        'equity' => 'Ekuitas',
                        'revenue' => 'Pendapatan',
                        'expense' => 'Beban',
                        'cogs' => 'HPP',
                    ])
                    ->required()
                    ->live()
                    ->afterStateUpdated(function ($state, $set) {
                        $normalMap = [
                            'asset' => 'debit',
                            'liability' => 'credit',
                            'equity' => 'credit',
                            'revenue' => 'credit',
                            'expense' => 'debit',
                            'cogs' => 'debit',
                        ];
                        $set('normal_balance', $normalMap[$state] ?? 'debit');
                    }),

                Select::make('parent_id')
                    ->label('Induk')
                    ->relationship('parent', 'name')
                    ->searchable()
                    ->preload()
                    ->nullable(),

                Select::make('normal_balance')
                    ->label('Saldo Normal')
                    ->options([
                        'debit' => 'Debit',
                        'credit' => 'Kredit',
                    ])
                    ->required(),

                Textarea::make('description')
                    ->label('Deskripsi')
                    ->columnSpanFull(),

                Toggle::make('active')
                    ->label('Aktif')
                    ->default(true),

                Toggle::make('is_locked')
                    ->label('Terkunci')
                    ->default(false),
            ]);
    }
}
