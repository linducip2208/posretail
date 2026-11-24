<?php

namespace App\Services\Tillora;

use App\Models\Account;
use App\Models\AssemblyOrder;
use App\Models\Attendance;
use App\Models\AuditLog;
use App\Models\BankStatement;
use App\Models\BinLocation;
use App\Models\BlogCategory;
use App\Models\BlogPost;
use App\Models\Budget;
use App\Models\CompanyAsset;
use App\Models\Consignment;
use App\Models\Customer;
use App\Models\CustomerDeposit;
use App\Models\CustomerGroup;
use App\Models\ExchangeRate;
use App\Models\Expense;
use App\Models\Installment;
use App\Models\JournalEntry;
use App\Models\JournalEntryItem;
use App\Models\MarketplaceOrder;
use App\Models\Outlet;
use App\Models\Product;
use App\Models\Provider;
use App\Models\PurchaseRequisition;
use App\Models\RawMaterial;
use App\Models\SerialNumber;
use App\Models\Supplier;
use App\Models\SupplierContract;
use App\Models\SupplierRating;
use App\Models\UnitConversion;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

/** DATA ASLI halaman Tools/Utilitas (100% Tillora). Diisi agen tillora-tools-*. */
class UtilityData
{
    // --- Keuangan & Akuntansi (agen tools-keuangan) ---
    public static function accounts(Request $r): array
    {
        $q = trim((string) $r->query('q', ''));
        $query = Account::with('parent')->orderBy('code');
        if ($q !== '') {
            $query->where(function ($w) use ($q) {
                $w->where('code', 'like', '%' . $q . '%')
                    ->orWhere('name', 'like', '%' . $q . '%')
                    ->orWhere('type', 'like', '%' . $q . '%');
            });
        }
        $rows = $query->get();
        $ids = $rows->pluck('id')->all();
        $sums = [];
        if (! empty($ids)) {
            $agg = JournalEntryItem::whereIn('account_id', $ids)
                ->selectRaw('account_id, COALESCE(SUM(debit),0) as d, COALESCE(SUM(credit),0) as c')
                ->groupBy('account_id')->get();
            foreach ($agg as $a) {
                $sums[$a->account_id] = [(float) $a->d, (float) $a->c];
            }
        }
        foreach ($rows as $acc) {
            $d = isset($sums[$acc->id]) ? $sums[$acc->id][0] : 0;
            $c = isset($sums[$acc->id]) ? $sums[$acc->id][1] : 0;
            $acc->total_debit = $d;
            $acc->total_credit = $c;
            $acc->saldo = $d - $c;
        }
        $typeCounts = Account::selectRaw('type, COUNT(*) as cnt')->groupBy('type')->orderBy('type')->get();
        $typeLabels = [];
        $typeValues = [];
        $typeRows = [];
        foreach ($typeCounts as $t) {
            $typeLabels[] = (string) ($t->type ?: 'Lainnya');
            $typeValues[] = (int) $t->cnt;
            $typeRows[] = ['type' => (string) ($t->type ?: 'Lainnya'), 'count' => (int) $t->cnt];
        }
        $activeCount = Account::where('active', 1)->count();

        return [
            'q' => $q,
            'rows' => $rows,
            'totalAccounts' => $rows->count(),
            'activeCount' => $activeCount,
            'typeRows' => $typeRows,
            'typeLabels' => $typeLabels,
            'typeValues' => $typeValues,
        ];
    }

