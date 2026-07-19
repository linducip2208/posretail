<?php

namespace App\Filament\Resources\Assets\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class AssetForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->label('Nama Aset')
                    ->required()
                    ->maxLength(255),

                Select::make('outlet_id')
                    ->label('Outlet')
                    ->relationship('outlet', 'name')
                    ->searchable()
                    ->required(),

                Select::make('category')
                    ->label('Kategori')
                    ->options([
                        'equipment' => 'Peralatan',
                        'furniture' => 'Furniture',
                        'vehicle' => 'Kendaraan',
                        'building' => 'Bangunan',
                        'it' => 'IT',
                    ])
                    ->required()
                    ->default('equipment'),

                DatePicker::make('purchase_date')
                    ->label('Tanggal Pembelian')
                    ->required(),

                TextInput::make('purchase_value')
                    ->label('Nilai Pembelian')
                    ->numeric()
                    ->required()
                    ->prefix('Rp')
                    ->minValue(0),

                TextInput::make('salvage_value')
                    ->label('Nilai Residu')
                    ->numeric()
                    ->required()
                    ->default(0)
                    ->prefix('Rp')
                    ->minValue(0),

                TextInput::make('useful_life_months')
                    ->label('Masa Manfaat (Bulan)')
                    ->numeric()
                    ->required()
                    ->default(48)
                    ->minValue(1),

                TextInput::make('current_value')
                    ->label('Nilai Saat Ini')
                    ->numeric()
                    ->default(0)
                    ->prefix('Rp')
                    ->disabled()
                    ->hint('Dihitung otomatis'),

                TextInput::make('monthly_depreciation')
                    ->label('Penyusutan Bulanan')
                    ->numeric()
                    ->disabled()
                    ->hint('Dihitung otomatis: (Nilai Pembelian - Nilai Residu) / Masa Manfaat'),

                TextInput::make('location')
                    ->label('Lokasi')
                    ->maxLength(255),

                Select::make('assigned_to')
                    ->label('Ditugaskan Kepada')
                    ->relationship('assignedUser', 'name')
                    ->searchable(),

                Select::make('status')
                    ->label('Status')
                    ->options([
                        'active' => 'Aktif',
                        'disposed' => 'Dihapus',
                        'maintenance' => 'Perbaikan',
                    ])
                    ->required()
                    ->default('active'),

                Textarea::make('notes')
                    ->label('Catatan')
                    ->columnSpanFull(),
            ]);
    }
}
