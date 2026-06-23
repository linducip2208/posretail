<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Stok</title>
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
        .section-title { font-size: 13px; font-weight: bold; color: #1e293b; margin: 16px 0 8px 0; padding-bottom: 4px; border-bottom: 2px solid #4f46e5; }
        .danger { color: #dc2626; font-weight: bold; }
        .warning { color: #d97706; font-weight: bold; }
        .success { color: #059669; }
    </style>
</head>
<body>
    <div class="header">
        <h1>LAPORAN STOK</h1>
        <p>Tanggal: {{ now()->format('d M Y') }}</p>
    </div>

    <div class="summary">
        <div class="summary-card">
            <div class="label">Total Produk</div>
            <div class="value">{{ $products->count() }}</div>
        </div>
        <div class="summary-card">
            <div class="label">Stok Rendah</div>
            <div class="value" style="color: #dc2626;">{{ $lowStock->count() }}</div>
        </div>
        <div class="summary-card">
            <div class="label">Overstock</div>
            <div class="value" style="color: #d97706;">{{ $overStock->count() }}</div>
        </div>
        <div class="summary-card">
            <div class="label">Nilai Inventori</div>
            <div class="value">Rp {{ number_format($totalInventoryValue, 0, ',', '.') }}</div>
        </div>
    </div>

    @if($lowStock->count() > 0)
    <div class="section-title">Produk Stok Rendah ({{ $lowStock->count() }})</div>
    <table>
        <thead>
            <tr>
                <th>SKU</th>
                <th>Produk</th>
                <th>Kategori</th>
                <th class="text-right">Stok</th>
                <th class="text-right">Min</th>
                <th class="text-right">Harga Beli</th>
                <th class="text-right">Harga Jual</th>
            </tr>
        </thead>
        <tbody>
            @foreach($lowStock as $p)
            <tr>
                <td>{{ $p->sku }}</td>
                <td class="danger">{{ $p->name }}</td>
                <td>{{ $p->category->name ?? '-' }}</td>
                <td class="text-right danger">{{ $p->current_stock }}</td>
                <td class="text-right">{{ $p->min_stock }}</td>
                <td class="text-right">Rp {{ number_format($p->cost_price, 0, ',', '.') }}</td>
                <td class="text-right">Rp {{ number_format($p->selling_price, 0, ',', '.') }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @endif

    <div class="section-title">Semua Produk ({{ $products->count() }})</div>
    <table>
        <thead>
            <tr>
                <th>SKU</th>
                <th>Produk</th>
                <th>Kategori</th>
                <th>Brand</th>
                <th class="text-right">Stok</th>
                <th class="text-right">Min</th>
                <th class="text-right">Max</th>
                <th class="text-right">Harga Beli</th>
                <th class="text-right">Harga Jual</th>
                <th class="text-right">Nilai Stok</th>
            </tr>
        </thead>
        <tbody>
            @forelse($products as $p)
            <tr>
                <td>{{ $p->sku }}</td>
                <td>{{ $p->name }}</td>
                <td>{{ $p->category->name ?? '-' }}</td>
                <td>{{ $p->brand->name ?? '-' }}</td>
                <td class="text-right {{ $p->current_stock <= $p->min_stock ? 'danger' : '' }}">{{ $p->current_stock }}</td>
                <td class="text-right">{{ $p->min_stock }}</td>
                <td class="text-right">{{ $p->max_stock }}</td>
                <td class="text-right">Rp {{ number_format($p->cost_price, 0, ',', '.') }}</td>
                <td class="text-right">Rp {{ number_format($p->selling_price, 0, ',', '.') }}</td>
                <td class="text-right">Rp {{ number_format($p->current_stock * $p->cost_price, 0, ',', '.') }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="10" style="text-align: center; padding: 20px;">Tidak ada data produk.</td>
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