    public static function journalEntries(Request $r): array
    {
        $from = (string) $r->query('from', now()->startOfMonth()->format('Y-m-d'));
        $to = (string) $r->query('to', now()->format('Y-m-d'));
        $query = JournalEntry::with(['items.account'])->orderByDesc('journal_date')->orderByDesc('id');
        if ($from !== '') {
            $query->whereDate('journal_date', '>=', $from);
        }
        if ($to !== '') {
            $query->whereDate('journal_date', '<=', $to);
        }
        $rows = $query->limit(100)->get();
        $entryIds = JournalEntry::query();
        if ($from !== '') {
            $entryIds->whereDate('journal_date', '>=', $from);
        }
        if ($to !== '') {
            $entryIds->whereDate('journal_date', '<=', $to);
        }
        $ids = $entryIds->pluck('id')->all();
        $totalDebit = 0;
        $totalCredit = 0;
        if (! empty($ids)) {
            $totalDebit = (float) JournalEntryItem::whereIn('journal_entry_id', $ids)->sum('debit');
            $totalCredit = (float) JournalEntryItem::whereIn('journal_entry_id', $ids)->sum('credit');
        }
        $countPosted = JournalEntry::where('status', 'posted');
        $countDraft = JournalEntry::where('status', 'draft');
        if ($from !== '') {
            $countPosted->whereDate('journal_date', '>=', $from);
            $countDraft->whereDate('journal_date', '>=', $from);
        }
        if ($to !== '') {
            $countPosted->whereDate('journal_date', '<=', $to);
            $countDraft->whereDate('journal_date', '<=', $to);
        }
        $trendLabels = [];
        $trendDebit = [];
        $trendCredit = [];
        for ($i = 13; $i >= 0; $i--) {
            $d = now()->subDays($i);
            $trendLabels[] = $d->format('d M');
            $dayIds = JournalEntry::whereDate('journal_date', $d->format('Y-m-d'))->pluck('id')->all();
            if (empty($dayIds)) {
                $trendDebit[] = 0;
                $trendCredit[] = 0;
            } else {
                $trendDebit[] = (float) JournalEntryItem::whereIn('journal_entry_id', $dayIds)->sum('debit');
                $trendCredit[] = (float) JournalEntryItem::whereIn('journal_entry_id', $dayIds)->sum('credit');
            }
        }

        return [
            'from' => $from,
            'to' => $to,
            'rows' => $rows,
            'totalDebit' => $totalDebit,
            'totalCredit' => $totalCredit,
            'countPosted' => $countPosted->count(),
            'countDraft' => $countDraft->count(),
            'totalCount' => $rows->count(),
            'trendLabels' => $trendLabels,
            'trendDebit' => $trendDebit,
            'trendCredit' => $trendCredit,
        ];
    }

    public static function expenses(Request $r): array
    {
        $rows = Expense::with(['outlet', 'user'])->orderByDesc('expense_date')->orderByDesc('id')->limit(60)->get();
        $monthStart = now()->startOfMonth()->format('Y-m-d');
        $totalMonth = (float) Expense::whereDate('expense_date', '>=', $monthStart)->sum('amount');
        $countMonth = Expense::whereDate('expense_date', '>=', $monthStart)->count();
        $totalAll = (float) Expense::sum('amount');
        $catAgg = Expense::selectRaw('category, COUNT(*) as cnt, COALESCE(SUM(amount),0) as total')
            ->groupBy('category')->orderByDesc('total')->limit(8)->get();
        $catLabels = [];
        $catValues = [];
        $catRows = [];
        foreach ($catAgg as $c) {
            $catLabels[] = (string) ($c->category ?: 'Lainnya');
            $catValues[] = (float) $c->total;
            $catRows[] = ['category' => (string) ($c->category ?: 'Lainnya'), 'count' => (int) $c->cnt, 'total' => (float) $c->total];
        }
        $trendLabels = [];
        $trendValues = [];
        for ($i = 29; $i >= 0; $i--) {
            $d = now()->subDays($i);
            $trendLabels[] = $d->format('d M');
            $trendValues[] = (float) Expense::whereDate('expense_date', $d->format('Y-m-d'))->sum('amount');
        }

        return [
            'rows' => $rows,
            'totalMonth' => $totalMonth,
            'countMonth' => $countMonth,
            'totalAll' => $totalAll,
            'totalCount' => Expense::count(),
            'catLabels' => $catLabels,
            'catValues' => $catValues,
            'catRows' => $catRows,
            'trendLabels' => $trendLabels,
            'trendValues' => $trendValues,
        ];
    }

    public static function budgets(Request $r): array
    {
        $rows = Budget::with('outlet')->orderByDesc('year')->orderByDesc('month')->get();
        foreach ($rows as $b) {
            $rt = (float) ($b->revenue_target ?: 0);
            $ar = (float) ($b->actual_revenue ?: 0);
            $el = (float) ($b->expense_limit ?: 0);
            $ae = (float) ($b->actual_expense ?: 0);
            $b->rev_pct = $rt > 0 ? min(100, (int) round($ar / $rt * 100)) : 0;
            $b->exp_pct = $el > 0 ? min(100, (int) round($ae / $el * 100)) : 0;
            $b->period_label = str_pad((string) $b->month, 2, '0', STR_PAD_LEFT) . '/' . $b->year;
            $b->status_label = 'Berjalan';
            if ($ar >= $rt && $rt > 0 && $ae <= $el) {
                $b->status_label = 'Tercapai';
            } elseif ($el > 0 && $ae > $el) {
                $b->status_label = 'Over budget';
            }
        }
        $periodLabels = [];
        $revTargets = [];
        $revActuals = [];
        $sorted = $rows->sortBy(function ($b) { return sprintf('%04d-%02d', $b->year, $b->month); })->take(12);
        foreach ($sorted as $b) {
            $periodLabels[] = $b->period_label;
            $revTargets[] = (float) ($b->revenue_target ?: 0);
            $revActuals[] = (float) ($b->actual_revenue ?: 0);
        }

        return [
            'rows' => $rows,
            'totalRevenueTarget' => (float) $rows->sum('revenue_target'),
            'totalExpenseLimit' => (float) $rows->sum('expense_limit'),
            'totalActualRevenue' => (float) $rows->sum('actual_revenue'),
            'totalActualExpense' => (float) $rows->sum('actual_expense'),
            'totalCount' => $rows->count(),
            'periodLabels' => $periodLabels,
            'revTargets' => $revTargets,
            'revActuals' => $revActuals,
        ];
    }

