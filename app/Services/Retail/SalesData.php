<?php

namespace App\Services\Retail;

use App\Models\CashDrawerTransaction;
use App\Models\Category;
use App\Models\Customer;
use App\Models\HeldCart;
use App\Models\Order;
use App\Models\Outlet;
use App\Models\Payment;
use App\Models\PaymentMethod;
use App\Models\Product;
use App\Models\Retur;
use App\Models\Shift;
use App\Models\TaxInvoice;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/** DATA ASLI halaman Sales + POS. */
class SalesData
{
    public static function salesList(Request $r): array
    {
        $q = trim((string) $r->input('q', ''));
        $ordersQuery = Order::with(['customer', 'outlet', 'user', 'orderItems', 'payments.paymentMethod'])
            ->when($q !== '', function ($qq) use ($q) {
                $qq->where(function ($w) use ($q) {
                    $w->where('order_number', 'like', "%{$q}%")
                        ->orWhereHas('customer', fn ($c) => $c->where('name', 'like', "%{$q}%"));
                });
            })
            ->latest();

        $orders = (clone $ordersQuery)->take(60)->get();

        $today = Carbon::today();
        $weekStart = Carbon::today()->subDays(6);
        $salesToday = (float) Order::where('order_status', 'completed')->whereDate('created_at', $today)->sum('total_amount');
        $countToday = Order::whereDate('created_at', $today)->count();
        $salesWeek = (float) Order::where('order_status', 'completed')->whereDate('created_at', '>=', $weekStart)->sum('total_amount');
        $countWeek = Order::whereDate('created_at', '>=', $weekStart)->count();
        $refundedTotal = (float) Retur::where('status', 'completed')->sum('total_amount');
        $pendingCount = Order::where('order_status', 'pending')->count();

        // 7 hari terakhir untuk chart
        $labels = [];
        $daily = [];
        for ($i = 6; $i >= 0; $i--) {
            $d = Carbon::today()->subDays($i);
            $labels[] = $d->format('d M');
            $daily[] = (float) Order::where('order_status', 'completed')->whereDate('created_at', $d)->sum('total_amount');
        }

        return compact('orders', 'q', 'salesToday', 'countToday', 'salesWeek', 'countWeek', 'refundedTotal', 'pendingCount', 'labels', 'daily');
    }

    public static function saleDetails(Request $r): array
    {
        $id = $r->input('id');
        abort_if(blank($id), 404);

        $order = Order::with(['customer', 'outlet', 'user', 'orderItems.product', 'payments.paymentMethod'])
            ->where(function ($qq) use ($id) {
                $qq->where('order_number', $id);
                if (is_numeric($id)) {
                    $qq->orWhere('id', $id);
                }
            })->first();
        abort_if(! $order, 404);

        return ['order' => $order];
    }

    public static function salesReturns(Request $r): array
    {
        $q = trim((string) $r->input('q', ''));
        $returns = Retur::with(['order.customer', 'order', 'outlet', 'user', 'returnItems'])
            ->when($q !== '', fn ($qq) => $qq->where('return_number', 'like', "%{$q}%"))
            ->latest()->take(50)->get();

        $totalRetur = (float) Retur::where('status', 'completed')->sum('total_amount');
        $countMonth = Retur::whereMonth('created_at', Carbon::now()->month)->whereYear('created_at', Carbon::now()->year)->count();
        $pendingCount = Retur::where('status', 'pending')->count();

        $labels = [];
        $daily = [];
        for ($i = 6; $i >= 0; $i--) {
            $d = Carbon::today()->subDays($i);
            $labels[] = $d->format('d M');
            $daily[] = (int) Retur::whereDate('created_at', $d)->count();
        }

        return compact('returns', 'q', 'totalRetur', 'countMonth', 'pendingCount', 'labels', 'daily');
    }

    public static function salesInvoices(Request $r): array
    {
        $q = trim((string) $r->input('q', ''));
        $invoices = TaxInvoice::with(['order.customer', 'order', 'outlet'])
            ->when($q !== '', fn ($qq) => $qq->where('invoice_number', 'like', "%{$q}%"))
            ->latest()->take(50)->get();

        $totalDpp = (float) TaxInvoice::sum('dpp');
        $totalPpn = (float) TaxInvoice::sum('ppn_amount');
        $count = TaxInvoice::count();

        return compact('invoices', 'q', 'totalDpp', 'totalPpn', 'count');
    }

