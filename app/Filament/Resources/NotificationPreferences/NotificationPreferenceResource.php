<?php

namespace App\Filament\Resources\NotificationPreferences;

use App\Filament\Resources\NotificationPreferences\Pages\ListNotificationPreferences;
use App\Models\NotificationPreference;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Support\Icons\Heroicon;

class NotificationPreferenceResource extends Resource
{
    protected static string|\UnitEnum|null $navigationGroup = '🔔 Notifikasi';
    protected static ?int $navigationSort = 2;
    protected static ?string $model = NotificationPreference::class;
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBellSlash;
    protected static ?string $navigationLabel = 'Preferensi Notifikasi';
    protected static ?string $label = 'Preferensi Notifikasi';

    public static function table(Table $table): Table
    {
        $types = NotificationPreference::types();
        return $table
            ->query(NotificationPreference::query()->with('user'))
            ->columns([
                TextColumn::make('user.name')->label('User')->searchable(),
                TextColumn::make('notification_type')
                    ->label('Tipe')
                    ->formatStateUsing(fn ($state) => $types[$state] ?? $state)
                    ->badge(),
                ToggleColumn::make('enabled')
                    ->label('Aktif')
                    ->afterStateUpdated(function ($record, $state) {
                        $record->update(['enabled' => $state]);
                    }),
                TextColumn::make('updated_at')->label('Diperbarui')->dateTime('d M Y H:i'),
            ])
            ->defaultSort('user_id')
            ->paginated([25, 50, 100]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListNotificationPreferences::route('/'),
        ];
    }
}
