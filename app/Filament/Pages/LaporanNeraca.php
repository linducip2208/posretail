<?php

namespace App\Filament\Pages;

use App\Models\Outlet;
use Filament\Pages\Page;
use Illuminate\Support\Facades\DB;
use BackedEnum;
use UnitEnum;

class LaporanNeraca extends Page
{
    protected static string|UnitEnum|null $navigationGroup = '📈 Laporan';

    protected static ?int $navigationSort = 8;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-scale';

    protected static ?string $title = 'Laporan Neraca';

    protected string $view = 'filament.pages.laporan-neraca';

    public string $asOfDate;

    public ?int $outletId = null;

    public function mount(): void
    {
        $this->asOfDate = now()->format('Y-m-d');
    }

    public function getOutletsProperty()
    {
        return auth()->user()?->accessibleOutlets()?->get() ?? Outlet::where('active', true)->orderBy('name')->get();
    }

    /** Total Aset: SUM(debit) - SUM(credit) cumulative up to asOfDate */
    public function getTotalAsetProperty(): float
    {
        return (float) $this->journalItemQuery()
            ->where('accounts.type', 'asset')
            ->selectRaw('COALESCE(SUM(journal_entry_items.debit), 0) - COALESCE(SUM(journal_entry_items.credit), 0) as balance')
            ->value('balance');
    }

    /** Total Liabilitas: SUM(credit) - SUM(debit) cumulative up to asOfDate */
    public function getTotalLiabilitasProperty(): float
    {
        return (float) $this->journalItemQuery()
            ->where('accounts.type', 'liability')
            ->selectRaw('COALESCE(SUM(journal_entry_items.credit), 0) - COALESCE(SUM(journal_entry_items.debit), 0) as balance')
            ->value('balance');
    }

    /** Total Ekuitas: SUM(credit) - SUM(debit) + Net Profit from P&L */
    public function getTotalEkuitasProperty(): float
    {
        $equityBalance = (float) $this->journalItemQuery()
            ->where('accounts.type', 'equity')
            ->selectRaw('COALESCE(SUM(journal_entry_items.credit), 0) - COALESCE(SUM(journal_entry_items.debit), 0) as balance')
            ->value('balance');

        return $equityBalance + $this->netProfitYear;
    }

    /** Net Profit from all P&L accounts (revenue + cogs + expense) for current year up to asOfDate */
    public function getNetProfitYearProperty(): float
    {
        $startOfYear = date('Y-m-d', strtotime($this->asOfDate . ' -1 year'));
        $startOfYear = date('Y-01-01', strtotime($this->asOfDate));

        $revenue = (float) DB::table('journal_entry_items')
            ->join('accounts', 'journal_entry_items.account_id', '=', 'accounts.id')
            ->join('journal_entries', 'journal_entry_items.journal_entry_id', '=', 'journal_entries.id')
            ->where('accounts.type', 'revenue')
            ->where('accounts.active', true)
            ->where('journal_entries.status', 'posted')
            ->whereBetween('journal_entries.journal_date', [$startOfYear, $this->asOfDate])
            ->when($this->outletId, fn ($q) => $this->applyOutletFilter($q))
            ->selectRaw('COALESCE(SUM(journal_entry_items.credit), 0) - COALESCE(SUM(journal_entry_items.debit), 0) as balance')
            ->value('balance');

        $cogs = (float) DB::table('journal_entry_items')
            ->join('accounts', 'journal_entry_items.account_id', '=', 'accounts.id')
            ->join('journal_entries', 'journal_entry_items.journal_entry_id', '=', 'journal_entries.id')
            ->where('accounts.type', 'cogs')
            ->where('accounts.active', true)
            ->where('journal_entries.status', 'posted')
            ->whereBetween('journal_entries.journal_date', [$startOfYear, $this->asOfDate])
            ->when($this->outletId, fn ($q) => $this->applyOutletFilter($q))
            ->selectRaw('COALESCE(SUM(journal_entry_items.debit), 0) - COALESCE(SUM(journal_entry_items.credit), 0) as balance')
            ->value('balance');

        $expense = (float) DB::table('journal_entry_items')
            ->join('accounts', 'journal_entry_items.account_id', '=', 'accounts.id')
            ->join('journal_entries', 'journal_entry_items.journal_entry_id', '=', 'journal_entries.id')
            ->where('accounts.type', 'expense')
            ->where('accounts.active', true)
            ->where('journal_entries.status', 'posted')
            ->whereBetween('journal_entries.journal_date', [$startOfYear, $this->asOfDate])
            ->when($this->outletId, fn ($q) => $this->applyOutletFilter($q))
            ->selectRaw('COALESCE(SUM(journal_entry_items.debit), 0) - COALESCE(SUM(journal_entry_items.credit), 0) as balance')
            ->value('balance');

        return $revenue - $cogs - $expense;
    }

