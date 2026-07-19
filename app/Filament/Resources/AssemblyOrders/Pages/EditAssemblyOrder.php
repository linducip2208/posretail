<?php

namespace App\Filament\Resources\AssemblyOrders\Pages;

use App\Filament\Resources\AssemblyOrders\AssemblyOrderResource;
use App\Models\StockMovement;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\DB;

class EditAssemblyOrder extends EditRecord
{
    protected static string $resource = AssemblyOrderResource::class;

    protected function getHeaderActions(): array
    {
        return [
            $this->getStartProductionAction(),
            $this->getCompleteAction(),
            $this->getCancelAction(),
            DeleteAction::make(),
        ];
    }

    protected function getStartProductionAction(): Action
    {
        return Action::make('startProduction')
            ->label('Mulai Produksi')
            ->icon('heroicon-o-play')
            ->color('warning')
            ->visible(fn () => $this->record->status === 'draft')
            ->action(function () {
                $this->record->update(['status' => 'in_progress']);
                Notification::make()
                    ->title('Produksi dimulai')
                    ->success()
                    ->send();
                $this->redirect($this->getResource()::getUrl('edit', ['record' => $this->record]));
            });
    }

    protected function getCompleteAction(): Action
    {
        return Action::make('complete')
            ->label('Selesaikan')
            ->icon('heroicon-o-check-circle')
            ->color('success')
            ->visible(fn () => in_array($this->record->status, ['draft', 'in_progress']))
            ->requiresConfirmation()
            ->modalHeading('Selesaikan Produksi?')
            ->modalDescription('Produk akan ditambahkan ke stok dan bahan baku akan dikurangi.')
            ->action(function () {
                DB::transaction(function () {
                    $assemblyOrder = $this->record;

                    $assemblyOrder->update([
                        'status' => 'completed',
                        'completed_at' => now(),
                    ]);

                    $product = $assemblyOrder->product;
                    $product->increment('current_stock', $assemblyOrder->quantity);

                    StockMovement::create([
                        'product_id' => $product->id,
                        'outlet_id' => $assemblyOrder->outlet_id,
                        'type' => 'in',
                        'quantity' => $assemblyOrder->quantity,
                        'reference_type' => AssemblyOrder::class,
                        'reference_id' => $assemblyOrder->id,
                        'notes' => 'Produksi: ' . $assemblyOrder->assembly_number,
                    ]);

                    foreach ($assemblyOrder->items as $item) {
                        $rawMaterial = $item->rawMaterial;
                        $rawMaterial->decrement('current_stock', $item->quantity);
                    }
                });

                Notification::make()
                    ->title('Produksi selesai')
                    ->success()
                    ->send();

                $this->redirect($this->getResource()::getUrl('edit', ['record' => $this->record]));
            });
    }

    protected function getCancelAction(): Action
    {
        return Action::make('cancel')
            ->label('Batalkan')
            ->icon('heroicon-o-x-circle')
            ->color('danger')
            ->visible(fn () => in_array($this->record->status, ['draft', 'in_progress']))
            ->requiresConfirmation()
            ->modalHeading('Batalkan Produksi?')
            ->modalDescription('Produksi akan dibatalkan dan tidak dapat dikembalikan.')
            ->action(function () {
                $this->record->update(['status' => 'cancelled']);

                Notification::make()
                    ->title('Produksi dibatalkan')
                    ->warning()
                    ->send();

                $this->redirect($this->getResource()::getUrl('edit', ['record' => $this->record]));
            });
    }
}