    public static function companyAssets(Request $r): array
    {
        $rows = CompanyAsset::with(['outlet', 'assignedUser'])->orderByDesc('purchase_date')->get();
        $catAgg = CompanyAsset::selectRaw('category, COUNT(*) as cnt, COALESCE(SUM(purchase_value),0) as total')
            ->groupBy('category')->orderByDesc('total')->get();
        $catLabels = [];
        $catValues = [];
        $catRows = [];
        foreach ($catAgg as $c) {
            $catLabels[] = (string) ($c->category ?: 'Lainnya');
            $catValues[] = (float) $c->total;
            $catRows[] = ['category' => (string) ($c->category ?: 'Lainnya'), 'count' => (int) $c->cnt, 'total' => (float) $c->total];
        }

        return [
            'rows' => $rows,
            'totalPurchase' => (float) $rows->sum('purchase_value'),
            'totalCurrent' => (float) $rows->sum('current_value'),
            'totalCount' => $rows->count(),
            'needMaintenance' => (int) $rows->where('status', 'perlu_perbaikan')->count(),
            'catLabels' => $catLabels,
            'catValues' => $catValues,
            'catRows' => $catRows,
        ];
    }

    public static function bankStatements(Request $r): array
    {
        $q = trim((string) $r->query('q', ''));
        $query = BankStatement::with('outlet')->orderByDesc('transaction_date')->orderByDesc('id');
        if ($q !== '') {
            $query->where(function ($w) use ($q) {
                $w->where('bank_name', 'like', '%' . $q . '%')
                    ->orWhere('account_number', 'like', '%' . $q . '%')
                    ->orWhere('description', 'like', '%' . $q . '%')
                    ->orWhere('reference', 'like', '%' . $q . '%');
            });
        }
        $all = (clone $query)->get();
        $rows = $all->take(60);
        $totalIn = (float) $all->sum('credit');
        $totalOut = (float) $all->sum('debit');
        $matched = (int) $all->where('is_matched', true)->count();

        return [
            'q' => $q,
            'rows' => $rows,
            'totalIn' => $totalIn,
            'totalOut' => $totalOut,
            'netFlow' => $totalIn - $totalOut,
            'totalCount' => $all->count(),
            'matchedCount' => $matched,
        ];
    }

    public static function exchangeRates(Request $r): array
    {
        $rows = ExchangeRate::orderByDesc('effective_date')->orderBy('currency')->get();
        $latest = [];
        foreach ($rows as $ex) {
            $cur = strtoupper((string) $ex->currency);
            if (! isset($latest[$cur])) {
                $latest[$cur] = [
                    'currency' => $cur,
                    'rate' => (float) $ex->rate,
                    'date' => $ex->effective_date ? $ex->effective_date->format('d M Y') : '-',
                ];
            }
        }
        $latestList = array_values($latest);
        $curLabels = [];
        $curValues = [];
        foreach ($latestList as $l) {
            $curLabels[] = $l['currency'];
            $curValues[] = $l['rate'];
        }

        return [
            'rows' => $rows,
            'totalCount' => $rows->count(),
            'currencyCount' => count($latestList),
            'latestList' => $latestList,
            'curLabels' => $curLabels,
            'curValues' => $curValues,
        ];
    }

    // --- Penjualan & People (agen tools-operasional-a) ---
    public static function marketplaceOrders(Request $r): array
    {
        $q = trim((string) $r->query('q', ''));
        $query = MarketplaceOrder::with('order')->latest();
        if ($q !== '') {
            $query->where(function ($w) use ($q) {
                $w->where('platform', 'like', '%' . $q . '%')
                    ->orWhere('customer_name', 'like', '%' . $q . '%')
                    ->orWhere('platform_order_id', 'like', '%' . $q . '%')
                    ->orWhere('platform_invoice', 'like', '%' . $q . '%');
            });
        }
        $orders = $query->limit(60)->get();

        $all = MarketplaceOrder::select('platform', DB::raw('COUNT(*) as cnt'), DB::raw('COALESCE(SUM(total_amount),0) as tot'))
            ->groupBy('platform')->orderByDesc('tot')->get();

        $platSummary = [];
        $platLabels = [];
        $platCounts = [];
        $platTotals = [];
        foreach ($all as $row) {
            $platSummary[] = [
                'platform' => (string) ($row->platform ?: 'Lainnya'),
                'count' => (int) $row->cnt,
                'total' => (float) $row->tot,
            ];
            $platLabels[] = (string) ($row->platform ?: 'Lainnya');
            $platCounts[] = (int) $row->cnt;
            $platTotals[] = (float) $row->tot;
        }

        $totalCount = (int) MarketplaceOrder::count();
        $totalValue = (float) MarketplaceOrder::sum('total_amount');
        $platformCount = (int) MarketplaceOrder::distinct('platform')->count('platform');
        $linkedCount = (int) MarketplaceOrder::whereNotNull('order_id')->count();
        $platMax = empty($platTotals) ? 0 : max($platTotals);

        return compact('orders', 'q', 'platSummary', 'platLabels', 'platCounts', 'platTotals', 'totalCount', 'totalValue', 'platformCount', 'linkedCount', 'platMax');
    }

