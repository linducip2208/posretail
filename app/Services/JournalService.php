<?php

namespace App\Services;

use App\Models\Account;
use App\Models\Expense;
use App\Models\JournalEntry;
use App\Models\Order;
use App\Models\Payment;
use App\Models\PurchaseOrder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Journal Service — auto-posting jurnal double-entry.
 * Dipanggil dari model events (Order, Payment, Expense, PurchaseOrder).
 */
class JournalService
{
    /**
     * Post journal saat order completed.
     * Debit: Piutang Usaha / Kas    Credit: Pendapatan
     */
    public static function postOrderRevenue(Order $order): ?JournalEntry
    {
        $arAccount = static::getAccount('1-1100', 'Piutang Usaha');
        $revenueAccount = static::getAccount('4-1000', 'Pendapatan Penjualan');
        $cogsAccount = static::getAccount('5-1000', 'HPP');
        $inventoryAccount = static::getAccount('1-1300', 'Persediaan');
        $discAccount = static::getAccount('4-1100', 'Diskon Penjualan');

        if (!$revenueAccount) {
            Log::warning('Journal: revenue account not found');
            return null;
        }

        $items = [];
        $totalRevenue = $order->total_amount;
        $totalDiscount = $order->discount_amount ?? 0;
        $totalCOGS = 0;

        foreach ($order->orderItems as $item) {
            $cost = ($item->product?->cost_price ?? 0) * $item->quantity;
            $totalCOGS += $cost;
        }

        // Piutang / Kas
        if ($arAccount) {
            $items[] = ['account_id' => $arAccount->id, 'debit' => $totalRevenue - $totalDiscount, 'credit' => 0, 'description' => 'Piutang penjualan'];
        }

        // Diskon
        if ($totalDiscount > 0 && $discAccount) {
            $items[] = ['account_id' => $discAccount->id, 'debit' => $totalDiscount, 'credit' => 0, 'description' => 'Diskon penjualan'];
        }

        // Pendapatan
        $items[] = ['account_id' => $revenueAccount->id, 'debit' => 0, 'credit' => $totalRevenue, 'description' => 'Pendapatan penjualan'];

        // HPP
        if ($totalCOGS > 0 && $cogsAccount && $inventoryAccount) {
            $items[] = ['account_id' => $cogsAccount->id, 'debit' => $totalCOGS, 'credit' => 0, 'description' => 'HPP penjualan'];
            $items[] = ['account_id' => $inventoryAccount->id, 'debit' => 0, 'credit' => $totalCOGS, 'description' => 'Keluar persediaan'];
        }

        return static::createJournal($order->created_at, $items, 'order', $order->id, "Penjualan #{$order->order_number}");
    }

    /**
     * Post journal saat payment diterima.
     * Debit: Kas    Credit: Piutang Usaha
     */
    public static function postPaymentReceived(Payment $payment): ?JournalEntry
    {
        $cashAccount = static::getAccount('1-1000', 'Kas');
        $arAccount = static::getAccount('1-1100', 'Piutang Usaha');

        if (!$cashAccount) {
            Log::warning('Journal: cash account not found');
            return null;
        }

        $items = [];
        $items[] = ['account_id' => $cashAccount->id, 'debit' => $payment->amount, 'credit' => 0, 'description' => 'Penerimaan pembayaran'];

        if ($arAccount) {
            $items[] = ['account_id' => $arAccount->id, 'debit' => 0, 'credit' => $payment->amount, 'description' => 'Pelunasan piutang'];
        }

        return static::createJournal($payment->created_at, $items, 'payment', $payment->id, "Pembayaran order #{$payment->order?->order_number}");
    }

    /**
     * Post journal saat expense dibuat.
     * Debit: Beban    Credit: Kas
     */
    public static function postExpense(Expense $expense): ?JournalEntry
    {
        $categoryMap = [
            'operasional' => '5-2000', 'utilities' => '5-3000', 'sewa' => '5-4000',
            'gaji' => '5-5000', 'marketing' => '5-6000', 'maintenance' => '5-7000',
            'lainnya' => '5-8000',
        ];

        $expenseCode = $categoryMap[$expense->category] ?? '5-8000';
        $expenseAccount = Account::where('code', $expenseCode)->first();
        if (!$expenseAccount) {
            $expenseAccount = static::getAccount('5-2000', 'Beban Operasional');
        }
        $cashAccount = static::getAccount('1-1000', 'Kas');

        if (!$expenseAccount || !$cashAccount) {
            Log::warning('Journal: expense/cash account not found');
            return null;
        }

        $items = [
            ['account_id' => $expenseAccount->id, 'debit' => $expense->amount, 'credit' => 0, 'description' => $expense->description ?? 'Beban ' . $expense->category],
            ['account_id' => $cashAccount->id, 'debit' => 0, 'credit' => $expense->amount, 'description' => 'Pembayaran beban'],
        ];

        return static::createJournal($expense->expense_date, $items, 'expense', $expense->id, "Beban: {$expense->description}");
    }

