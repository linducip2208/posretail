<?php

namespace App\Filament\Resources\MarketplaceOrders\Tables;

use App\Models\MarketplaceOrder;
use App\Models\Order;
use App\Models\Outlet;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class MarketplaceOrdersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('platform')
                    ->label('Platform')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'tokopedia' => 'success',
                        'shopee' => 'warning',
                        'lazada' => 'primary',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'tokopedia' => 'Tokopedia',
                        'shopee' => 'Shopee',
                        'lazada' => 'Lazada',
                        default => $state,
                    }),

                TextColumn::make('platform_order_id')
                    ->label('ID Pesanan')
                    ->searchable(),

                TextColumn::make('customer_name')
                    ->label('Pelanggan')
                    ->searchable(),

                TextColumn::make('total_amount')
                    ->label('Total')
                    ->money('IDR')
                    ->sortable(),

                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'new' => 'gray',
                        'processed' => 'warning',
                        'completed' => 'success',
                        'cancelled' => 'danger',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'new' => 'Baru',
                        'processed' => 'Diproses',
                        'completed' => 'Selesai',
                        'cancelled' => 'Dibatalkan',
                        default => $state,
                    }),

                TextColumn::make('order.order_number')
                    ->label('Order Lokal')
                    ->toggleable(),

                TextColumn::make('created_at')
                    ->label('Diterima')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(),
            ])
            ->filters([
                SelectFilter::make('platform')
                    ->label('Platform')
                    ->options([
                        'tokopedia' => 'Tokopedia',
                        'shopee' => 'Shopee',
                        'lazada' => 'Lazada',
                    ]),

                SelectFilter::make('status')
                    ->label('Status')
                    ->options([
                        'new' => 'Baru',
                        'processed' => 'Diproses',
                        'completed' => 'Selesai',
                        'cancelled' => 'Dibatalkan',
                    ]),
            ])
            ->recordActions([
                Action::make('processOrder')
                    ->label('Proses ke Order')
                    ->icon('heroicon-o-arrow-right-circle')
                    ->color('success')
                    ->visible(fn (MarketplaceOrder $record): bool => $record->status === 'new')
                    ->action(function (MarketplaceOrder $record) {
                        $order = Order::create([
                            'order_number' => 'MP-' . $record->platform . '-' . $record->platform_order_id,
                            'customer_id' => null,
                            'outlet_id' => Outlet::first()?->id ?? 1,
                            'user_id' => auth()->id(),
                            'subtotal' => $record->total_amount - $record->shipping_fee,
                            'discount_amount' => 0,
                            'tax_amount' => 0,
                            'total_amount' => $record->total_amount,
                            'commission_amount' => 0,
                            'payment_status' => 'unpaid',
                            'order_status' => 'pending',
                            'order_type' => 'marketplace',
                            'notes' => "Pesanan marketplace dari {$record->platform}: {$record->platform_invoice}\nPelanggan: {$record->customer_name}\nTelepon: {$record->customer_phone}\nAlamat: {$record->shipping_address}",
                        ]);

                        $record->update([
                            'order_id' => $order->id,
                            'status' => 'processed',
                        ]);

                        Notification::make()
                            ->title('Order berhasil dibuat')
                            ->body("Order #{$order->order_number} berhasil dibuat dari pesanan marketplace.")
                            ->success()
                            ->send();
                    }),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