    public static function installments(Request $r): array
    {
        $installments = Installment::with(['order.customer', 'schedules'])
            ->orderBy('due_date')->limit(80)->get();

        foreach ($installments as $inst) {
            $scheds = $inst->schedules ?? collect();
            $unpaid = $scheds->filter(function ($s) { return $s->status !== 'paid'; });
            $inst->tenor = $scheds->count() > 0 ? $scheds->count() : (int) ($inst->installment_number ?: 1);
            $inst->paid_count = $scheds->count() > 0
                ? $scheds->filter(function ($s) { return $s->status === 'paid'; })->count()
                : (($inst->status === 'paid') ? 1 : 0);
            if ($unpaid->count() > 0) {
                $inst->sisa = (float) $unpaid->sum('amount');
                $next = $unpaid->sortBy('due_date')->first();
                $inst->next_due = $next && $next->due_date ? Carbon::parse($next->due_date)->format('Y-m-d') : null;
                $inst->next_due_label = $next && $next->due_date ? Carbon::parse($next->due_date)->format('d M Y') : '—';
            } else {
                $inst->sisa = $inst->status === 'paid' ? 0.0 : (float) $inst->amount;
                $inst->next_due = $inst->status === 'paid' ? null : ($inst->due_date ? Carbon::parse($inst->due_date)->format('Y-m-d') : null);
                $inst->next_due_label = $inst->status === 'paid' ? 'Lunas' : ($inst->due_date ? Carbon::parse($inst->due_date)->format('d M Y') : '—');
            }
            $cust = optional(optional($inst->order)->customer)->name;
            $inst->customer_name = $cust ?: '—';
            $inst->order_number = optional($inst->order)->order_number ?: ('#' . $inst->order_id);
        }

        $totalReceivable = (float) Installment::where('status', '!=', 'paid')->sum('amount');
        $schedReceivable = (float) \App\Models\InstallmentSchedule::where('status', '!=', 'paid')->sum('amount');
        $due7 = (int) Installment::where('status', '!=', 'paid')
            ->whereDate('due_date', '<=', Carbon::today()->addDays(7))->count();
        $overdueCount = (int) Installment::where('status', 'overdue')
            ->orWhere(function ($w) { $w->where('status', '!=', 'paid')->whereDate('due_date', '<', Carbon::today()); })
            ->count();
        $paidTotal = (float) Installment::where('status', 'paid')->sum('amount');

        $byStatus = Installment::select('status', DB::raw('COUNT(*) as cnt'))
            ->groupBy('status')->get();
        $statusLabels = [];
        $statusCounts = [];
        foreach ($byStatus as $row) {
            $statusLabels[] = ucfirst((string) $row->status);
            $statusCounts[] = (int) $row->cnt;
        }

        return compact('installments', 'totalReceivable', 'schedReceivable', 'due7', 'overdueCount', 'paidTotal', 'statusLabels', 'statusCounts');
    }

    public static function customerDeposits(Request $r): array
    {
        $deposits = CustomerDeposit::with(['customer', 'outlet', 'user'])
            ->latest()->limit(60)->get();

        $topCustomers = Customer::orderByDesc('deposit_balance')->limit(10)->get();
        $totalDeposit = (float) Customer::sum('deposit_balance');
        $txCount = (int) CustomerDeposit::count();
        $holderCount = (int) Customer::where('deposit_balance', '>', 0)->count();

        $byType = CustomerDeposit::select('type', DB::raw('COUNT(*) as cnt'), DB::raw('COALESCE(SUM(amount),0) as tot'))
            ->groupBy('type')->get();
        $typeLabels = [];
        $typeCounts = [];
        $typeTotals = [];
        foreach ($byType as $row) {
            $typeLabels[] = ucfirst((string) ($row->type ?: 'Lainnya'));
            $typeCounts[] = (int) $row->cnt;
            $typeTotals[] = (float) $row->tot;
        }

        return compact('deposits', 'topCustomers', 'totalDeposit', 'txCount', 'holderCount', 'typeLabels', 'typeCounts', 'typeTotals');
    }

