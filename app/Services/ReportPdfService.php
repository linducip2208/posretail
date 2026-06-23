<?php

namespace App\Services;

use App\Models\Order;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Response;

class ReportPdfService
{
    public function generateSalesReport(string $startDate, string $endDate, ?int $outletId = null): Response
    {
        $orders = Order::with(['user', 'outlet', 'customer', 'orderItems.product'])
            ->whereBetween('created_at', [$startDate, $endDate . ' 23:59:59'])
            ->when($outletId, fn ($q) => $q->where('outlet_id', $outletId))
            ->where('order_status', 'completed')
            ->latest()
            ->get();

        $totalRevenue = $orders->sum('total_amount');
        $totalOrders = $orders->count();
        $avgOrder = $totalOrders > 0 ? $totalRevenue / $totalOrders : 0;
        $totalDiscount = $orders->sum('discount_amount');
        $totalTax = $orders->sum('tax_amount');

        $data = compact(
            'orders', 'totalRevenue', 'totalOrders', 'avgOrder',
            'totalDiscount', 'totalTax', 'startDate', 'endDate'
        );

        $pdf = Pdf::loadView('pdf.laporan-penjualan', $data)
            ->setPaper('a4', 'landscape');

        return $pdf->download('laporan-penjualan-' . $startDate . '-sd-' . $endDate . '.pdf');
    }

    public function generateFinancialReport(string $startDate, string $endDate, ?int $outletId = null): Response
    {
        $orders = Order::with(['user', 'outlet', 'payments'])
            ->whereBetween('created_at', [$startDate, $endDate . ' 23:59:59'])
            ->when($outletId, fn ($q) => $q->where('outlet_id', $outletId))
            ->latest()
            ->get();

        $paidRevenue = $orders->where('payment_status', 'paid')->sum('total_amount');
        $pendingRevenue = $orders->where('payment_status', 'pending')->sum('total_amount');
        $partialRevenue = $orders->where('payment_status', 'partial')->sum('total_amount');

        $totalRevenue = $orders->sum('total_amount');
        $totalOrders = $orders->count();
        $totalDiscount = $orders->sum('discount_amount');
        $totalTax = $orders->sum('tax_amount');

        $paymentMethods = [];
        foreach ($orders as $order) {
            foreach ($order->payments as $payment) {
                $method = $payment->paymentMethod?->name ?? 'Unknown';
                $paymentMethods[$method] = ($paymentMethods[$method] ?? 0) + $payment->amount;
            }
        }

        $data = compact(
            'orders', 'paidRevenue', 'pendingRevenue', 'partialRevenue',
            'totalRevenue', 'totalOrders', 'totalDiscount', 'totalTax',
            'paymentMethods', 'startDate', 'endDate'
        );

        $pdf = Pdf::loadView('pdf.laporan-keuangan', $data)
            ->setPaper('a4', 'landscape');

        return $pdf->download('laporan-keuangan-' . $startDate . '-sd-' . $endDate . '.pdf');
    }

    public function generateStockReport(?int $outletId = null): Response
    {
        $products = \App\Models\Product::with(['category', 'outlet'])
            ->when($outletId, fn ($q) => $q->where('outlet_id', $outletId))
            ->where('active', true)
            ->orderBy('current_stock')
            ->get();

        $lowStock = $products->filter(fn ($p) => $p->current_stock <= $p->min_stock);
        $overStock = $products->filter(fn ($p) => $p->current_stock > $p->max_stock && $p->max_stock > 0);
        $normal = $products->filter(fn ($p) => $p->current_stock > $p->min_stock && ($p->current_stock <= $p->max_stock || $p->max_stock == 0));

        $totalInventoryValue = $products->sum(fn ($p) => $p->current_stock * $p->cost_price);

        $data = compact(
            'products', 'lowStock', 'overStock', 'normal', 'totalInventoryValue'
        );

        $pdf = Pdf::loadView('pdf.laporan-stok', $data)
            ->setPaper('a4', 'landscape');

        return $pdf->download('laporan-stok-' . now()->format('Y-m-d') . '.pdf');
    }
}