    public function getIsBalancedProperty(): bool
    {
        $diff = abs($this->totalAset - ($this->totalLiabilitas + $this->totalEkuitas));

        return $diff < 1; // tolerance 1 rupiah for rounding
    }

    /** Aset accounts grouped by parent, with balances */
    public function getAsetAccountsProperty()
    {
        return $this->getAccountsByType('asset');
    }

    /** Liabilitas accounts grouped by parent */
    public function getLiabilitasAccountsProperty()
    {
        return $this->getAccountsByType('liability');
    }

    /** Ekuitas accounts grouped by parent */
    public function getEkuitasAccountsProperty()
    {
        $accounts = $this->getAccountsByType('equity');

        // Add laba tahun berjalan as pseudo account
        if ($this->netProfitYear != 0) {
            $accounts->push((object) [
                'name' => 'Laba Tahun Berjalan',
                'code' => '',
                'balance' => $this->netProfitYear,
                'is_summary' => true,
            ]);
        }

        return $accounts;
    }

    protected function getAccountsByType(string $type)
    {
        $isDebitNormal = in_array($type, ['asset']);

        $query = $this->journalItemQuery()
            ->where('accounts.type', $type)
            ->selectRaw("
                accounts.id,
                accounts.name,
                accounts.code,
                accounts.parent_id,
                COALESCE(SUM(journal_entry_items.debit), 0) as total_debit,
                COALESCE(SUM(journal_entry_items.credit), 0) as total_credit
            ")
            ->groupBy('accounts.id', 'accounts.name', 'accounts.code', 'accounts.parent_id')
            ->orderBy('accounts.code');

        $items = $query->get()->map(function ($item) use ($isDebitNormal) {
            $item->balance = $isDebitNormal
                ? $item->total_debit - $item->total_credit
                : $item->total_credit - $item->total_debit;
            return $item;
        });

        // Also include accounts with zero balance that are active
        $allAccounts = DB::table('accounts')
            ->where('type', $type)
            ->where('active', true)
            ->select('id', 'name', 'code', 'parent_id')
            ->orderBy('code')
            ->get();

        $existingIds = $items->pluck('id')->toArray();

        foreach ($allAccounts as $acc) {
            if (!in_array($acc->id, $existingIds)) {
                $acc->balance = 0;
                $acc->total_debit = 0;
                $acc->total_credit = 0;
                $items->push($acc);
            }
        }

        return $items->sortBy('code')->values();
    }

    protected function journalItemQuery()
    {
        return DB::table('journal_entry_items')
            ->join('accounts', 'journal_entry_items.account_id', '=', 'accounts.id')
            ->join('journal_entries', 'journal_entry_items.journal_entry_id', '=', 'journal_entries.id')
            ->where('journal_entries.journal_date', '<=', $this->asOfDate)
            ->where('journal_entries.status', 'posted')
            ->where('accounts.active', true)
            ->whereIn('accounts.type', ['asset', 'liability', 'equity'])
            ->when($this->outletId, fn ($query) => $this->applyOutletFilter($query));
    }

    protected function applyOutletFilter($query): void
    {
        $query->where(function ($q) {
            $q->where(function ($sub) {
                $sub->where('journal_entries.reference_type', 'order')
                    ->whereIn('journal_entries.reference_id', function ($inner) {
                        $inner->select('id')->from('orders')->where('outlet_id', $this->outletId);
                    });
            })->orWhere(function ($sub) {
                $sub->where('journal_entries.reference_type', 'expense')
                    ->whereIn('journal_entries.reference_id', function ($inner) {
                        $inner->select('id')->from('expenses')->where('outlet_id', $this->outletId);
                    });
            })->orWhere(function ($sub) {
                $sub->where('journal_entries.reference_type', 'purchase_order')
                    ->whereIn('journal_entries.reference_id', function ($inner) {
                        $inner->select('id')->from('purchase_orders')->where('outlet_id', $this->outletId);
                    });
            });
        });
    }
}