    public static function attendances(Request $r): array
    {
        $attendances = Attendance::with(['user', 'outlet'])
            ->orderByDesc('date')->orderByDesc('id')->limit(60)->get();

        $today = Carbon::today()->format('Y-m-d');
        $todayDate = Carbon::today()->format('d M Y');
        $todayRows = Attendance::whereDate('date', Carbon::today())->get();
        $todayTotal = $todayRows->count();
        $todayHadir = $todayRows->filter(function ($a) {
            return in_array(strtolower((string) $a->status), ['hadir', 'present', 'masuk', 'on_time', 'tepat_waktu']);
        })->count();
        $todayTerlambat = $todayRows->filter(function ($a) {
            return in_array(strtolower((string) $a->status), ['terlambat', 'late']);
        })->count();
        $todayAlfa = $todayRows->filter(function ($a) {
            return in_array(strtolower((string) $a->status), ['alfa', 'absent', 'mangkir', 'tidak_hadir']);
        })->count();

        $byStatus = Attendance::select('status', DB::raw('COUNT(*) as cnt'))
            ->where('date', '>=', Carbon::today()->subDays(30))
            ->groupBy('status')->get();
        $statusLabels = [];
        $statusCounts = [];
        foreach ($byStatus as $row) {
            $statusLabels[] = ucfirst((string) ($row->status ?: 'Tanpa status'));
            $statusCounts[] = (int) $row->cnt;
        }

        return compact('attendances', 'today', 'todayDate', 'todayTotal', 'todayHadir', 'todayTerlambat', 'todayAlfa', 'statusLabels', 'statusCounts');
    }

    public static function notifications(Request $r): array
    {
        $rows = DB::table('notifications')->orderByDesc('created_at')->limit(50)->get();

        $notes = [];
        foreach ($rows as $n) {
            $data = json_decode((string) ($n->data ?? '{}'), true);
            if (! is_array($data)) { $data = []; }
            $title = $data['title'] ?? $data['subject'] ?? $data['heading'] ?? null;
            $message = $data['message'] ?? $data['body'] ?? $data['text'] ?? $data['description'] ?? null;
            if ($title === null) {
                $title = $message !== null ? mb_substr((string) $message, 0, 60) : class_basename((string) $n->type);
            }
            if ($message === null) { $message = '—'; }
            $created = $n->created_at ? Carbon::parse($n->created_at) : null;
            $notes[] = [
                'id' => (string) $n->id,
                'type' => class_basename((string) $n->type),
                'type_full' => (string) $n->type,
                'title' => (string) $title,
                'message' => (string) $message,
                'is_read' => $n->read_at !== null,
                'created_label' => $created ? $created->format('d M Y H:i') : '—',
                'created_ago' => $created ? $created->diffForHumans() : '—',
            ];
        }

        $totalCount = (int) DB::table('notifications')->count();
        $unreadCount = (int) DB::table('notifications')->whereNull('read_at')->count();
        $typeGroups = DB::table('notifications')->select('type', DB::raw('COUNT(*) as cnt'))
            ->groupBy('type')->orderByDesc('cnt')->limit(6)->get();
        $typeLabels = [];
        $typeCounts = [];
        foreach ($typeGroups as $g) {
            $typeLabels[] = class_basename((string) $g->type);
            $typeCounts[] = (int) $g->cnt;
        }

        return compact('notes', 'totalCount', 'unreadCount', 'typeLabels', 'typeCounts');
    }

    public static function auditLogs(Request $r): array
    {
        $q = trim((string) $r->query('q', ''));
        $query = AuditLog::with('user')->latest();
        if ($q !== '') {
            $query->where(function ($w) use ($q) {
                $w->where('action', 'like', '%' . $q . '%')
                    ->orWhere('model_type', 'like', '%' . $q . '%')
                    ->orWhere('ip_address', 'like', '%' . $q . '%');
            });
        }
        $rawLogs = $query->limit(100)->get();

        $logs = [];
        foreach ($rawLogs as $l) {
            $modelShort = $l->model_type ? class_basename($l->model_type) : '—';
            $detail = null;
            if (is_array($l->new_values) && count($l->new_values) > 0) {
                $keys = array_keys($l->new_values);
                $detail = 'Ubah: ' . implode(', ', array_slice($keys, 0, 5));
            } elseif (is_array($l->old_values) && count($l->old_values) > 0) {
                $keys = array_keys($l->old_values);
                $detail = 'Data: ' . implode(', ', array_slice($keys, 0, 5));
            } else {
                $detail = '—';
            }
            $logs[] = [
                'user_name' => optional($l->user)->name ?: 'Sistem',
                'action' => (string) $l->action,
                'model' => $modelShort,
                'model_id' => $l->model_id,
                'ip' => $l->ip_address ?: '—',
                'detail' => $detail,
                'time_label' => $l->created_at ? $l->created_at->format('d M Y H:i') : '—',
            ];
        }

        $totalCount = (int) AuditLog::count();
        $byAction = AuditLog::select('action', DB::raw('COUNT(*) as cnt'))
            ->groupBy('action')->orderByDesc('cnt')->limit(6)->get();
        $actionLabels = [];
        $actionCounts = [];
        foreach ($byAction as $row) {
            $actionLabels[] = ucfirst((string) $row->action);
            $actionCounts[] = (int) $row->cnt;
        }

        return compact('logs', 'q', 'totalCount', 'actionLabels', 'actionCounts');
    }

