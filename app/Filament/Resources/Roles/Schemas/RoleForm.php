<?php

namespace App\Filament\Resources\Roles\Schemas;

use App\Models\Permission;
use Filament\Forms\Components\CheckboxList;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class RoleForm
{
    public static function configure(Schema $schema): Schema
    {
        $permissionGroups = [];
        foreach (Permission::groups() as $slug => $label) {
            $perms = Permission::where('group', $slug)->get();
            if ($perms->isEmpty()) {
                continue;
            }
            $permissionGroups[] = Section::make($label)
                ->schema([
                    CheckboxList::make("permissions")
                        ->label('')
                        ->options($perms->pluck('name', 'id'))
                        ->columns(2)
                        ->bulkToggleable()
                        ->relationship('permissions', 'name')
                        ->hiddenLabel(),
                ])
                ->collapsible()
                ->collapsed(fn (string $operation): bool => $operation === 'edit');
        }

        return $schema
            ->components([
                Section::make('Informasi Role')
                    ->schema([
                        TextInput::make('name')
                            ->required()
                            ->maxLength(255)
                            ->live(onBlur: true)
                            ->afterStateUpdated(fn ($state, callable $set) => $set('slug', \Illuminate\Support\Str::slug($state))),

                        TextInput::make('slug')
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(255),

                        TextInput::make('description')
                            ->maxLength(500),
                    ])
                    ->columns(2),

                Section::make('Permission')
                    ->description('Pilih permission yang dimiliki role ini')
                    ->schema($permissionGroups),
            ]);
    }
}