    public static function salesPayments(Request $r): array
    {
        $q = trim((string) $r->input('q', ''));
        $payments = Payment::with(['paymentMethod', 'order'])
            ->when($q !== '', function ($qq) use ($q) {
                $qq->where('reference_number', 'like', "%{$q}%")
                    ->orWhereHas('order', fn ($o) => $o->where('order_number', 'like', "%{$q}%"));
            })
            ->latest()->take(50)->get();

        $perMethod = Payment::selectRaw('payment_method_id, COALESCE(SUM(amount),0) as total')
            ->groupBy('payment_method_id')->pluck('total', 'payment_method_id');
        $methodNames = PaymentMethod::pluck('name', 'id');
        $totalsPerMethod = [];
        foreach ($perMethod as $mid => $total) {
            $totalsPerMethod[] = ['name' => $methodNames[$mid] ?? 'Lainnya', 'total' => (float) $total];
        }
        usort($totalsPerMethod, fn ($a, $b) => $b['total'] <=> $a['total']);
        $totalToday = (float) Payment::whereDate('created_at', Carbon::today())->sum('amount');
        $methodLabels = array_column($totalsPerMethod, 'name');
        $methodTotals = array_column($totalsPerMethod, 'total');

        return compact('payments', 'q', 'totalsPerMethod', 'totalToday', 'methodLabels', 'methodTotals');
    }

    public static function cashManagement(Request $r): array
    {
        $transactions = CashDrawerTransaction::with(['shift.user', 'shift', 'order'])
            ->latest()->take(50)->get();

        $today = Carbon::today();
        $cashInToday = (float) CashDrawerTransaction::where('type', 'cash_in')->whereDate('created_at', $today)->sum('amount');
        $cashOutToday = (float) CashDrawerTransaction::where('type', 'cash_out')->whereDate('created_at', $today)->sum('amount');
        $openShifts = Shift::where('status', 'open')->count();

        return compact('transactions', 'cashInToday', 'cashOutToday', 'openShifts');
    }

    public static function registersList(Request $r): array
    {
        $outlets = Outlet::where('active', 1)->orderBy('name')->get();
        if ($outlets->isEmpty()) {
            $outlets = Outlet::orderBy('name')->get();
        }
        $today = Carbon::today();

        $openShifts = Shift::with('user')->where('status', 'open')->get()->keyBy('outlet_id');
        $revenueToday = Order::where('order_status', 'completed')->whereDate('created_at', $today)
            ->selectRaw('outlet_id, COALESCE(SUM(total_amount),0) as total')->groupBy('outlet_id')->pluck('total', 'outlet_id');
        $countToday = Order::whereDate('created_at', $today)
            ->selectRaw('outlet_id, COUNT(*) as c')->groupBy('outlet_id')->pluck('c', 'outlet_id');

        $registers = $outlets->map(function ($o) use ($openShifts, $revenueToday, $countToday) {
            $shift = $openShifts->get($o->id);

            return [
                'outlet' => $o,
                'open' => $shift !== null,
                'shift' => $shift,
                'revenue_today' => (float) ($revenueToday[$o->id] ?? 0),
                'count_today' => (int) ($countToday[$o->id] ?? 0),
            ];
        });

        $totalRevenueToday = (float) $revenueToday->sum();

        return compact('registers', 'totalRevenueToday');
    }

    public static function shiftManagement(Request $r): array
    {
        $shifts = Shift::with(['user', 'outlet'])->latest('started_at')->take(20)->get();
        $openShifts = Shift::with(['user', 'outlet'])->where('status', 'open')->latest('started_at')->get();

        return compact('shifts', 'openShifts');
    }

