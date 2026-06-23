<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Keuangan</title>
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
    </style>
</head>
<body>
    <div class="header">
        <h1>LAPORAN KEUANGAN</h1>
        <p>Periode: {{ \Carbon\Carbon::parse($startDate)->format('d M Y') }} - {{ \Carbon\Carbon::parse($endDate)->format('d M Y') }}</p>
    </div>

    <div class="summary">
        <div class="summary-card">
            <div class="label">Total Pendapatan</div>
            <div class="value">Rp {{ number_format($totalRevenue, 0, ',', '.') }}</div>
        </div>
        <div class="summary-card">
            <div class="label">Sudah Dibayar</div>
            <div class="value" style="color: #059669;">Rp {{ number_format($paidRevenue, 0, ',', '.') }}</div>
        </div>
        <div class="summary-card">
            <div class="label">Belum Dibayar</div>
            <div class="value" style="color: #d97706;">Rp {{ number_format($pendingRevenue, 0, ',', '.') }}</div>
        </div>
        <div class="summary-card">
            <div class="label">DP / Partial</div>
            <div class="value" style="color: #6366f1;">Rp {{ number_format($partialRevenue, 0, ',', '.') }}</div>
        </div>
        <div class="summary-card">
            <div class="label">Total Pesanan</div>
            <div class="value">{{ $totalOrders }}</div>
        </div>
    </div>

    @if(count($paymentMethods) > 0)
    <div class="section-title">Rincian per Metode Pembayaran</div>
    <table>
        <thead>
            <tr>
                <th>Metode Pembayaran</th>
                <th class="text-right">Total</th>
            </tr>
        </thead>
        <tbody>
            @foreach($paymentMethods as $method => $amount)
            <tr>
                <td>{{ $method }}</td>
                <td class="text-right">Rp {{ number_format($amount, 0, ',', '.') }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @endif

    <div class="section-title">Daftar Transaksi</div>
    <table>
        <thead>
            <tr>
                <th>No. Order</th>
                <th>Tanggal</th>
                <th>Outlet</th>
                <th>Pelanggan</th>
                <th class="text-right">Total</th>
                <th class="text-right">Diskon</th>
                <th class="text-right">Pajak</th>
                <th class="text-center">Status Bayar</th>
            </tr>
        </thead>
        <tbody>
            @forelse($orders as $order)
            <tr>
                <td>{{ $order->order_number }}</td>
                <td>{{ $order->created_at->format('d M Y H:i') }}</td>
                <td>{{ $order->outlet->name ?? '-' }}</td>
                <td>{{ $order->customer->name ?? 'Walk-in' }}</td>
                <td class="text-right">Rp {{ number_format($order->total_amount, 0, ',', '.') }}</td>
                <td class="text-right">Rp {{ number_format($order->discount_amount, 0, ',', '.') }}</td>
                <td class="text-right">Rp {{ number_format($order->tax_amount, 0, ',', '.') }}</td>
                <td class="text-center">{{ $order->payment_status }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="8" style="text-align: center; padding: 20px;">Tidak ada data pada periode ini.</td>
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
