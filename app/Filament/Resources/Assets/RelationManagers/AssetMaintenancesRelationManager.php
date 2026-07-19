<?php

namespace App\Filament\Resources\Assets\RelationManagers;

use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class AssetMaintenancesRelationManager extends RelationManager
{
    protected static string $relationship = 'maintenances';

    protected static ?string $title = 'Riwayat Perbaikan';

    protected static ?string $label = 'Perbaikan';

    protected static ?string $pluralLabel = 'Perbaikan';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                DatePicker::make('maintenance_date')
                    ->label('Tanggal Perbaikan')
                    ->required(),

                Select::make('type')
                    ->label('Tipe')
                    ->options([
                        'repair' => 'Perbaikan',
                        'inspection' => 'Inspeksi',
                        'upgrade' => 'Upgrade',
                    ])
                    ->required()
                    ->default('repair'),

                TextInput::make('cost')
                    ->label('Biaya')
                    ->numeric()
                    ->required()
                    ->default(0)
                    ->prefix('Rp'),

                Textarea::make('description')
                    ->label('Deskripsi')
                    ->columnSpanFull(),

                DatePicker::make('next_maintenance_date')
                    ->label('Tanggal Perbaikan Berikutnya'),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('maintenance_date')
                    ->label('Tanggal')
                    ->date()
                    ->sortable(),

                TextColumn::make('type')
                    ->label('Tipe')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'repair' => 'warning',
                        'inspection' => 'info',
                        'upgrade' => 'success',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'repair' => 'Perbaikan',
                        'inspection' => 'Inspeksi',
                        'upgrade' => 'Upgrade',
                        default => $state,
                    }),

                TextColumn::make('cost')
                    ->label('Biaya')
                    ->money('IDR'),

                TextColumn::make('description')
                    ->label('Deskripsi')
                    ->limit(50),

                TextColumn::make('next_maintenance_date')
                    ->label('Perbaikan Berikutnya')
                    ->date(),
            ])
            ->headerActions([
                CreateAction::make()
                    ->label('Tambah Perbaikan'),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ]);
    }
}
