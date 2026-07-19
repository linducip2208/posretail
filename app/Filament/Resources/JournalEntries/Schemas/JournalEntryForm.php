<?php

namespace App\Filament\Resources\JournalEntries\Schemas;

use App\Models\Account;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class JournalEntryForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(2)
            ->inlineLabel()
            ->components([
                Placeholder::make('journal_number')
                    ->label('Nomor Jurnal')
                    ->content(fn (?string $state): string => $state ?? '(Auto-generate)'),

                DatePicker::make('journal_date')
                    ->label('Tanggal Jurnal')
                    ->default(now())
                    ->required(),

                Textarea::make('description')
                    ->label('Deskripsi')
                    ->required()
                    ->columnSpanFull(),

                Repeater::make('items')
                    ->label('Item Jurnal')
                    ->relationship('items')
                    ->schema([
                        Select::make('account_id')
                            ->label('Akun')
                            ->relationship('account', 'name')
                            ->searchable()
                            ->preload()
                            ->required()
                            ->live(),

                        Textarea::make('description')
                            ->label('Deskripsi Item'),

                        TextInput::make('debit')
                            ->label('Debit')
                            ->numeric()
                            ->default(0)
                            ->prefix('Rp')
                            ->required()
                            ->live(),

                        TextInput::make('credit')
                            ->label('Kredit')
                            ->numeric()
                            ->default(0)
                            ->prefix('Rp')
                            ->required()
                            ->live(),
                    ])
                    ->columns(2)
                    ->addActionLabel('Tambah Item')
                    ->columnSpanFull()
                    ->mutateRelationshipDataBeforeCreateUsing(function (array $data): array {
                        if (empty($data['description'])) {
                            $account = Account::find($data['account_id']);
                            $data['description'] = $account?->name ?? '';
                        }
                        return $data;
                    }),
            ]);
    }
}
