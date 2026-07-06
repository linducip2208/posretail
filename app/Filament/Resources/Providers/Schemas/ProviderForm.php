<?php

namespace App\Filament\Resources\Providers\Schemas;

use Filament\Schemas\Components\Fieldset;
use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class ProviderForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->schema([
            TextInput::make('name')
                ->label('Nama Provider')
                ->required()
                ->maxLength(255),
            Select::make('type')
                ->label('Tipe')
                ->options([
                    'payment' => 'Payment Gateway',
                    'sms' => 'SMS Gateway',
                    'email' => 'Email Provider',
                    'storage' => 'Storage Provider',
                ])
                ->default('payment')
                ->required(),
            Select::make('api_format')
                ->label('Format API')
                ->options([
                    'rest-redirect' => 'REST Redirect (Midtrans-style)',
                    'rest-api' => 'REST API (Xendit-style)',
                    'qr-static' => 'QR Static',
                ])
                ->default('rest-redirect')
                ->required(),
            TextInput::make('base_url')
                ->label('Base URL')
                ->url()
                ->required(),
            TextInput::make('api_key_encrypted')
                ->label('API Key / Server Key')
                ->password()
                ->revealable(),
            TextInput::make('api_secret_encrypted')
                ->label('API Secret / Client Key')
                ->password()
                ->revealable(),
            TextInput::make('merchant_id')
                ->label('Merchant ID / Client ID'),
            TextInput::make('client_id')
                ->label('Client ID / App ID'),
            KeyValue::make('extra_headers')
                ->label('Extra Headers')
                ->keyLabel('Header Name')
                ->valueLabel('Value'),
            KeyValue::make('extra_config')
                ->label('Extra Config')
                ->keyLabel('Key')
                ->valueLabel('Value'),
            Toggle::make('is_active')
                ->label('Aktif')
                ->default(true),
            Toggle::make('is_default')
                ->label('Default')
                ->default(false),
        ]);
    }
}