    /**
     * Post journal saat PO diterima.
     * Debit: Persediaan    Credit: Hutang Usaha
     */
    public static function postPOReceived(PurchaseOrder $po): ?JournalEntry
    {
        $inventoryAccount = static::getAccount('1-1300', 'Persediaan');
        $apAccount = static::getAccount('2-1000', 'Hutang Usaha');

        if (!$inventoryAccount || !$apAccount) {
            Log::warning('Journal: inventory/ap account not found');
            return null;
        }

        $items = [
            ['account_id' => $inventoryAccount->id, 'debit' => $po->total_amount, 'credit' => 0, 'description' => 'Penerimaan barang PO'],
            ['account_id' => $apAccount->id, 'debit' => 0, 'credit' => $po->total_amount, 'description' => 'Hutang supplier'],
        ];

        return static::createJournal(now(), $items, 'purchase_order', $po->id, "PO #{$po->po_number}");
    }

    /**
     * Create a journal entry with items.
     */
    protected static function createJournal($date, array $items, string $refType, int $refId, string $desc): ?JournalEntry
    {
        $totalDebit = collect($items)->sum('debit');
        $totalCredit = collect($items)->sum('credit');

        if (abs($totalDebit - $totalCredit) > 0.01) {
            Log::warning("Journal unbalanced: debit={$totalDebit} credit={$totalCredit}");
            return null;
        }

        if ($totalDebit == 0 && $totalCredit == 0) {
            return null;
        }

        $seq = JournalEntry::whereDate('created_at', today())->count() + 1;

        return DB::transaction(function () use ($date, $items, $refType, $refId, $desc, $seq) {
            $journal = JournalEntry::create([
                'journal_number' => 'JRN-' . date('Ymd') . '-' . str_pad($seq, 4, '0', STR_PAD_LEFT),
                'journal_date' => $date,
                'reference_type' => $refType,
                'reference_id' => $refId,
                'description' => $desc,
                'status' => 'posted',
                'posted_at' => now(),
            ]);

            foreach ($items as $item) {
                $journal->items()->create($item);
            }

            return $journal;
        });
    }

    /**
     * Get or create a standard account by code.
     */
    protected static function getAccount(string $code, string $name): ?Account
    {
        $account = Account::where('code', $code)->first();
        if (!$account) {
            $typeMap = [
                '1-' => 'asset', '2-' => 'liability', '3-' => 'equity',
                '4-' => 'revenue', '5-' => 'expense', '6-' => 'cogs',
            ];
            $prefix = substr($code, 0, 2);
            $type = $typeMap[$prefix] ?? 'expense';
            $normal = in_array($type, ['asset', 'expense', 'cogs']) ? 'debit' : 'credit';

            $account = Account::create([
                'code' => $code,
                'name' => $name,
                'type' => $type,
                'normal_balance' => $normal,
                'active' => true,
            ]);
        }
        return $account;
    }

    /**
     * Seed default Chart of Accounts.
     */
    public static function seedDefaultCOA(): void
    {
        $accounts = [
            // Aset (1-)
            ['1-1000', 'Kas', 'asset'],
            ['1-1100', 'Piutang Usaha', 'asset'],
            ['1-1200', 'Piutang Karyawan', 'asset'],
            ['1-1300', 'Persediaan', 'asset'],
            ['1-2000', 'Peralatan', 'asset'],
            ['1-3000', 'Akumulasi Penyusutan', 'asset'],

            // Liabilitas (2-)
            ['2-1000', 'Hutang Usaha', 'liability'],
            ['2-2000', 'Hutang Bank', 'liability'],
            ['2-3000', 'Pendapatan Diterima Dimuka', 'liability'],

            // Ekuitas (3-)
            ['3-1000', 'Modal Pemilik', 'equity'],
            ['3-2000', 'Prive', 'equity'],
            ['3-3000', 'Laba Ditahan', 'equity'],

            // Pendapatan (4-)
            ['4-1000', 'Pendapatan Penjualan', 'revenue'],
            ['4-1100', 'Diskon Penjualan', 'revenue'],
            ['4-2000', 'Pendapatan Lain-lain', 'revenue'],

            // Beban (5-)
            ['5-1000', 'HPP', 'cogs'],
            ['5-2000', 'Beban Operasional', 'expense'],
            ['5-3000', 'Beban Listrik & Air', 'expense'],
            ['5-4000', 'Beban Sewa', 'expense'],
            ['5-5000', 'Beban Gaji', 'expense'],
            ['5-6000', 'Beban Marketing', 'expense'],
            ['5-7000', 'Beban Perbaikan', 'expense'],
            ['5-8000', 'Beban Lain-lain', 'expense'],
            ['5-9000', 'Beban Penyusutan', 'expense'],
        ];

        foreach ($accounts as [$code, $name, $type]) {
            $normal = in_array($type, ['asset', 'expense', 'cogs']) ? 'debit' : 'credit';
            Account::firstOrCreate(
                ['code' => $code],
                ['name' => $name, 'type' => $type, 'normal_balance' => $normal, 'active' => true]
            );
        }
    }
}