    public static function blogPosts(Request $r): array
    {
        $posts = BlogPost::with(['category', 'author'])->latest()->limit(60)->get();
        $categories = BlogCategory::withCount('posts')->orderBy('name')->get();

        $totalPosts = (int) BlogPost::count();
        $publishedCount = (int) BlogPost::where('is_published', true)->count();
        $draftCount = $totalPosts - $publishedCount;
        $catCount = (int) BlogCategory::count();

        $catLabels = [];
        $catCounts = [];
        foreach ($categories as $c) {
            $catLabels[] = (string) $c->name;
            $catCounts[] = (int) $c->posts_count;
        }

        return compact('posts', 'categories', 'totalPosts', 'publishedCount', 'draftCount', 'catCount', 'catLabels', 'catCounts');
    }

    // --- Produksi & Inventory (agen tools-operasional-b) ---
    public static function consignments(Request $r): array
    {
        $consignments = Consignment::with(['supplier', 'product', 'outlet'])->latest()->take(100)->get();
        $statusSummary = Consignment::query()->selectRaw('status, COUNT(*) as c')->groupBy('status')->pluck('c', 'status')->toArray();
        $totalValue = 0.0;
        $totalQty = 0;
        $totalSold = 0;
        foreach ($consignments as $c) {
            $totalValue += ((float) ($c->sold_quantity ?? 0)) * ((float) ($c->unit_price ?? 0));
            $totalQty += (int) ($c->quantity ?? 0);
            $totalSold += (int) ($c->sold_quantity ?? 0);
            $c->sold_value = ((float) ($c->sold_quantity ?? 0)) * ((float) ($c->unit_price ?? 0));
            $c->remaining_qty = ((int) ($c->quantity ?? 0)) - ((int) ($c->sold_quantity ?? 0));
        }
        $chartLabels = [];
        $chartValues = [];
        foreach ($statusSummary as $k => $v) { $chartLabels[] = (string) $k; $chartValues[] = (int) $v; }
        $totalRemaining = $totalQty - $totalSold;
        return compact('consignments', 'statusSummary', 'totalValue', 'totalQty', 'totalSold', 'totalRemaining', 'chartLabels', 'chartValues');
    }
    public static function purchaseRequisitions(Request $r): array
    {
        $requisitions = PurchaseRequisition::with(['items.product', 'outlet', 'requester', 'approver'])->latest()->take(100)->get();
        $statusSummary = PurchaseRequisition::query()->selectRaw('status, COUNT(*) as c')->groupBy('status')->pluck('c', 'status')->toArray();
        $totalItems = 0;
        $totalQty = 0;
        foreach ($requisitions as $pr) {
            $totalItems += $pr->items->count();
            $parts = [];
            $i = 0;
            foreach ($pr->items as $it) {
                $totalQty += (int) ($it->quantity ?? 0);
                if ($i < 3) { $parts[] = (($it->product->name ?? null) ?: 'Item') . ' x ' . $it->quantity; }
                $i++;
            }
            $pr->item_summary = implode(', ', $parts);
        }
        $totalPr = $requisitions->count();
        $chartLabels = [];
        $chartValues = [];
        foreach ($statusSummary as $k => $v) { $chartLabels[] = (string) $k; $chartValues[] = (int) $v; }
        return compact('requisitions', 'statusSummary', 'totalPr', 'totalItems', 'totalQty', 'chartLabels', 'chartValues');
    }
    public static function supplierContracts(Request $r): array
    {
        $contracts = SupplierContract::with('supplier')->orderByDesc('end_date')->take(100)->get();
        $statusSummary = SupplierContract::query()->selectRaw('status, COUNT(*) as c')->groupBy('status')->pluck('c', 'status')->toArray();
        $totalValue = (float) SupplierContract::query()->sum('value');
        $limit = Carbon::now()->addDays(30)->toDateString();
        $expiring = SupplierContract::with('supplier')
            ->whereNotNull('end_date')
            ->whereDate('end_date', '<=', $limit)
            ->where('status', '!=', 'expired')
            ->orderBy('end_date')
            ->take(20)->get();
        $expiringCount = $expiring->count();
        $chartLabels = [];
        $chartValues = [];
        foreach ($statusSummary as $k => $v) { $chartLabels[] = (string) $k; $chartValues[] = (int) $v; }
        return compact('contracts', 'statusSummary', 'totalValue', 'expiring', 'expiringCount', 'chartLabels', 'chartValues');
    }
    public static function supplierRatings(Request $r): array
    {
        $ratings = SupplierRating::with(['supplier', 'purchaseOrder'])->latest()->take(100)->get();
        $avgRating = round((float) (SupplierRating::query()->avg('avg_score') ?? 0), 1);
        $totalRatings = (int) SupplierRating::query()->count();
        $perSupplier = SupplierRating::query()->selectRaw('supplier_id, AVG(avg_score) as avg_score, COUNT(*) as cnt')
            ->groupBy('supplier_id')->orderByDesc('avg_score')->get();
        $names = Supplier::whereIn('id', $perSupplier->pluck('supplier_id')->all())->pluck('name', 'id');
        $topSuppliers = [];
        $worstSuppliers = [];
        $idx = 0;
        foreach ($perSupplier as $row) {
            if ($idx < 5) { $topSuppliers[] = ['name' => $names[$row->supplier_id] ?? ('Supplier #' . $row->supplier_id), 'avg' => round((float) $row->avg_score, 1), 'count' => (int) $row->cnt]; }
            $idx++;
        }
        $rev = $perSupplier->reverse()->values();
        $j = 0;
        foreach ($rev as $row) {
            if ($j < 5) { $worstSuppliers[] = ['name' => $names[$row->supplier_id] ?? ('Supplier #' . $row->supplier_id), 'avg' => round((float) $row->avg_score, 1), 'count' => (int) $row->cnt]; }
            $j++;
        }
        $chartLabels = [];
        $chartValues = [];
        foreach ($topSuppliers as $t) { $chartLabels[] = $t['name']; $chartValues[] = $t['avg']; }
        return compact('ratings', 'avgRating', 'totalRatings', 'topSuppliers', 'worstSuppliers', 'chartLabels', 'chartValues');
    }
    public static function rawMaterials(Request $r): array
    {
        $materials = RawMaterial::with(['unit', 'outlet'])->orderBy('name')->take(200)->get();
        $totalValue = 0.0;
        $lowStock = [];
        foreach ($materials as $m) {
            $m->stock_value = ((float) ($m->cost_per_unit ?? 0)) * ((float) ($m->current_stock ?? 0));
            $totalValue += $m->stock_value;
            if ((float) ($m->current_stock ?? 0) <= (float) ($m->min_stock ?? 0)) { $lowStock[] = $m; }
        }
        $lowCount = count($lowStock);
        $sorted = $materials->sortByDesc('stock_value');
        $chartLabels = [];
        $chartValues = [];
        $n = 0;
        foreach ($sorted as $m) {
            if ($n >= 8) break;
            $chartLabels[] = (string) $m->name;
            $chartValues[] = round((float) $m->stock_value, 2);
            $n++;
        }
        return compact('materials', 'totalValue', 'lowStock', 'lowCount', 'chartLabels', 'chartValues');
    }
    public static function assemblyOrders(Request $r): array
    {
        $orders = AssemblyOrder::with(['product', 'outlet', 'user', 'items.rawMaterial'])->latest()->take(100)->get();
        $statusSummary = AssemblyOrder::query()->selectRaw('status, COUNT(*) as c')->groupBy('status')->pluck('c', 'status')->toArray();
        $totalQty = 0;
        foreach ($orders as $a) {
            $totalQty += (int) ($a->quantity ?? 0);
            $parts = [];
            $i = 0;
            foreach ($a->items as $it) {
                if ($i < 3) { $parts[] = (($it->rawMaterial->name ?? null) ?: 'Material') . ' x ' . $it->quantity; }
                $i++;
            }
            $a->material_summary = implode(', ', $parts);
        }
        $chartLabels = [];
        $chartValues = [];
        foreach ($statusSummary as $k => $v) { $chartLabels[] = (string) $k; $chartValues[] = (int) $v; }
        return compact('orders', 'statusSummary', 'totalQty', 'chartLabels', 'chartValues');
    }
    public static function binLocations(Request $r): array
    {
        $bins = BinLocation::with('outlet')->withCount('products')->orderBy('code')->take(200)->get();
        $totalProducts = 0;
        $emptyBinCount = 0;
        foreach ($bins as $b) { $totalProducts += (int) ($b->products_count ?? 0); if ((int) ($b->products_count ?? 0) === 0) { $emptyBinCount++; } }
        $sorted = $bins->sortByDesc('products_count')->values();
        $chartLabels = [];
        $chartValues = [];
        $n = 0;
        foreach ($sorted as $b) {
            if ($n >= 10) break;
            $chartLabels[] = (string) $b->code;
            $chartValues[] = (int) ($b->products_count ?? 0);
            $n++;
        }
        return compact('bins', 'totalProducts', 'emptyBinCount', 'chartLabels', 'chartValues');
    }
    // --- Sistem & form interaktif (agen tools-interaktif) ---
    public static function serialNumbers(Request $r): array
    {
        $q = trim((string) $r->input('q', ''));

        $serials = SerialNumber::with(['product', 'outlet'])
            ->when($q !== '', function ($x) use ($q) {
                $x->where(function ($w) use ($q) {
                    $w->where('serial_number', 'like', "%{$q}%")
                        ->orWhereHas('product', function ($p) use ($q) {
                            $p->where('name', 'like', "%{$q}%");
                        });
                });
            })
            ->latest()->take(100)->get();

        return [
            'serials' => $serials,
            'q' => $q,
            'totalCount' => (int) SerialNumber::count(),
            'inStockCount' => (int) SerialNumber::where('status', 'in_stock')->count(),
            'soldCount' => (int) SerialNumber::where('status', 'sold')->count(),
            'returnedCount' => (int) SerialNumber::where('status', 'returned')->count(),
            'defectiveCount' => (int) SerialNumber::where('status', 'defective')->count(),
        ];
    }

