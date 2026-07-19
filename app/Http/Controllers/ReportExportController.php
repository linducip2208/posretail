<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Product;
use App\Models\StockMovement;
use App\Services\ReportPdfService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

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
        if (!$outletId) return;

        $user = auth()->user();
        if ($user && !in_array((int) $outletId, $user->getAccessibleOutletIds())) {
            abort(403, 'Anda tidak memiliki akses ke outlet ini.');
        }
    }

    protected function exportSalesCsv(string $startDate, string $endDate, ?string $outletId): mixed
    {
        $orders = Order::with(['user', 'outlet', 'customer'])
            ->whereBetween('created_at', [$startDate, $endDate . ' 23:59:59'])
            ->when($outletId, fn ($q) => $q->where('outlet_id', $outletId))
            ->where('order_status', 'completed')
            ->latest()
            ->get();

        $filename = 'laporan-penjualan-' . $startDate . '-sd-' . $endDate . '.csv';

        $headers = [
            'Content-Type' => 'text/csv; charset=utf-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
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

    protected function exportFinancialCsv(string $startDate, string $endDate, ?string $outletId): mixed
    {
        $orders = Order::with(['user', 'outlet', 'payments.paymentMethod'])
            ->whereBetween('created_at', [$startDate, $endDate . ' 23:59:59'])
            ->when($outletId, fn ($q) => $q->where('outlet_id', $outletId))
            ->latest()
            ->get();

        $filename = 'laporan-keuangan-' . $startDate . '-sd-' . $endDate . '.csv';

        $headers = [
            'Content-Type' => 'text/csv; charset=utf-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        $handle = fopen('php://temp', 'r+');
        fwrite($handle, "\xEF\xBB\xBF");

        fputcsv($handle, [
            'No. Order', 'Tanggal', 'Outlet', 'Kasir', 'Total', 'Diskon', 'Pajak',
            'Status Bayar', 'Metode Bayar', 'Jumlah Bayar',
        ]);

        foreach ($orders as $order) {
            $methods = $order->payments->map(fn ($p) => ($p->paymentMethod?->name ?? '-') . ' (' . number_format($p->amount, 0, ',', '.') . ')')->implode('; ');
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

    protected function exportStockCsv(?string $outletId): mixed
    {
        $products = Product::with(['category', 'brand', 'unit', 'outlet'])
            ->when($outletId, fn ($q) => $q->where('outlet_id', $outletId))
            ->where('active', true)
            ->orderBy('current_stock')
            ->get();

        $filename = 'laporan-stok-' . now()->format('Y-m-d') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv; charset=utf-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
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
