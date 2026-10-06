<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Services\AdvancedReportService;
use App\Services\ReportPdfService;
use App\Services\SimpleXlsxService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ReportExportController extends Controller
{
    public function __construct(
        protected ReportPdfService $pdfService,
    ) {}

    public function sales(Request $request): mixed
    {
        $startDate = $request->query('start_date', now()->subDays(30)->format('Y-m-d'));
        $endDate = $request->query('end_date', now()->format('Y-m-d'));
        $outletId = $request->query('outlet_id');
        $format = $request->query('format', 'csv');

        $this->validateOutletAccess($outletId);

        if ($format === 'pdf') {
            return $this->pdfService->generateSalesReport($startDate, $endDate, $outletId ? (int) $outletId : null);
        }

        if ($format === 'xlsx') {
            return $this->exportSalesXlsx($startDate, $endDate, $outletId);
        }

        return $this->exportSalesCsv($startDate, $endDate, $outletId);
    }

    public function financial(Request $request): mixed
    {
        $startDate = $request->query('start_date', now()->subDays(30)->format('Y-m-d'));
        $endDate = $request->query('end_date', now()->format('Y-m-d'));
        $outletId = $request->query('outlet_id');
        $format = $request->query('format', 'csv');

        $this->validateOutletAccess($outletId);

        if ($format === 'pdf') {
            return $this->pdfService->generateFinancialReport($startDate, $endDate, $outletId ? (int) $outletId : null);
        }

        return $this->exportFinancialCsv($startDate, $endDate, $outletId);
    }

    public function stock(Request $request): mixed
    {
        $outletId = $request->query('outlet_id');
        $format = $request->query('format', 'csv');

        $this->validateOutletAccess($outletId);

        if ($format === 'pdf') {
            return $this->pdfService->generateStockReport($outletId ? (int) $outletId : null);
        }

        return $this->exportStockCsv($outletId);
    }

    public function salesPdf(Request $request): mixed
    {
        $startDate = $request->query('start_date', now()->subDays(30)->format('Y-m-d'));
        $endDate = $request->query('end_date', now()->format('Y-m-d'));
        $outletId = $request->query('outlet_id');
        $this->validateOutletAccess($outletId);

        return $this->pdfService->generateSalesReport($startDate, $endDate, $outletId ? (int) $outletId : null);
    }

    public function salesItems(Request $request): mixed
    {
        $startDate = $request->query('start_date', now()->subDays(30)->format('Y-m-d'));
        $endDate = $request->query('end_date', now()->format('Y-m-d'));
        $outletId = $request->query('outlet_id');
        $format = $request->query('format', 'csv');

        $this->validateOutletAccess($outletId);

        if ($format === 'pdf') {
            return $this->pdfService->generateSalesItemsReport($startDate, $endDate, $outletId ? (int) $outletId : null);
        }

        if ($format === 'xlsx') {
            return $this->exportSalesItemsXlsx($startDate, $endDate, $outletId);
        }

        return $this->exportSalesItemsCsv($startDate, $endDate, $outletId);
    }

    public function salesXlsx(Request $request): mixed
    {
        $startDate = $request->query('start_date', now()->subDays(30)->format('Y-m-d'));
        $endDate = $request->query('end_date', now()->format('Y-m-d'));
        $outletId = $request->query('outlet_id');
        $this->validateOutletAccess($outletId);

        return $this->exportSalesXlsx($startDate, $endDate, $outletId);
    }

    public function salesItemsXlsx(Request $request): mixed
    {
        $startDate = $request->query('start_date', now()->subDays(30)->format('Y-m-d'));
        $endDate = $request->query('end_date', now()->format('Y-m-d'));
        $outletId = $request->query('outlet_id');
        $this->validateOutletAccess($outletId);

        return $this->exportSalesItemsXlsx($startDate, $endDate, $outletId);
    }

    public function salesItemsPdf(Request $request): mixed
    {
        $startDate = $request->query('start_date', now()->subDays(30)->format('Y-m-d'));
        $endDate = $request->query('end_date', now()->format('Y-m-d'));
        $outletId = $request->query('outlet_id');
        $this->validateOutletAccess($outletId);

        return $this->pdfService->generateSalesItemsReport($startDate, $endDate, $outletId ? (int) $outletId : null);
    }

    public function financialPdf(Request $request): mixed
    {
        $startDate = $request->query('start_date', now()->subDays(30)->format('Y-m-d'));
        $endDate = $request->query('end_date', now()->format('Y-m-d'));
        $outletId = $request->query('outlet_id');
        $this->validateOutletAccess($outletId);

        return $this->pdfService->generateFinancialReport($startDate, $endDate, $outletId ? (int) $outletId : null);
    }

    public function stockPdf(Request $request): mixed
    {
        $outletId = $request->query('outlet_id');
        $this->validateOutletAccess($outletId);

        return $this->pdfService->generateStockReport($outletId ? (int) $outletId : null);
    }

    protected function validateOutletAccess(?string $outletId): void
    {
        if (! $outletId) {
            return;
        }

        $user = auth()->user();
        if ($user && ! in_array((int) $outletId, $user->getAccessibleOutletIds())) {
            abort(403, 'Anda tidak memiliki akses ke outlet ini.');
        }
    }

    protected function exportSalesCsv(string $startDate, string $endDate, ?string $outletId): mixed
    {
        $orders = Order::with(['user', 'outlet', 'customer'])
            ->whereBetween('created_at', [$startDate, $endDate.' 23:59:59'])
            ->when($outletId, fn ($q) => $q->where('outlet_id', $outletId))
            ->where('order_status', 'completed')
            ->latest()
            ->get();

        $filename = 'laporan-penjualan-'.$startDate.'-sd-'.$endDate.'.csv';

        $headers = [
            'Content-Type' => 'text/csv; charset=utf-8',
            'Content-Disposition' => 'attachment; filename="'.$filename.'"',
        ];

        $handle = fopen('php://temp', 'r+');
        fwrite($handle, "\xEF\xBB\xBF");

        fputcsv($handle, [
            'No. Order', 'Tanggal', 'Outlet', 'Kasir', 'Pelanggan',
            'Subtotal', 'Diskon', 'Pajak', 'Total', 'Status Bayar', 'Status Order',
        ]);

        foreach ($orders as $order) {
            fputcsv($handle, [
                $order->order_number,
                $order->created_at->format('Y-m-d H:i'),
                $order->outlet?->name,
                $order->user?->name,
                $order->customer?->name,
                $order->subtotal,
                $order->discount_amount,
                $order->tax_amount,
                $order->total_amount,
                $order->payment_status,
                $order->order_status,
            ]);
        }

        rewind($handle);
        $content = stream_get_contents($handle);
        fclose($handle);

        return response($content, 200, $headers);
    }

    protected function exportSalesItemsCsv(string $startDate, string $endDate, ?string $outletId): mixed
    {
        $items = OrderItem::with(['order.user', 'order.outlet', 'product', 'productVariant'])
            ->whereHas('order', function ($q) use ($startDate, $endDate, $outletId) {
                $q->whereBetween('created_at', [$startDate, $endDate.' 23:59:59'])
                    ->when($outletId, fn ($qq) => $qq->where('outlet_id', $outletId))
                    ->where('order_status', 'completed');
            })
            ->join('orders', 'order_items.order_id', '=', 'orders.id')
            ->orderBy('orders.created_at')
            ->orderBy('orders.order_number')
            ->select('order_items.*')
            ->get();

        $filename = 'laporan-penjualan-item-'.$startDate.'-sd-'.$endDate.'.csv';

        $headers = [
            'Content-Type' => 'text/csv; charset=utf-8',
            'Content-Disposition' => 'attachment; filename="'.$filename.'"',
        ];

        $handle = fopen('php://temp', 'r+');
        fwrite($handle, "\xEF\xBB\xBF");

        fputcsv($handle, [
            'Tanggal', 'No. Order', 'Outlet', 'Kasir',
            'SKU', 'Nama Produk', 'Varian', 'Qty',
            'Harga Satuan', 'Diskon', 'Subtotal',
        ]);

        foreach ($items as $item) {
            $order = $item->order;
            fputcsv($handle, [
                $order?->created_at?->format('Y-m-d H:i') ?? '-',
                $order?->order_number ?? '-',
                $order?->outlet?->name ?? '-',
                $order?->user?->name ?? '-',
                $item->product?->sku ?? $item->productVariant?->sku ?? '-',
                $item->product?->name ?? '(produk dihapus #'.$item->product_id.')',
                $item->productVariant?->name ?? '-',
                $item->quantity,
                $item->unit_price,
                $item->discount_amount,
                $item->subtotal,
            ]);
        }

        rewind($handle);
        $content = stream_get_contents($handle);
        fclose($handle);

        return response($content, 200, $headers);
    }

    protected function exportSalesXlsx(string $startDate, string $endDate, ?string $outletId): mixed
    {
        $orders = Order::with(['user', 'outlet', 'customer'])
            ->whereBetween('created_at', [$startDate, $endDate.' 23:59:59'])
            ->when($outletId, fn ($q) => $q->where('outlet_id', $outletId))
            ->where('order_status', 'completed')
            ->latest()
            ->get();

        $headers = [
            'No. Order', 'Tanggal', 'Outlet', 'Kasir', 'Pelanggan',
            'Subtotal', 'Diskon', 'Pajak', 'Total', 'Status Bayar', 'Status Order',
        ];

        $rows = $orders->map(fn ($order) => [
            $order->order_number,
            $order->created_at->format('Y-m-d H:i'),
            $order->outlet?->name,
            $order->user?->name,
            $order->customer?->name,
            (float) $order->subtotal,
            (float) $order->discount_amount,
            (float) $order->tax_amount,
            (float) $order->total_amount,
            $order->payment_status,
            $order->order_status,
        ])->toArray();

        return SimpleXlsxService::download(
            'laporan-penjualan-'.$startDate.'-sd-'.$endDate.'.xlsx',
            $headers,
            $rows,
            ['F', 'G', 'H', 'I']
        );
    }

    protected function exportSalesItemsXlsx(string $startDate, string $endDate, ?string $outletId): mixed
    {
        $items = OrderItem::with(['order.user', 'order.outlet', 'product', 'productVariant'])
            ->whereHas('order', function ($q) use ($startDate, $endDate, $outletId) {
                $q->whereBetween('created_at', [$startDate, $endDate.' 23:59:59'])
                    ->when($outletId, fn ($qq) => $qq->where('outlet_id', $outletId))
                    ->where('order_status', 'completed');
            })
            ->join('orders', 'order_items.order_id', '=', 'orders.id')
            ->orderBy('orders.created_at')
            ->orderBy('orders.order_number')
            ->select('order_items.*')
            ->get();

        $headers = [
            'Tanggal', 'No. Order', 'Outlet', 'Kasir',
            'SKU', 'Nama Produk', 'Varian', 'Qty',
            'Harga Satuan', 'Diskon', 'Subtotal',
        ];

        $rows = $items->map(fn ($item) => [
            $item->order?->created_at?->format('Y-m-d H:i') ?? '-',
            $item->order?->order_number ?? '-',
            $item->order?->outlet?->name ?? '-',
            $item->order?->user?->name ?? '-',
            $item->product?->sku ?? $item->productVariant?->sku ?? '-',
            $item->product?->name ?? '(produk dihapus #'.$item->product_id.')',
            $item->productVariant?->name ?? '-',
            (int) $item->quantity,
            (float) $item->unit_price,
            (float) $item->discount_amount,
            (float) $item->subtotal,
        ])->toArray();

        return SimpleXlsxService::download(
            'laporan-penjualan-item-'.$startDate.'-sd-'.$endDate.'.xlsx',
            $headers,
            $rows,
            ['H', 'I', 'J', 'K']
        );
    }

    protected function exportFinancialCsv(string $startDate, string $endDate, ?string $outletId): mixed
    {
        $orders = Order::with(['user', 'outlet', 'payments.paymentMethod'])
            ->whereBetween('created_at', [$startDate, $endDate.' 23:59:59'])
            ->when($outletId, fn ($q) => $q->where('outlet_id', $outletId))
            ->excludeCancelled()
            ->latest()
            ->get();

        $filename = 'laporan-keuangan-'.$startDate.'-sd-'.$endDate.'.csv';

        $headers = [
            'Content-Type' => 'text/csv; charset=utf-8',
            'Content-Disposition' => 'attachment; filename="'.$filename.'"',
        ];

        $handle = fopen('php://temp', 'r+');
        fwrite($handle, "\xEF\xBB\xBF");

        fputcsv($handle, [
            'No. Order', 'Tanggal', 'Outlet', 'Kasir', 'Total', 'Diskon', 'Pajak',
            'Status Bayar', 'Metode Bayar', 'Jumlah Bayar',
        ]);

        foreach ($orders as $order) {
            $methods = $order->payments->map(fn ($p) => ($p->paymentMethod?->name ?? '-').' ('.number_format($p->amount, 0, ',', '.').')')->implode('; ');
            $totalPaid = $order->payments->where('status', 'confirmed')->sum('amount');

            fputcsv($handle, [
                $order->order_number,
                $order->created_at->format('Y-m-d H:i'),
                $order->outlet?->name,
                $order->user?->name,
                $order->total_amount,
                $order->discount_amount,
                $order->tax_amount,
                $order->payment_status,
                $methods ?: '-',
                $totalPaid ?: 0,
            ]);
        }

        rewind($handle);
        $content = stream_get_contents($handle);
        fclose($handle);

        return response($content, 200, $headers);
    }

    public function labaRugi(Request $request): mixed
    {
        $startDate = $request->query('start_date', now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->query('end_date', now()->format('Y-m-d'));
        $outletId = $request->query('outlet_id');

        $this->validateOutletAccess($outletId);

        return $this->exportLabaRugiCsv($startDate, $endDate, $outletId);
    }

    public function neraca(Request $request): mixed
    {
        $asOfDate = $request->query('as_of_date', now()->format('Y-m-d'));
        $outletId = $request->query('outlet_id');

        $this->validateOutletAccess($outletId);

        return $this->exportNeracaCsv($asOfDate, $outletId);
    }

    public function cancelled(Request $request): mixed
    {
        $startDate = $request->query('start_date', now()->subDays(30)->format('Y-m-d'));
        $endDate = $request->query('end_date', now()->format('Y-m-d'));
        $outletId = $request->query('outlet_id');

        $this->validateOutletAccess($outletId);

        $orders = Order::with(['user', 'outlet', 'customer'])
            ->whereBetween('created_at', [$startDate, $endDate.' 23:59:59'])
            ->when($outletId, fn ($q) => $q->where('outlet_id', $outletId))
            ->where('order_status', 'cancelled')
            ->latest()
            ->get();

        $filename = 'laporan-pembatalan-'.$startDate.'-sd-'.$endDate.'.csv';

        $headers = [
            'Content-Type' => 'text/csv; charset=utf-8',
            'Content-Disposition' => 'attachment; filename="'.$filename.'"',
        ];

        $handle = fopen('php://temp', 'r+');
        fwrite($handle, "\xEF\xBB\xBF");

        fputcsv($handle, [
            'No. Order', 'Tanggal', 'Outlet', 'Kasir', 'Pelanggan',
            'Subtotal', 'Diskon', 'Pajak', 'Total', 'Status Bayar', 'Catatan',
        ]);

        foreach ($orders as $order) {
            fputcsv($handle, [
                $order->order_number,
                $order->created_at->format('Y-m-d H:i'),
                $order->outlet?->name,
                $order->user?->name,
                $order->customer?->name,
                $order->subtotal,
                $order->discount_amount,
                $order->tax_amount,
                $order->total_amount,
                $order->payment_status,
                $order->notes ?? $order->order_notes ?? '-',
            ]);
        }

        rewind($handle);
        $content = stream_get_contents($handle);
        fclose($handle);

        return response($content, 200, $headers);
    }

    public function receivables(Request $request): mixed
    {
        $outletId = $request->query('outlet_id');

        $this->validateOutletAccess($outletId);

        $orders = Order::with(['customer', 'outlet', 'user'])
            ->where('order_status', '!=', 'cancelled')
            ->where('remaining_amount', '>', 0)
            ->when($outletId, fn ($q) => $q->where('outlet_id', $outletId))
            ->latest()
            ->get();

        $filename = 'laporan-piutang-'.now()->format('Y-m-d').'.csv';

        $headers = [
            'Content-Type' => 'text/csv; charset=utf-8',
            'Content-Disposition' => 'attachment; filename="'.$filename.'"',
        ];

        $handle = fopen('php://temp', 'r+');
        fwrite($handle, "\xEF\xBB\xBF");

        fputcsv($handle, ['No. Order', 'Pelanggan', 'Outlet', 'Tanggal', 'Total', 'Sisa Piutang', 'Status Bayar']);

        foreach ($orders as $order) {
            fputcsv($handle, [
                $order->order_number,
                $order->customer?->name,
                $order->outlet?->name,
                $order->created_at->format('Y-m-d'),
                $order->total_amount,
                $order->remaining_amount,
                $order->payment_status,
            ]);
        }

        rewind($handle);
        $content = stream_get_contents($handle);
        fclose($handle);

        return response($content, 200, $headers);
    }

    protected function exportLabaRugiCsv(string $startDate, string $endDate, ?string $outletId): mixed
    {
        $items = DB::table('journal_entry_items')
            ->join('accounts', 'journal_entry_items.account_id', '=', 'accounts.id')
            ->join('journal_entries', 'journal_entry_items.journal_entry_id', '=', 'journal_entries.id')
            ->whereBetween('journal_entries.journal_date', [$startDate, $endDate])
            ->where('journal_entries.status', 'posted')
            ->where('accounts.active', true)
            ->whereIn('accounts.type', ['revenue', 'cogs', 'expense'])
            ->where(function ($q) {
                $q->whereNotIn('journal_entries.reference_type', ['order', 'order_cancel'])
                    ->orWhere(function ($inner) {
                        $inner->whereIn('journal_entries.reference_type', ['order', 'order_cancel'])
                            ->whereNotExists(function ($exists) {
                                $exists->select(DB::raw(1))
                                    ->from('orders')
                                    ->whereColumn('orders.id', 'journal_entries.reference_id')
                                    ->where('orders.order_status', 'cancelled');
                            });
                    });
            })
            ->when($outletId, fn ($q) => $this->applyJournalOutletFilter($q, (int) $outletId))
            ->selectRaw('
                accounts.code,
                accounts.name,
                accounts.type,
                COALESCE(SUM(journal_entry_items.debit), 0) as total_debit,
                COALESCE(SUM(journal_entry_items.credit), 0) as total_credit
            ')
            ->groupBy('accounts.id', 'accounts.code', 'accounts.name', 'accounts.type')
            ->orderBy('accounts.code')
            ->get();

        $filename = 'laporan-laba-rugi-'.$startDate.'-sd-'.$endDate.'.csv';

        $headers = [
            'Content-Type' => 'text/csv; charset=utf-8',
            'Content-Disposition' => 'attachment; filename="'.$filename.'"',
        ];

        $handle = fopen('php://temp', 'r+');
        fwrite($handle, "\xEF\xBB\xBF");

        fputcsv($handle, ['LAPORAN LABA RUGI', $startDate.' s/d '.$endDate]);
        fputcsv($handle, []);
        fputcsv($handle, ['Kode', 'Nama Akun', 'Tipe', 'Debit', 'Kredit', 'Saldo']);

        foreach ($items as $item) {
            $typeLabel = match ($item->type) {
                'revenue' => 'Pendapatan',
                'cogs' => 'HPP',
                'expense' => 'Beban',
                default => $item->type,
            };
            $balance = in_array($item->type, ['cogs', 'expense'])
                ? $item->total_debit - $item->total_credit
                : $item->total_credit - $item->total_debit;

            fputcsv($handle, [
                $item->code,
                $item->name,
                $typeLabel,
                number_format($item->total_debit, 0, ',', '.'),
                number_format($item->total_credit, 0, ',', '.'),
                number_format($balance, 0, ',', '.'),
            ]);
        }

        rewind($handle);
        $content = stream_get_contents($handle);
        fclose($handle);

        return response($content, 200, $headers);
    }

    protected function exportNeracaCsv(string $asOfDate, ?string $outletId): mixed
    {
        $items = DB::table('journal_entry_items')
            ->join('accounts', 'journal_entry_items.account_id', '=', 'accounts.id')
            ->join('journal_entries', 'journal_entry_items.journal_entry_id', '=', 'journal_entries.id')
            ->where('journal_entries.journal_date', '<=', $asOfDate)
            ->where('journal_entries.status', 'posted')
            ->where('accounts.active', true)
            ->whereIn('accounts.type', ['asset', 'liability', 'equity'])
            ->where(function ($q) {
                $q->whereNotIn('journal_entries.reference_type', ['order', 'order_cancel'])
                    ->orWhere(function ($inner) {
                        $inner->whereIn('journal_entries.reference_type', ['order', 'order_cancel'])
                            ->whereNotExists(function ($exists) {
                                $exists->select(DB::raw(1))
                                    ->from('orders')
                                    ->whereColumn('orders.id', 'journal_entries.reference_id')
                                    ->where('orders.order_status', 'cancelled');
                            });
                    });
            })
            ->when($outletId, fn ($q) => $this->applyJournalOutletFilter($q, (int) $outletId))
            ->selectRaw('
                accounts.code,
                accounts.name,
                accounts.type,
                COALESCE(SUM(journal_entry_items.debit), 0) as total_debit,
                COALESCE(SUM(journal_entry_items.credit), 0) as total_credit
            ')
            ->groupBy('accounts.id', 'accounts.code', 'accounts.name', 'accounts.type')
            ->orderBy('accounts.code')
            ->get();

        $filename = 'laporan-neraca-'.$asOfDate.'.csv';

        $headers = [
            'Content-Type' => 'text/csv; charset=utf-8',
            'Content-Disposition' => 'attachment; filename="'.$filename.'"',
        ];

        $handle = fopen('php://temp', 'r+');
        fwrite($handle, "\xEF\xBB\xBF");

        fputcsv($handle, ['LAPORAN NERACA', 'Per '.$asOfDate]);
        fputcsv($handle, []);
        fputcsv($handle, ['Kode', 'Nama Akun', 'Tipe', 'Debit', 'Kredit', 'Saldo']);

        foreach ($items as $item) {
            $typeLabel = match ($item->type) {
                'asset' => 'Aset',
                'liability' => 'Liabilitas',
                'equity' => 'Ekuitas',
                default => $item->type,
            };
            $balance = in_array($item->type, ['asset'])
                ? $item->total_debit - $item->total_credit
                : $item->total_credit - $item->total_debit;

            fputcsv($handle, [
                $item->code,
                $item->name,
                $typeLabel,
                number_format($item->total_debit, 0, ',', '.'),
                number_format($item->total_credit, 0, ',', '.'),
                number_format($balance, 0, ',', '.'),
            ]);
        }

        rewind($handle);
        $content = stream_get_contents($handle);
        fclose($handle);

        return response($content, 200, $headers);
    }

    protected function applyJournalOutletFilter($query, int $outletId): void
    {
        $query->where(function ($q) use ($outletId) {
            $q->where(function ($sub) use ($outletId) {
                $sub->whereIn('journal_entries.reference_type', ['order', 'order_cancel'])
                    ->whereIn('journal_entries.reference_id', function ($inner) use ($outletId) {
                        $inner->select('id')->from('orders')->where('outlet_id', $outletId);
                    });
            })->when(Schema::hasTable('expenses'), function ($q) use ($outletId) {
                $q->orWhere(function ($sub) use ($outletId) {
                    $sub->where('journal_entries.reference_type', 'expense')
                        ->whereIn('journal_entries.reference_id', function ($inner) use ($outletId) {
                            $inner->select('id')->from('expenses')->where('outlet_id', $outletId);
                        });
                });
            })->orWhere(function ($sub) use ($outletId) {
                $sub->where('journal_entries.reference_type', 'purchase_order')
                    ->whereIn('journal_entries.reference_id', function ($inner) use ($outletId) {
                        $inner->select('id')->from('purchase_orders')->where('outlet_id', $outletId);
                    });
            });
        });
    }

    // ================= LAPORAN BARU (luas) =================

    protected function csvResponse(string $filename, array $headers, array $rows): mixed
    {
        $handle = fopen('php://temp', 'r+');
        fwrite($handle, "\xEF\xBB\xBF");
        fputcsv($handle, $headers);
        foreach ($rows as $r) {
            fputcsv($handle, array_values($r));
        }
        rewind($handle);
        $content = stream_get_contents($handle);
        fclose($handle);

        return response($content, 200, [
            'Content-Type' => 'text/csv; charset=utf-8',
            'Content-Disposition' => 'attachment; filename="'.$filename.'"',
        ]);
    }

    protected function outletIdOrNull(?string $outletId): ?int
    {
        return $outletId ? (int) $outletId : null;
    }

    public function profit(Request $request): mixed
    {
        $start = $request->query('start_date', now()->subDays(30)->format('Y-m-d'));
        $end = $request->query('end_date', now()->format('Y-m-d'));
        $outletId = $this->outletIdOrNull($request->query('outlet_id'));
        $this->validateOutletAccess($request->query('outlet_id'));
        $format = $request->query('format', 'csv');

        $headers = AdvancedReportService::profitHeaders();
        $rows = AdvancedReportService::profitRows($start, $end, $outletId);

        if ($format === 'xlsx' || $request->route()->getName() === 'export.profit.xlsx') {
            return SimpleXlsxService::download("laporan-profit-{$start}-sd-{$end}.xlsx", $headers, $rows, AdvancedReportService::profitNumericColumns());
        }

        return $this->csvResponse("laporan-profit-{$start}-sd-{$end}.csv", $headers, $rows);
    }

    public function stockSlow(Request $request): mixed
    {
        $outletId = $this->outletIdOrNull($request->query('outlet_id'));
        $this->validateOutletAccess($request->query('outlet_id'));
        $format = $request->query('format', 'csv');

        $headers = AdvancedReportService::slowHeaders();
        $rows = AdvancedReportService::slowRows($outletId);

        if ($format === 'xlsx') {
            return SimpleXlsxService::download('laporan-stok-lambat-'.now()->format('Y-m-d').'.xlsx', $headers, $rows, AdvancedReportService::slowNumericColumns());
        }

        return $this->csvResponse('laporan-stok-lambat-'.now()->format('Y-m-d').'.csv', $headers, $rows);
    }

    public function cashflow(Request $request): mixed
    {
        $start = $request->query('start_date', now()->subDays(30)->format('Y-m-d'));
        $end = $request->query('end_date', now()->format('Y-m-d'));
        $outletId = $this->outletIdOrNull($request->query('outlet_id'));
        $this->validateOutletAccess($request->query('outlet_id'));

        $headers = AdvancedReportService::cashflowHeaders();
        $rows = AdvancedReportService::cashflowRows($start, $end, $outletId);

        if ($request->query('format') === 'xlsx') {
            return SimpleXlsxService::download("laporan-arus-kas-{$start}-sd-{$end}.xlsx", $headers, $rows, AdvancedReportService::cashflowNumericColumns());
        }

        return $this->csvResponse("laporan-arus-kas-{$start}-sd-{$end}.csv", $headers, $rows);
    }

    public function payables(Request $request): mixed
    {
        $outletId = $this->outletIdOrNull($request->query('outlet_id'));
        $this->validateOutletAccess($request->query('outlet_id'));

        $headers = AdvancedReportService::payableHeaders();
        $rows = AdvancedReportService::payableRows($outletId);

        if ($request->query('format') === 'xlsx') {
            return SimpleXlsxService::download('laporan-hutang-supplier-'.now()->format('Y-m-d').'.xlsx', $headers, $rows, AdvancedReportService::payableNumericColumns());
        }

        return $this->csvResponse('laporan-hutang-supplier-'.now()->format('Y-m-d').'.csv', $headers, $rows);
    }

    public function tax(Request $request): mixed
    {
        $start = $request->query('start_date', now()->startOfMonth()->format('Y-m-d'));
        $end = $request->query('end_date', now()->format('Y-m-d'));
        $outletId = $this->outletIdOrNull($request->query('outlet_id'));
        $this->validateOutletAccess($request->query('outlet_id'));

        $headers = AdvancedReportService::taxHeaders();
        $rows = AdvancedReportService::taxRows($start, $end, $outletId);

        if ($request->query('format') === 'xlsx') {
            return SimpleXlsxService::download("laporan-pajak-{$start}-sd-{$end}.xlsx", $headers, $rows, AdvancedReportService::taxNumericColumns());
        }

        return $this->csvResponse("laporan-pajak-{$start}-sd-{$end}.csv", $headers, $rows);
    }

    public function customers(Request $request): mixed
    {
        $start = $request->query('start_date', now()->subDays(90)->format('Y-m-d'));
        $end = $request->query('end_date', now()->format('Y-m-d'));
        $outletId = $this->outletIdOrNull($request->query('outlet_id'));
        $this->validateOutletAccess($request->query('outlet_id'));

        $headers = AdvancedReportService::rfmHeaders();
        $rows = AdvancedReportService::rfmRows($start, $end, $outletId);

        if ($request->query('format') === 'xlsx') {
            return SimpleXlsxService::download("laporan-pelanggan-rfm-{$start}-sd-{$end}.xlsx", $headers, $rows, AdvancedReportService::rfmNumericColumns());
        }

        return $this->csvResponse("laporan-pelanggan-rfm-{$start}-sd-{$end}.csv", $headers, $rows);
    }

    public function promo(Request $request): mixed
    {
        $start = $request->query('start_date', now()->subDays(30)->format('Y-m-d'));
        $end = $request->query('end_date', now()->format('Y-m-d'));
        $outletId = $this->outletIdOrNull($request->query('outlet_id'));
        $this->validateOutletAccess($request->query('outlet_id'));

        $headers = AdvancedReportService::promoHeaders();
        $rows = AdvancedReportService::promoRows($start, $end, $outletId);

        if ($request->query('format') === 'xlsx') {
            return SimpleXlsxService::download("laporan-efektivitas-diskon-{$start}-sd-{$end}.xlsx", $headers, $rows, AdvancedReportService::promoNumericColumns());
        }

        return $this->csvResponse("laporan-efektivitas-diskon-{$start}-sd-{$end}.csv", $headers, $rows);
    }

    /** Satu file multi-sheet: Ringkasan + Profit + Arus Kas + Hutang + RFM + Diskon. */
    public function comprehensiveXlsx(Request $request): mixed
    {
        $start = $request->query('start_date', now()->subDays(30)->format('Y-m-d'));
        $end = $request->query('end_date', now()->format('Y-m-d'));
        $outletId = $this->outletIdOrNull($request->query('outlet_id'));
        $this->validateOutletAccess($request->query('outlet_id'));

        $summary = AdvancedReportService::dailySummary($outletId, $end);
        $summaryRows = [
            ['Tanggal', $summary['date']],
            ['Transaksi', $summary['trx']],
            ['Omzet', $summary['omzet']],
            ['Diskon', $summary['diskon']],
            ['Hutang jatuh tempo', $summary['overduePayables']],
            ['Stok menipis', $summary['lowStock']],
        ];

        return SimpleXlsxService::downloadMulti("laporan-komprehensif-{$start}-sd-{$end}.xlsx", [
            ['name' => 'Ringkasan', 'headers' => ['Metrik', 'Nilai'], 'rows' => $summaryRows, 'numericColumns' => ['B']],
            ['name' => 'Profit Produk', 'headers' => AdvancedReportService::profitHeaders(), 'rows' => AdvancedReportService::profitRows($start, $end, $outletId), 'numericColumns' => AdvancedReportService::profitNumericColumns()],
            ['name' => 'Arus Kas', 'headers' => AdvancedReportService::cashflowHeaders(), 'rows' => AdvancedReportService::cashflowRows($start, $end, $outletId), 'numericColumns' => AdvancedReportService::cashflowNumericColumns()],
            ['name' => 'Hutang Supplier', 'headers' => AdvancedReportService::payableHeaders(), 'rows' => AdvancedReportService::payableRows($outletId), 'numericColumns' => AdvancedReportService::payableNumericColumns()],
            ['name' => 'Pelanggan RFM', 'headers' => AdvancedReportService::rfmHeaders(), 'rows' => AdvancedReportService::rfmRows($start, $end, $outletId), 'numericColumns' => AdvancedReportService::rfmNumericColumns()],
            ['name' => 'Efek Diskon', 'headers' => AdvancedReportService::promoHeaders(), 'rows' => AdvancedReportService::promoRows($start, $end, $outletId), 'numericColumns' => AdvancedReportService::promoNumericColumns()],
        ]);
    }

    protected function exportStockCsv(?string $outletId): mixed
    {
        $products = Product::with(['category', 'brand', 'unit', 'outlet'])
            ->when($outletId, fn ($q) => $q->where('outlet_id', $outletId))
            ->where('active', true)
            ->orderBy('current_stock')
            ->get();

        $filename = 'laporan-stok-'.now()->format('Y-m-d').'.csv';

        $headers = [
            'Content-Type' => 'text/csv; charset=utf-8',
            'Content-Disposition' => 'attachment; filename="'.$filename.'"',
        ];

        $handle = fopen('php://temp', 'r+');
        fwrite($handle, "\xEF\xBB\xBF");

        fputcsv($handle, [
            'SKU', 'Barcode', 'Nama Produk', 'Kategori', 'Brand', 'Satuan', 'Outlet',
            'Stok', 'Min Stok', 'Max Stok', 'Harga Beli', 'Harga Jual', 'Nilai Stok', 'Status',
        ]);

        foreach ($products as $product) {
            $status = 'Normal';
            if ($product->min_stock > 0 && $product->current_stock <= $product->min_stock) {
                $status = 'Menipis';
            }
            if ($product->max_stock > 0 && $product->current_stock > $product->max_stock) {
                $status = 'Berlebih';
            }

            fputcsv($handle, [
                $product->sku,
                $product->barcode,
                $product->name,
                $product->category?->name,
                $product->brand?->name,
                $product->unit?->name,
                $product->outlet?->name,
                $product->current_stock,
                $product->min_stock,
                $product->max_stock,
                $product->cost_price,
                $product->selling_price,
                $product->current_stock * $product->cost_price,
                $status,
            ]);
        }

        rewind($handle);
        $content = stream_get_contents($handle);
        fclose($handle);

        return response($content, 200, $headers);
    }
}