    public static function unitConversions(Request $r): array
    {
        $conversions = UnitConversion::with(['fromUnit', 'toUnit', 'product'])
            ->orderBy('id')->get();

        return [
            'conversions' => $conversions,
            'totalCount' => $conversions->count(),
        ];
    }

    public static function providers(Request $r): array
    {
        $providers = Provider::with('paymentMethods')->orderBy('name')->get();

        return [
            'providers' => $providers,
            'totalCount' => $providers->count(),
            'activeCount' => (int) Provider::where('is_active', true)->count(),
            'defaultProvider' => Provider::where('is_default', true)->first(['id', 'name', 'type']),
        ];
    }

    public static function broadcastWa(Request $r): array
    {
        $groups = CustomerGroup::orderBy('name')->get(['id', 'name']);
        $selectedGroupId = (string) $r->input('customer_group_id', '');

        $groupOptions = [];
        foreach ($groups as $g) {
            $count = (int) Customer::where('active', true)
                ->whereNotNull('phone')->where('phone', '<>', '')
                ->where('customer_group_id', $g->id)->count();
            $groupOptions[] = ['id' => $g->id, 'name' => $g->name, 'count' => $count];
        }

        $totalRecipients = (int) Customer::where('active', true)
            ->whereNotNull('phone')->where('phone', '<>', '')->count();

        $selectedRecipients = 0;
        foreach ($groupOptions as $opt) {
            if ((string) $opt['id'] === $selectedGroupId) {
                $selectedRecipients = $opt['count'];
            }
        }

        return [
            'groupOptions' => $groupOptions,
            'selectedGroupId' => $selectedGroupId,
            'totalRecipients' => $totalRecipients,
            'selectedRecipients' => $selectedRecipients,
            'totalGroups' => count($groupOptions),
        ];
    }

