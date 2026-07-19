<?php

namespace App\Filament\Resources\PurchaseRequisitions\Pages;

use App\Filament\Resources\PurchaseOrders\PurchaseOrderResource;
use App\Filament\Resources\PurchaseRequisitions\PurchaseRequisitionResource;
use App\Models\PurchaseOrder;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;

class EditPurchaseRequisition extends EditRecord
{
    protected static string $resource = PurchaseRequisitionResource::class;

    protected function getHeaderActions(): array
    {
        $record = $this->getRecord();

        $actions = [];

        if ($record->status === 'draft') {
            $actions[] = Action::make('submit')
                ->label('Ajukan')
                ->icon('heroicon-o-paper-airplane')
                ->color('warning')
                ->action(function () use ($record) {
                    $record->update([
                        'status' => 'submitted',
                        'submitted_at' => now(),
                    ]);
                    Notification::make()
                        ->title('Permintaan pembelian berhasil diajukan')
                        ->success()
                        ->send();
                    $this->redirect($this->getResource()::getUrl('index'));
                });
        }

        if ($record->status === 'submitted') {
            $actions[] = Action::make('approve')
                ->label('Setujui')
                ->icon('heroicon-o-check-circle')
                ->color('success')
                ->action(function () use ($record) {
                    $record->update([
                        'status' => 'approved',
                        'approved_by' => auth()->id(),
                        'approved_at' => now(),
                    ]);
                    Notification::make()
                        ->title('Permintaan pembelian disetujui')
                        ->success()
                        ->send();
                    $this->redirect($this->getResource()::getUrl('index'));
                });

            $actions[] = Action::make('reject')
                ->label('Tolak')
                ->icon('heroicon-o-x-circle')
                ->color('danger')
                ->requiresConfirmation()
                ->modalHeading('Tolak Permintaan Pembelian')
                ->modalDescription('Masukkan alasan penolakan:')
                ->form([
                    \Filament\Forms\Components\Textarea::make('rejection_reason')
                        ->required()
                        ->label('Alasan Penolakan'),
                ])
                ->action(function (array $data) use ($record) {
                    $record->update([
                        'status' => 'rejected',
                        'rejection_reason' => $data['rejection_reason'],
                    ]);
                    Notification::make()
                        ->title('Permintaan pembelian ditolak')
                        ->success()
                        ->send();
                    $this->redirect($this->getResource()::getUrl('index'));
                });
        }

        if ($record->status === 'approved') {
            $actions[] = Action::make('createPO')
                ->label('Buat PO')
                ->icon('heroicon-o-document-plus')
                ->color('info')
                ->requiresConfirmation()
                ->modalHeading('Buat Purchase Order')
                ->modalDescription('Ini akan membuat Purchase Order baru dari permintaan ini dan mengubah status menjadi "Diorder". Lanjutkan?')
                ->action(function () use ($record) {
                    $poNumber = PurchaseOrder::generateNumber();
                    $defaultSupplierId = \App\Models\Supplier::first()?->id;

                    $po = PurchaseOrder::create([
                        'po_number' => $poNumber,
                        'supplier_id' => $defaultSupplierId,
                        'outlet_id' => $record->outlet_id,
                        'user_id' => auth()->id(),
                        'total_amount' => 0,
                        'status' => 'draft',
                        'notes' => 'Dibuat dari PR: ' . $record->pr_number,
                    ]);

                    foreach ($record->items as $item) {
                        $po->purchaseOrderItems()->create([
                            'product_id' => $item->product_id,
                            'quantity' => $item->quantity,
                            'unit_price' => 0,
                            'subtotal' => 0,
                        ]);
                    }

                    $record->update(['status' => 'ordered']);

                    Notification::make()
                        ->title('Purchase Order berhasil dibuat: ' . $poNumber)
                        ->success()
                        ->send();

                    $this->redirect(PurchaseOrderResource::getUrl('edit', ['record' => $po->id]));
                });
        }

        $actions[] = DeleteAction::make();

        return $actions;
    }
}
