<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Penjualan Item</title>
    <style>
        body { font-family: sans-serif; font-size: 11px; margin: 20px; }
        .header { text-align: center; margin-bottom: 20px; }
        .header h1 { font-size: 18px; margin: 0 0 5px 0; color: #1e293b; }
        .header p { font-size: 11px; color: #64748b; margin: 0; }
        .summary { display: flex; gap: 12px; margin-bottom: 20px; flex-wrap: wrap; }
        .summary-card { border: 1px solid #e2e8f0; border-radius: 8px; padding: 10px 16px; flex: 1; min-width: 130px; }
        .summary-card .label { font-size: 9px; color: #64748b; text-transform: uppercase; letter-spacing: 0.5px; }
        .summary-card .value { font-size: 16px; font-weight: bold; color: #1e293b; margin-top: 2px; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        th { background: #f1f5f9; color: #475569; font-size: 9px; text-transform: uppercase; letter-spacing: 0.5px; padding: 8px 10px; text-align: left; border-bottom: 2px solid #e2e8f0; }
        td { padding: 6px 10px; border-bottom: 1px solid #f1f5f9; font-size: 10px; color: #334155; }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .footer { font-size: 8px; color: #94a3b8; text-align: center; margin-top: 30px; border-top: 1px solid #e2e8f0; padding-top: 10px; }
    </style>
</head>
<body>
    <div class="header">
        <h1>LAPORAN PENJUALAN ITEM</h1>
        <p>Periode: {{ \Carbon\Carbon::parse($startDate)->format('d M Y') }} - {{ \Carbon\Carbon::parse($endDate)->format('d M Y') }}</p>
    </div>

    <div class="summary">
        <div class="summary-card">
            <div class="label">Total Qty Terjual</div>
            <div class="value">{{ number_format($totalQty, 0, ',', '.') }}</div>
        </div>
        <div class="summary-card">
            <div class="label">Total Omset Item</div>
            <div class="value">Rp {{ number_format($totalRevenue, 0, ',', '.') }}</div>
        </div>
        <div class="summary-card">
            <div class="label">Total Diskon Item</div>
            <div class="value">Rp {{ number_format($totalDiscount, 0, ',', '.') }}</div>
        </div>
        <div class="summary-card">
            <div class="label">Total Baris</div>
            <div class="value">{{ $items->count() }}</div>
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th>Tanggal</th>
                <th>No. Order</th>
                <th>Outlet</th>
                <th>Kasir</th>
                <th>SKU</th>
                <th>Nama Produk</th>
                <th>Varian</th>
                <th class="text-right">Qty</th>
                <th class="text-right">Harga</th>
                <th class="text-right">Diskon</th>
                <th class="text-right">Subtotal</th>
            </tr>
        </thead>
        <tbody>
            @forelse($items as $item)
            <tr>
                <td>{{ $item->order?->created_at?->format('d M Y H:i') ?? '-' }}</td>
                <td>{{ $item->order?->order_number ?? '-' }}</td>
                <td>{{ $item->order?->outlet?->name ?? '-' }}</td>
                <td>{{ $item->order?->user?->name ?? '-' }}</td>
                <td>{{ $item->product?->sku ?? $item->productVariant?->sku ?? '-' }}</td>
                <td>{{ $item->product?->name ?? '(produk dihapus #'.$item->product_id.')' }}</td>
                <td>{{ $item->productVariant?->name ?? '-' }}</td>
                <td class="text-right">{{ number_format($item->quantity, 0, ',', '.') }}</td>
                <td class="text-right">Rp {{ number_format($item->unit_price, 0, ',', '.') }}</td>
                <td class="text-right">Rp {{ number_format($item->discount_amount, 0, ',', '.') }}</td>
                <td class="text-right">Rp {{ number_format($item->subtotal, 0, ',', '.') }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="11" style="text-align: center; padding: 20px;">Tidak ada item terjual pada periode ini.</td>
            </tr>
            @endforelse
        </tbody>
    </table>

    <div class="footer">
        <p>Laporan digenerate pada {{ now()->format('d M Y H:i:s') }} oleh {{ auth()->user()?->name ?? 'System' }}</p>
        <p>POS Retail &copy; {{ date('Y') }}</p>
    </div>
</body>
</html>
