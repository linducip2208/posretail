<?php

namespace App\Filament\Resources\Users\Schemas;

use App\Models\Outlet;
use App\Models\Role;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Validation\Rules\Password;

class UserForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->required()
                    ->maxLength(255),
                TextInput::make('email')
                    ->label('Email address')
                    ->required()
                    ->email()
                    ->unique(ignoreRecord: true)
                    ->maxLength(255),
                TextInput::make('password')
                    ->label('Password')
                    ->password()
                    ->required(fn (string $operation): bool => $operation === 'create')
                    ->dehydrated(fn (?string $state): bool => filled($state))
                    ->rule(Password::defaults())
                    ->confirmed()
                    ->maxLength(255),
                TextInput::make('password_confirmation')
                    ->label('Konfirmasi Password')
                    ->password()
                    ->required(fn (string $operation): bool => $operation === 'create')
                    ->maxLength(255),
                Select::make('role')
                    ->required()
                    ->default('kasir')
                    ->options([
                        'owner' => 'Owner',
                        'manager' => 'Manager',
                        'admin' => 'Admin',
                        'kasir' => 'Kasir',
                        'gudang' => 'Gudang',
                    ]),
                Select::make('roles')
                    ->label('Role & Permission')
                    ->helperText('Role menentukan hak akses user ke menu dan fitur')
                    ->multiple()
                    ->relationship('roles', 'name')
                    ->preload()
                    ->options(fn () => Role::pluck('name', 'id'))
                    ->createOptionForm([
                        TextInput::make('name')->required(),
                        TextInput::make('slug')->required(),
                        TextInput::make('description'),
                    ])
                    ->createOptionAction(fn ($action) => $action->label('Buat Role Baru')),
                Section::make('Akses Outlet')
                    ->description('Pilih outlet yang dapat diakses user ini')
                    ->schema([
                        Select::make('outlets')
                            ->label('Outlet')
                            ->multiple()
                            ->relationship('outlets', 'name')
                            ->preload()
                            ->options(fn () => Outlet::where('active', true)->pluck('name', 'id'))
                            ->helperText('Kosongkan jika user tidak boleh akses outlet manapun'),
                    ]),
            ]);
    }
}
