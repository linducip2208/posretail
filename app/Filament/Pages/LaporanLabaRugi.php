<?php

namespace App\Filament\Pages;

use App\Models\Outlet;
use Filament\Pages\Page;
use Illuminate\Support\Facades\DB;
use BackedEnum;
use UnitEnum;

class LaporanLabaRugi extends Page
{
    protected static string|UnitEnum|null $navigationGroup = '📈 Laporan';

    protected static ?int $navigationSort = 7;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-presentation-chart-line';

    protected static ?string $title = 'Laporan Laba Rugi';

    protected string $view = 'filament.pages.laporan-laba-rugi';

    public string $startDate;

    public string $endDate;

    public ?int $outletId = null;

    public function mount(): void
    {
        $this->startDate = now()->startOfMonth()->format('Y-m-d');
        $this->endDate = now()->format('Y-m-d');
    }

    public function getOutletsProperty()
    {
        return auth()->user()?->accessibleOutlets()?->get() ?? Outlet::where('active', true)->orderBy('name')->get();
    }

    /** Total Revenue: SUM(credit) - SUM(debit) for revenue accounts in period */
    public function getTotalRevenueProperty(): float
    {
        return (float) $this->journalItemQuery()
            ->where('accounts.type', 'revenue')
            ->selectRaw('COALESCE(SUM(journal_entry_items.credit), 0) - COALESCE(SUM(journal_entry_items.debit), 0) as balance')
            ->value('balance');
    }

    /** Total COGS: SUM(debit) - SUM(credit) for cogs accounts in period */
    public function getTotalCogsProperty(): float
    {
        return (float) $this->journalItemQuery()
            ->where('accounts.type', 'cogs')
            ->selectRaw('COALESCE(SUM(journal_entry_items.debit), 0) - COALESCE(SUM(journal_entry_items.credit), 0) as balance')
            ->value('balance');
    }

    /** Total Expenses: SUM(debit) - SUM(credit) for expense accounts in period */
    public function getTotalExpensesProperty(): float
    {
        return (float) $this->journalItemQuery()
            ->where('accounts.type', 'expense')
            ->selectRaw('COALESCE(SUM(journal_entry_items.debit), 0) - COALESCE(SUM(journal_entry_items.credit), 0) as balance')
            ->value('balance');
    }

    public function getGrossProfitProperty(): float
    {
        return $this->totalRevenue - $this->totalCogs;
    }

    public function getNetProfitProperty(): float
    {
        return $this->grossProfit - $this->totalExpenses;
    }

    public function getNetProfitMarginProperty(): float
    {
        if ($this->totalRevenue <= 0) {
            return 0;
        }

        return round(($this->netProfit / $this->totalRevenue) * 100, 2);
    }

    /** Detailed account list for P&L table */
    public function getAccountDetailsProperty()
    {
        $revenue = $this->journalItemQuery()
            ->where('accounts.type', 'revenue')
            ->selectRaw("
                accounts.id,
                accounts.name,
                accounts.type,
                accounts.code,
                COALESCE(SUM(journal_entry_items.debit), 0) as total_debit,
                COALESCE(SUM(journal_entry_items.credit), 0) as total_credit
            ")
            ->groupBy('accounts.id', 'accounts.name', 'accounts.type', 'accounts.code')
            ->orderBy('accounts.code')
            ->get()
            ->map(function ($item) {
                $item->balance = $item->total_credit - $item->total_debit;
                $item->category = 'Pendapatan';
                return $item;
            });

        $cogs = $this->journalItemQuery()
            ->where('accounts.type', 'cogs')
            ->selectRaw("
                accounts.id,
                accounts.name,
                accounts.type,
                accounts.code,
                COALESCE(SUM(journal_entry_items.debit), 0) as total_debit,
                COALESCE(SUM(journal_entry_items.credit), 0) as total_credit
            ")
            ->groupBy('accounts.id', 'accounts.name', 'accounts.type', 'accounts.code')
            ->orderBy('accounts.code')
            ->get()
            ->map(function ($item) {
                $item->balance = $item->total_debit - $item->total_credit;
                $item->category = 'HPP';
                return $item;
            });

        $expenses = $this->journalItemQuery()
            ->where('accounts.type', 'expense')
            ->selectRaw("
                accounts.id,
                accounts.name,
                accounts.type,
                accounts.code,
                COALESCE(SUM(journal_entry_items.debit), 0) as total_debit,
                COALESCE(SUM(journal_entry_items.credit), 0) as total_credit
            ")
            ->groupBy('accounts.id', 'accounts.name', 'accounts.type', 'accounts.code')
            ->orderBy('accounts.code')
            ->get()
            ->map(function ($item) {
                $item->balance = $item->total_debit - $item->total_credit;
                $item->category = 'Beban';
                return $item;
            });

        return $revenue->concat($cogs)->concat($expenses);
    }

    /** Expense breakdown by account for chart */
    public function getExpenseChartDataProperty(): array
    {
        $items = $this->journalItemQuery()
            ->where('accounts.type', 'expense')
            ->selectRaw("
                accounts.name,
                COALESCE(SUM(journal_entry_items.debit), 0) - COALESCE(SUM(journal_entry_items.credit), 0) as balance
            ")
            ->groupBy('accounts.id', 'accounts.name')
            ->having('balance', '>', 0)
            ->orderByDesc('balance')
            ->get();

        return [
            'labels' => $items->pluck('name')->toArray(),
            'data' => $items->pluck('balance')->map(fn ($v) => (float) $v)->toArray(),
        ];
    }

    /** Revenue breakdown by account for chart */
    public function getRevenueChartDataProperty(): array
    {
        $items = $this->journalItemQuery()
            ->where('accounts.type', 'revenue')
            ->selectRaw("
                accounts.name,
                COALESCE(SUM(journal_entry_items.credit), 0) - COALESCE(SUM(journal_entry_items.debit), 0) as balance
            ")
            ->groupBy('accounts.id', 'accounts.name')
            ->having('balance', '>', 0)
            ->orderByDesc('balance')
            ->get();

        return [
            'labels' => $items->pluck('name')->toArray(),
            'data' => $items->pluck('balance')->map(fn ($v) => (float) $v)->toArray(),
        ];
    }

    protected function journalItemQuery()
    {
        return DB::table('journal_entry_items')
            ->join('accounts', 'journal_entry_items.account_id', '=', 'accounts.id')
            ->join('journal_entries', 'journal_entry_items.journal_entry_id', '=', 'journal_entries.id')
            ->whereBetween('journal_entries.journal_date', [$this->startDate, $this->endDate])
            ->where('journal_entries.status', 'posted')
            ->where('accounts.active', true)
            ->whereIn('accounts.type', ['revenue', 'cogs', 'expense'])
            ->when($this->outletId, function ($query) {
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
            });
    }
}
