<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Struk #{{ $order['order_number'] }}</title>
    <style>
        @php $w = ($size ?? '80') === '58' ? '48mm' : '72mm'; @endphp
        @page { margin: 0; }
        * { box-sizing: border-box; }
        body {
            font-family: 'Courier New', Courier, monospace;
            font-size: {{ ($size ?? '80') === '58' ? '11px' : '12px' }};
            line-height: 1.45;
            width: {{ $w }};
            margin: 3mm auto;
            padding: 0 1mm;
            color: #000;
            background: #fff;
        }
        .center { text-align: center; }
        .bold { font-weight: bold; }
        .right { text-align: right; }
        .queue {
            text-align: center;
            font-size: 28px;
            font-weight: bold;
            letter-spacing: 2px;
            border: 2px solid #000;
            padding: 2px 0;
            margin: 4px 0;
        }
        hr { border: none; border-top: 1px dashed #000; margin: 5px 0; }
        table { width: 100%; border-collapse: collapse; }
        td { padding: 1px 0; vertical-align: top; }
        .line { display: flex; justify-content: space-between; gap: 4px; }
        .text-sm { font-size: 0.85em; }
        .barcode {
            text-align: center;
            font-size: 10px;
            letter-spacing: 3px;
            margin-top: 4px;
        }
        .reprint {
            text-align: center;
            font-weight: bold;
            border: 1px solid #000;
            margin-bottom: 4px;
        }
        @media print {
            body { margin: 0 auto; }
            .no-print { display: none; }
        }
    </style>
</head>
<body>
    @php
        $appName = \App\Models\SystemSetting::getAppName();
        $appLogo = \App\Models\SystemSetting::getLogoUrl();
        $receiptFooter = \App\Models\SystemSetting::getValue('receipt_footer', 'Terima kasih telah berbelanja!');
        $storeAddress = \App\Models\SystemSetting::getValue('store_address', '');
        $storePhone = \App\Models\SystemSetting::getValue('store_phone', '');
        $showLogo = \App\Models\SystemSetting::getBool('receipt_show_logo', true);
        $showName = \App\Models\SystemSetting::getBool('receipt_show_name', true);
        $showAddress = \App\Models\SystemSetting::getBool('receipt_show_address', true);
        $showPhone = \App\Models\SystemSetting::getBool('receipt_show_phone', true);
        $showFooter = \App\Models\SystemSetting::getBool('receipt_show_footer', true);
    @endphp
    @if(!empty($isReprint))
    <div class="reprint">*** CETAK ULANG ***</div>
    @endif
    @if($showLogo && $appLogo)
    <div class="center" style="margin-bottom:2mm">
        <img src="{{ $appLogo }}" alt="{{ $appName }}" style="max-width:100%; max-height:20mm; display:block; margin:0 auto;">
    </div>
    @endif
    @if($showName)
    <div class="center bold" style="font-size:1.15em">{{ $appName }}</div>
    @endif
    @if($showAddress && $storeAddress)
    <div class="center text-sm">{{ $storeAddress }}</div>
    @endif
    @if($showPhone && $storePhone)
    <div class="center text-sm">Telp: {{ $storePhone }}</div>
    @endif
    <div class="center text-sm">{{ $outlet ?? 'Outlet' }}</div>
    <hr>

    @if(!empty($order['queue_number']))
    <div class="queue">{{ $order['queue_number'] }}</div>
    @endif

    <div class="line">
        <span>No: {{ $order['order_number'] }}</span>
    </div>
    <div class="line">
        <span>{{ \Carbon\Carbon::parse($order['created_at'])->format('d/m/y H:i') }}</span>
        <span>Kasir: {{ $cashier ?? '-' }}</span>
    </div>
    @if(!empty($order['customer']))
    <div>Cust: {{ $order['customer']['name'] ?? '-' }}</div>
    @endif
    @if(!empty($order['table_name']))
    <div>Meja: {{ $order['table_name'] }}</div>
    @endif
    <hr>

    <table>
        @foreach($order['items'] as $item)
        <tr>
            <td colspan="3">{{ Str::limit($item['product']['name'] ?? 'Item', ($size ?? '80') === '58' ? 24 : 32) }}{{ !empty($item['variant']) ? ' ('.$item['variant'].')' : '' }}</td>
        </tr>
        <tr>
            <td>{{ $item['quantity'] }} x {{ number_format($item['unit_price'], 0, ',', '.') }}</td>
            <td class="right" colspan="2">{{ number_format($item['subtotal'] ?? ($item['quantity'] * $item['unit_price']), 0, ',', '.') }}</td>
        </tr>
        @endforeach
    </table>
    <hr>

    <div class="line">
        <span>Subtotal ({{ count($order['items']) }} item)</span>
        <span>Rp {{ number_format($order['subtotal'] ?? 0, 0, ',', '.') }}</span>
    </div>
    @if(!empty($order['promo_name']))
    <div class="line text-sm">
        <span>Promo: {{ $order['promo_name'] }}</span>
    </div>
    @endif
    @if(($order['discount_amount'] ?? 0) > 0)
    <div class="line">
        <span>Diskon</span>
        <span>-Rp {{ number_format($order['discount_amount'], 0, ',', '.') }}</span>
    </div>
    @endif
    @if(($order['tax_amount'] ?? 0) > 0)
    <div class="line">
        <span>Pajak</span>
        <span>Rp {{ number_format($order['tax_amount'], 0, ',', '.') }}</span>
    </div>
    @endif

    <div class="line bold" style="font-size: 1.25em; margin-top: 4px;">
        <span>TOTAL</span>
        <span>Rp {{ number_format($order['total_amount'] ?? 0, 0, ',', '.') }}</span>
    </div>
    <hr>

    @php
        $payments = $order['payments'] ?? [];
        $totalPaid = collect($payments)->sum('amount');
        $change = $totalPaid - ($order['total_amount'] ?? 0);
    @endphp

    @foreach($payments as $pay)
    <div class="line text-sm">
        <span>{{ $pay['method'] ?? 'Bayar' }}</span>
        <span>Rp {{ number_format($pay['amount'], 0, ',', '.') }}</span>
    </div>
    @endforeach
    @if(count($payments) > 1)
    <div class="line">
        <span>Dibayar</span>
        <span>Rp {{ number_format($totalPaid, 0, ',', '.') }}</span>
    </div>
    @endif
    @if($change > 0)
    <div class="line bold">
        <span>Kembali</span>
        <span>Rp {{ number_format($change, 0, ',', '.') }}</span>
    </div>
    @elseif(($order['remaining_amount'] ?? 0) > 0)
    <div class="line">
        <span>Sisa</span>
        <span>Rp {{ number_format($order['remaining_amount'], 0, ',', '.') }}</span>
    </div>
    @endif
    <hr>

    <div class="barcode">* {{ $order['order_number'] }} *</div>

    @if($showFooter)
    <div class="center text-sm" style="margin-top:4px">{{ $receiptFooter }}</div>
    @endif
    <div class="center text-sm">WiFi: tanya kasir</div>
    <br>
    <div class="center no-print">
        <button onclick="window.print()" style="padding:8px 24px">Cetak</button>
        <a href="?size={{ ($size ?? '80') === '58' ? '80' : '58' }}{{ !empty($isReprint) ? '&reprint=1' : '' }}" style="margin-left:8px">Ukuran {{ ($size ?? '80') === '58' ? '80mm' : '58mm' }}</a>
    </div>
    <script>
        const auto = new URLSearchParams(location.search).get('auto');
        if (auto !== '0') { window.onload = function(){ window.print(); }; }
    </script>
</body>
</html>
