<?php

namespace App\Filament\Resources\SerialNumbers\Pages;

use App\Filament\Resources\SerialNumbers\SerialNumberResource;
use App\Models\Product;
use App\Models\SerialNumber;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;

class ListSerialNumbers extends ListRecords
{
    protected static string $resource = SerialNumberResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
            Action::make('importImei')
                ->label('Import Massal IMEI')
                ->icon('heroicon-o-clipboard-document-list')
                ->color('success')
                ->form([
                    Select::make('product_id')
                        ->label('Produk')
                        ->options(fn () => Product::query()
                            ->where('serial_tracking', '!=', 'none')
                            ->where('active', true)
                            ->pluck('name', 'id'))
                        ->searchable()
                        ->required(),
                    Select::make('outlet_id')
                        ->label('Outlet')
                        ->options(fn () => auth()->user()?->accessibleOutlets()->pluck('name', 'id'))
                        ->searchable()
                        ->nullable(),
                    Textarea::make('serials')
                        ->label('Daftar IMEI / Serial')
                        ->helperText('Satu IMEI per baris. Bisa paste dari Excel/notepad.')
                        ->rows(8)
                        ->required(),
                ])
                ->action(function (array $data) {
                    $serials = collect(preg_split('/\r\n|\r|\n/', $data['serials']))
                        ->map(fn ($s) => trim($s))
                        ->filter()
                        ->unique()
                        ->values();

                    if ($serials->isEmpty()) {
                        Notification::make()->title('Tidak ada IMEI valid')->warning()->send();

                        return;
                    }

                    $existing = SerialNumber::where('product_id', $data['product_id'])
                        ->whereIn('serial_number', $serials)
                        ->pluck('serial_number')
                        ->all();

                    $created = 0;
                    $skipped = 0;

                    foreach ($serials as $sn) {
                        if (in_array($sn, $existing, true)) {
                            $skipped++;

                            continue;
                        }

                        SerialNumber::create([
                            'product_id' => $data['product_id'],
                            'outlet_id' => $data['outlet_id'] ?? null,
                            'serial_number' => $sn,
                            'status' => 'in_stock',
                        ]);
                        $created++;
                    }

                    Notification::make()
                        ->title("{$created} IMEI ditambahkan, {$skipped} dilewati (duplikat)")
                        ->success()
                        ->send();
                }),
        ];
    }
}
