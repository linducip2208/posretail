<?php

namespace App\Filament\Resources\Products\Schemas;

use App\Helpers\BarcodeHelper;
use App\Models\SystemSetting;
use App\Support\SafeNumber;
use Filament\Actions\Action;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class ProductForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->required()
                    ->live(onBlur: true)
                    ->afterStateUpdated(fn ($state, callable $set) => $set('slug', Str::slug((string) $state))),
                TextInput::make('slug')
                    ->helperText('Dikosongkan akan dibuat otomatis dari nama produk.'),
                Textarea::make('description')
                    ->columnSpanFull(),
                Select::make('category_id')
                    ->relationship('category', 'name'),
                Select::make('brand_id')
                    ->relationship('brand', 'name'),
                Select::make('unit_id')
                    ->relationship('unit', 'name'),
                Select::make('outlet_id')
                    ->options(fn () => auth()->user()?->accessibleOutlets()->pluck('name', 'id')),
                TextInput::make('sku')
                    ->label('SKU')
                    ->required(),
                TextInput::make('barcode')
                    ->unique('products', 'barcode', ignoreRecord: true)
                    ->suffixAction(
                        Action::make('generateBarcode')
                            ->icon('tabler-refresh')
                            ->tooltip('Generate barcode otomatis')
                            ->action(function ($set) {
                                $set('barcode', BarcodeHelper::generate());
                            })
                    ),
                TextInput::make('cost_price')
                    ->required()
                    ->numeric()
                    ->minValue(0)
                    ->maxValue(SafeNumber::MONEY_MAX)
                    ->validationAttribute('Harga beli')
                    ->default(0)
                    ->prefix('Rp'),
                TextInput::make('selling_price')
                    ->required()
                    ->numeric()
                    ->minValue(0)
                    ->maxValue(SafeNumber::MONEY_MAX)
                    ->validationAttribute('Harga jual')
                    ->default(0)
                    ->prefix('Rp'),
                TextInput::make('wholesale_price')
                    ->numeric()
                    ->minValue(0)
                    ->maxValue(SafeNumber::MONEY_MAX)
                    ->validationAttribute('Harga grosir')
                    ->prefix('Rp'),
                TextInput::make('member_price')
                    ->numeric()
                    ->minValue(0)
                    ->maxValue(SafeNumber::MONEY_MAX)
                    ->validationAttribute('Harga member')
                    ->prefix('Rp'),
                TextInput::make('min_stock')
                    ->required()
                    ->numeric()
                    ->integer()
                    ->minValue(0)
                    ->maxValue(SafeNumber::STOCK_MAX)
                    ->validationAttribute('Stok minimum')
                    ->default(0),
                TextInput::make('max_stock')
                    ->required()
                    ->numeric()
                    ->integer()
                    ->minValue(0)
                    ->maxValue(SafeNumber::STOCK_MAX)
                    ->validationAttribute('Stok maksimum')
                    ->default(0),
                TextInput::make('current_stock')
                    ->required()
                    ->numeric()
                    ->integer()
                    ->minValue(0)
                    ->maxValue(SafeNumber::STOCK_MAX)
                    ->validationAttribute('Stok saat ini')
                    ->default(0),
                FileUpload::make('image')
                    ->image(),
                Toggle::make('has_variants')
                    ->required(),
                Select::make('serial_tracking')
                    ->options([
                        'none' => 'Tanpa Serial / IMEI',
                        'optional' => 'Opsional (boleh input IMEI)',
                        'required' => 'Wajib IMEI (HP / elektronik)',
                    ])
                    ->default(fn () => SystemSetting::getSerialTrackingDefault())
                    ->required()
                    ->label('Pelacakan Serial / IMEI'),
                TextInput::make('warranty_months')
                    ->numeric()
                    ->integer()
                    ->minValue(0)
                    ->maxValue(SafeNumber::WARRANTY_MAX)
                    ->validationAttribute('Masa garansi')
                    ->default(0)
                    ->suffix('bulan')
                    ->helperText('Masa garansi dalam bulan. 0 = tanpa garansi.')
                    ->label('Masa Garansi'),
                Toggle::make('active')
                    ->default(true)
                    ->required(),
            ]);
    }
}
