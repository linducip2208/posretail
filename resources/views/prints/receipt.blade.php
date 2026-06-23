<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Struk #{{ $order['order_number'] }}</title>
    <style>
        @page { margin: 0; size: 80mm 200mm; }
        body {
            font-family: 'Courier New', monospace;
            font-size: 12px;
            line-height: 1.4;
            width: 72mm;
            margin: 4mm auto;
            padding: 0;
            color: #000;
        }
        .center { text-align: center; }
        .bold { font-weight: bold; }
        .right { text-align: right; }
        hr { border: none; border-top: 1px dashed #000; margin: 4px 0; }
        table { width: 100%; border-collapse: collapse; }
        td { padding: 2px 0; vertical-align: top; }
        .line { display: flex; justify-content: space-between; }
        .text-sm { font-size: 10px; }
    </style>
</head>
<body>
    <div class="center bold">POS RETAIL</div>
    <div class="center text-sm">{{ $outlet ?? 'Outlet' }}</div>
    <hr>

    <div class="line">
        <span>No: {{ $order['order_number'] }}</span>
        <span>{{ \Carbon\Carbon::parse($order['created_at'])->format('d/m/y H:i') }}</span>
    </div>
    <div>Kasir: {{ $cashier ?? '-' }}</div>
    @if(!empty($order['customer']))
    <div>Cust: {{ $order['customer']['name'] ?? '-' }}</div>
    @endif
    <hr>

    <table>
        <tr class="text-sm bold">
            <td>Item</td>
            <td class="right">Qty</td>
            <td class="right">Harga</td>
            <td class="right">Subtotal</td>
        </tr>
        @foreach($order['items'] as $item)
        <tr>
            <td>{{ Str::limit($item['product']['name'] ?? 'Item', 16) }}</td>
            <td class="right">{{ $item['quantity'] }}</td>
            <td class="right">{{ number_format($item['unit_price'], 0, ',', '.') }}</td>
            <td class="right">{{ number_format($item['subtotal'] ?? ($item['quantity'] * $item['unit_price']), 0, ',', '.') }}</td>
        </tr>
        @endforeach
    </table>
    <hr>

    <div class="line">
        <span>Subtotal</span>
        <span>Rp {{ number_format($order['subtotal'] ?? 0, 0, ',', '.') }}</span>
    </div>
    @if(($order['discount_amount'] ?? 0) > 0)
    <div class="line">
        <span>Diskon</span>
        <span>Rp {{ number_format($order['discount_amount'], 0, ',', '.') }}</span>
    </div>
    @endif
    @if(($order['tax_amount'] ?? 0) > 0)
    <div class="line">
        <span>Pajak</span>
        <span>Rp {{ number_format($order['tax_amount'], 0, ',', '.') }}</span>
    </div>
    @endif

    <div class="line bold" style="font-size: 14px; margin-top: 4px;">
        <span>TOTAL</span>
        <span>Rp {{ number_format($order['total_amount'] ?? 0, 0, ',', '.') }}</span>
    </div>
    <hr>

    @php
        $payments = $order['payments'] ?? [];
        $totalPaid = collect($payments)->sum('amount');
        $change = $totalPaid - ($order['total_amount'] ?? 0);
    @endphp

    <div class="line">
        <span>Dibayar</span>
        <span>Rp {{ number_format($totalPaid, 0, ',', '.') }}</span>
    </div>
    @if($change > 0)
    <div class="line">
        <span>Kembali</span>
        <span>Rp {{ number_format($change, 0, ',', '.') }}</span>
    </div>
    @endif
    <hr>

    <div class="center text-sm">Terima kasih telah berbelanja!</div>
    <div class="center text-sm">Barang yang sudah dibeli tidak dapat ditukar</div>
    <br>
</body>
</html>
