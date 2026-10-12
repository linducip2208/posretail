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
        $data['writeoff_number'] = static::generateWriteOffNumber();
        $data['user_id'] = auth()->id();
        $data['total_loss'] = (float) ($data['unit_cost'] ?? 0) * (int) ($data['quantity'] ?? 0);

        return $data;
    }

    /**
     * Retry nomor WO bila balapan (kasus yang sama dengan nomor jurnal).
     */
    protected function handleRecordCreation(array $data): \Illuminate\Database\Eloquent\Model
    {
        for ($attempt = 1; $attempt <= 5; $attempt++) {
            try {
                $data['writeoff_number'] = static::generateWriteOffNumber(withLock: true);

                return parent::handleRecordCreation($data);
            } catch (\Illuminate\Database\QueryException $e) {
                $isDup = str_contains($e->getMessage(), 'write_offs_writeoff_number_unique')
                    || str_contains($e->getMessage(), "Duplicate entry 'WO-");
                if (! $isDup || $attempt >= 5) {
                    throw $e;
                }

                usleep(50000 * $attempt);
            }
        }

        return parent::handleRecordCreation($data);
    }

    protected static function generateWriteOffNumber(bool $withLock = false): string
    {
        $prefix = 'WO-'.now()->format('Ymd').'-';
        $query = \App\Models\WriteOff::where('writeoff_number', 'like', $prefix.'%')
            ->orderBy('writeoff_number', 'desc');

        if ($withLock && \Illuminate\Support\Facades\DB::transactionLevel() > 0) {
            $query->lockForUpdate();
        }

        $last = $query->value('writeoff_number');
        $seq = $last ? ((int) substr($last, -4) + 1) : 1;

        return $prefix.str_pad((string) $seq, 4, '0', STR_PAD_LEFT);
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

            $journal = null;
            for ($j = 1; $j <= 5; $j++) {
                try {
                    $journal = JournalEntry::create([
                        'journal_number' => JournalEntry::generateNextNumber(withLock: true),
                        'journal_date' => today(),
                        'reference_type' => 'writeoff',
                        'reference_id' => $record->id,
                        'description' => 'Write-Off: '.$record->writeoff_number.' - '.$record->product->name,
                        'status' => 'posted',
                        'posted_at' => now(),
                    ]);
                    break;
                } catch (\Illuminate\Database\QueryException $e) {
                    if (! JournalEntry::isDuplicateKeyException($e) || $j >= 5) {
                        throw $e;
                    }
                    usleep(50000 * $j);
                }
            }

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