    public static function posTerminal(Request $r): array
    {
        $products = Product::with('category')->where('active', 1)->where('current_stock', '>', 0)
            ->orderBy('name')->take(60)->get();
        if ($products->isEmpty()) {
            $products = Product::with('category')->where('active', 1)->orderBy('name')->take(60)->get();
        }
        $categories = Category::orderBy('name')->take(30)->get(['id', 'name']);
        $heldCount = HeldCart::count();
        $methods = PaymentMethod::where('active', 1)->orderBy('name')->get();
        if ($methods->isEmpty()) {
            $methods = PaymentMethod::orderBy('name')->get();
        }
        $taxPercent = (float) \App\Models\SystemSetting::getValue('tax_percent', '0');
        $customers = \App\Models\Customer::orderBy('name')->limit(100)->get(['id', 'name']);
        $catalogJson = $products->map(fn ($p) => [
            'id' => $p->id, 'name' => $p->name, 'sku' => $p->sku,
            'price' => (float) $p->selling_price, 'stock' => (int) $p->current_stock,
            'cat' => $p->category_id, 'catName' => optional($p->category)->name,
            'tracking' => $p->serial_tracking ?? 'none',
        ])->values()->all();

        return compact('products', 'categories', 'heldCount', 'methods', 'taxPercent', 'customers', 'catalogJson');
    }

    public static function posProductSearch(Request $r): array
    {
        $q = trim((string) $r->input('q', ''));
        $products = Product::with('category')
            ->when($q !== '', function ($qq) use ($q) {
                $qq->where(function ($w) use ($q) {
                    $w->where('name', 'like', "%{$q}%")
                        ->orWhere('sku', 'like', "%{$q}%")
                        ->orWhere('barcode', 'like', "%{$q}%");
                });
            })
            ->orderBy('name')->take(60)->get();

        return compact('products', 'q');
    }

    public static function posCart(Request $r): array
    {
        $carts = HeldCart::with(['user', 'outlet', 'customer'])->latest()->get();
        $carts->each(function ($c) {
            $items = is_array($c->items) ? $c->items : [];
            $c->line_total = collect($items)->sum(fn ($i) => ((float) ($i['price'] ?? 0)) * ((int) ($i['qty'] ?? 0)));
            $c->line_count = collect($items)->sum(fn ($i) => (int) ($i['qty'] ?? 0));
        });
        $grandTotal = (float) $carts->sum('line_total');

        return compact('carts', 'grandTotal');
    }

    public static function posPayment(Request $r): array
    {
        $methods = PaymentMethod::orderBy('name')->get();
        $openCarts = HeldCart::with(['user', 'outlet', 'customer'])->latest()->take(10)->get();
        $openCarts->each(function ($c) {
            $items = is_array($c->items) ? $c->items : [];
            $c->line_total = collect($items)->sum(fn ($i) => ((float) ($i['price'] ?? 0)) * ((int) ($i['qty'] ?? 0)));
        });

        return compact('methods', 'openCarts');
    }

    public static function posReceipt(Request $r): array
    {
        $id = $r->input('id');
        $order = null;
        if (filled($id)) {
            $order = Order::with(['customer', 'outlet', 'user', 'orderItems.product', 'payments.paymentMethod'])
                ->where(function ($qq) use ($id) {
                    $qq->where('order_number', $id);
                    if (is_numeric($id)) {
                        $qq->orWhere('id', $id);
                    }
                })->first();
            abort_if(! $order, 404);
        } else {
            $order = Order::with(['customer', 'outlet', 'user', 'orderItems.product', 'payments.paymentMethod'])
                ->where('order_status', 'completed')->latest()->first();
        }

        return ['order' => $order];
    }

    public static function posRefunds(Request $r): array
    {
        $returns = Retur::with(['order.customer', 'order', 'outlet', 'user'])
            ->latest()->take(30)->get();
        $pendingCount = Retur::where('status', 'pending')->count();
        $completedTotal = (float) Retur::where('status', 'completed')->sum('total_amount');

        return compact('returns', 'pendingCount', 'completedTotal');
    }

    public static function posHoldOrders(Request $r): array
    {
        $carts = HeldCart::with(['user', 'outlet', 'customer'])->latest()->get();
        $carts->each(function ($c) {
            $items = is_array($c->items) ? $c->items : [];
            $c->line_total = collect($items)->sum(fn ($i) => ((float) ($i['price'] ?? 0)) * ((int) ($i['qty'] ?? 0)));
            $c->line_count = collect($items)->sum(fn ($i) => (int) ($i['qty'] ?? 0));
        });

        return compact('carts');
    }
}

