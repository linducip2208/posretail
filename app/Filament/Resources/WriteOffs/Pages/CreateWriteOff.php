<?php

namespace App\Filament\Resources\WriteOffs\Pages;

use App\Filament\Resources\WriteOffs\WriteOffResource;
use App\Models\Account;
use App\Models\JournalEntry;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\StockMovement;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\DB;

class CreateWriteOff extends CreateRecord
{
    protected static string $resource = WriteOffResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $seq = \App\Models\WriteOff::whereDate('created_at', today())->count() + 1;
        $data['writeoff_number'] = 'WO-' . date('Ymd') . '-' . str_pad($seq, 4, '0', STR_PAD_LEFT);
        $data['user_id'] = auth()->id();
        $data['total_loss'] = (float) ($data['unit_cost'] ?? 0) * (int) ($data['quantity'] ?? 0);

        return $data;
    }

    protected function afterCreate(): void
    {
        $record = $this->record;

        DB::transaction(function () use ($record) {
            if ($record->product_variant_id) {
                $variant = ProductVariant::find($record->product_variant_id);
                if ($variant) {
                    $variant->decrement('current_stock', $record->quantity);
                }
            } else {
                $product = Product::find($record->product_id);
                if ($product) {
                    $product->decrement('current_stock', $record->quantity);
                }
            }

            StockMovement::create([
                'product_id' => $record->product_id,
                'product_variant_id' => $record->product_variant_id,
                'outlet_id' => $record->outlet_id,
                'type' => 'adjustment',
                'quantity' => -$record->quantity,
                'reference_type' => 'writeoff',
                'reference_id' => $record->id,
                'notes' => 'Write-off: ' . $record->writeoff_number,
            ]);

            $expenseAccount = Account::firstOrCreate(
                ['code' => '5-9000'],
                ['name' => 'Beban Write-Off', 'type' => 'expense', 'normal_balance' => 'debit', 'active' => true]
            );
            $inventoryAccount = Account::firstOrCreate(
                ['code' => '1-1300'],
                ['name' => 'Persediaan', 'type' => 'asset', 'normal_balance' => 'debit', 'active' => true]
            );

            $jrnSeq = JournalEntry::whereDate('created_at', today())->count() + 1;
            $journal = JournalEntry::create([
                'journal_number' => 'JRN-' . date('Ymd') . '-' . str_pad($jrnSeq, 4, '0', STR_PAD_LEFT),
                'journal_date' => today(),
                'reference_type' => 'writeoff',
                'reference_id' => $record->id,
                'description' => 'Write-Off: ' . $record->writeoff_number . ' - ' . $record->product->name,
                'status' => 'posted',
                'posted_at' => now(),
            ]);

            $journal->items()->create([
                'account_id' => $expenseAccount->id,
                'debit' => $record->total_loss,
                'credit' => 0,
                'description' => 'Beban write-off barang',
            ]);

            $journal->items()->create([
                'account_id' => $inventoryAccount->id,
                'debit' => 0,
                'credit' => $record->total_loss,
                'description' => 'Pengurangan persediaan',
            ]);
        });
    }
}
