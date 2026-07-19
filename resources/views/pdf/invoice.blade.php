<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Invoice {{ $order->order_number }}</title>
    <style>
        body { font-family: sans-serif; font-size: 12px; color: #1e293b; }
        table { width: 100%; border-collapse: collapse; }
        table th, table td { border: 1px solid #cbd5e1; padding: 6px 8px; text-align: left; }
        table th { background-color: #f1f5f9; font-weight: bold; }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .header { margin-bottom: 20px; }
        .header h1 { font-size: 20px; margin: 0; }
        .header p { margin: 2px 0; color: #64748b; }
        .info { margin-bottom: 20px; }
        .info table { border: none; }
        .info td { border: none; padding: 2px 12px 2px 0; }
        .summary { margin-top: 16px; }
        .summary td { border: none; }
        .footer { margin-top: 30px; text-align: center; color: #94a3b8; font-size: 10px; }
        .total-row { font-weight: bold; font-size: 14px; }
    </style>
</head>
<body>
    <div class="header">
        <h1>INVOICE</h1>
        <p>{{ $order->order_number }}</p>
    </div>

    <div class="info">
        <table>
            <tr><td><strong>Outlet:</strong></td><td>{{ $order->outlet?->name ?? '-' }}</td></tr>
            <tr><td><strong>Tanggal:</strong></td><td>{{ $order->created_at->format('d M Y H:i') }}</td></tr>
            <tr><td><strong>Pelanggan:</strong></td><td>{{ $order->customer?->name ?? 'Walk-in' }}</td></tr>
            <tr><td><strong>Kasir:</strong></td><td>{{ $order->user?->name ?? '-' }}</td></tr>
        </table>
    </div>

    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Produk</th>
                <th class="text-center">Qty</th>
                <th class="text-right">Harga</th>
                <th class="text-right">Subtotal</th>
            </tr>
        </thead>
        <tbody>
            @foreach($order->orderItems as $i => $item)
            <tr>
                <td>{{ $i + 1 }}</td>
                <td>{{ $item->product?->name ?? $item->productVariant?->name ?? '-' }}</td>
                <td class="text-center">{{ $item->quantity }}</td>
                <td class="text-right">{{ number_format($item->unit_price, 0, ',', '.') }}</td>
                <td class="text-right">{{ number_format($item->subtotal, 0, ',', '.') }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <table class="summary">
        <tr><td class="text-right"><strong>Subtotal</strong></td><td class="text-right" width="120">{{ number_format($order->subtotal, 0, ',', '.') }}</td></tr>
        @if($order->discount_amount > 0)
        <tr><td class="text-right"><strong>Diskon</strong></td><td class="text-right">-{{ number_format($order->discount_amount, 0, ',', '.') }}</td></tr>
        @endif
        @if($order->tax_amount > 0)
        <tr><td class="text-right"><strong>Pajak</strong></td><td class="text-right">{{ number_format($order->tax_amount, 0, ',', '.') }}</td></tr>
        @endif
        <tr class="total-row"><td class="text-right"><strong>TOTAL</strong></td><td class="text-right">{{ number_format($order->total_amount, 0, ',', '.') }}</td></tr>
        <tr><td class="text-right"><strong>Status</strong></td><td class="text-right">{{ $order->payment_status }}</td></tr>
    </table>

    <div class="footer">
        <p>Terima kasih telah berbelanja — {{ config('app.name') }}</p>
    </div>
</body>
</html>