    public static function cetakLabel(Request $r): array
    {
        $q = trim((string) $r->input('q', ''));

        $products = Product::select('id', 'name', 'sku', 'barcode', 'selling_price')
            ->when($q !== '', function ($x) use ($q) {
                $x->where(function ($w) use ($q) {
                    $w->where('name', 'like', "%{$q}%")
                        ->orWhere('sku', 'like', "%{$q}%")
                        ->orWhere('barcode', 'like', "%{$q}%");
                });
            })
            ->orderBy('name')->take(24)->get();

        return [
            'products' => $products,
            'q' => $q,
        ];
    }

    public static function stockTakeMobile(Request $r): array
    {
        $outlets = Outlet::where('active', true)->orderBy('name')->get(['id', 'name', 'code']);
        $products = Product::orderBy('name')
            ->get(['id', 'name', 'sku', 'current_stock']);

        $stockMap = [];
        $productOptions = [];
        foreach ($products as $p) {
            $stockMap[] = ['id' => $p->id, 'stock' => (int) $p->current_stock];
            $productOptions[] = ['id' => $p->id, 'label' => $p->name.' ('.$p->sku.')'];
        }

        return [
            'outlets' => $outlets,
            'products' => $products,
            'stockMap' => $stockMap,
            'productOptions' => $productOptions,
            'selectedOutletId' => (string) $r->input('outlet_id', ''),
        ];
    }
}
