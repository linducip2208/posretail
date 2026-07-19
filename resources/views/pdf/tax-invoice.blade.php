<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Faktur Pajak {{ $taxInvoice->invoice_number }}</title>
    <style>
        body { font-family: Arial, Helvetica, sans-serif; font-size: 12px; color: #1a1a1a; margin: 0; padding: 0; }
        .container { padding: 30px; }
        .header { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 30px; border-bottom: 2px solid #2563eb; padding-bottom: 15px; }
        .header-left h1 { font-size: 22px; margin: 0 0 4px; color: #2563eb; }
        .header-left p { margin: 2px 0; color: #475569; font-size: 11px; }
        .header-right { text-align: right; }
        .header-right .label { font-size: 10px; color: #64748b; text-transform: uppercase; }
        .header-right .number { font-size: 16px; font-weight: bold; color: #1e293b; }
        .section { margin-bottom: 20px; }
        .section-title { font-size: 13px; font-weight: bold; background: #f1f5f9; padding: 6px 10px; border-radius: 4px; margin-bottom: 8px; }
        table { width: 100%; border-collapse: collapse; }
        table.detail th, table.detail td { border: 1px solid #e2e8f0; padding: 8px 10px; text-align: left; vertical-align: top; }
        table.detail th { background: #f8fafc; font-size: 11px; text-transform: uppercase; color: #475569; width: 30%; }
        table.detail td { font-size: 12px; }
        table.calc { margin-top: 10px; }
        table.calc th, table.calc td { border: 1px solid #e2e8f0; padding: 8px 10px; text-align: right; }
        table.calc th { background: #f8fafc; font-size: 11px; text-transform: uppercase; color: #475569; }
        table.calc td.label { text-align: left; font-weight: bold; }
        table.calc tr.total td { font-weight: bold; font-size: 14px; background: #eff6ff; }
        .status { display: inline-block; padding: 3px 10px; border-radius: 12px; font-size: 10px; font-weight: bold; text-transform: uppercase; }
        .status.issued { background: #dcfce7; color: #166534; }
        .status.draft { background: #f1f5f9; color: #475569; }
        .status.cancelled { background: #fee2e2; color: #991b1b; }
        .footer { margin-top: 40px; font-size: 10px; color: #94a3b8; text-align: center; border-top: 1px solid #e2e8f0; padding-top: 10px; }
        @media print { body { -webkit-print-color-adjust: exact; } }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <div class="header-left">
                <h1>{{ config('app.name') }}</h1>
                <p>FAKTUR PAJAK</p>
            </div>
            <div class="header-right">
                <div class="label">Nomor Faktur</div>
                <div class="number">{{ $taxInvoice->invoice_number }}</div>
                <div style="margin-top:8px">
                    <span class="status {{ $taxInvoice->status }}">{{ match($taxInvoice->status) { 'issued' => 'Diterbitkan', 'cancelled' => 'Dibatalkan', default => 'Draft' } }}</span>
                </div>
            </div>
        </div>

        <div class="section">
            <div class="section-title">Data Pelanggan</div>
            <table class="detail">
                <tr><th>Nama</th><td>{{ $taxInvoice->customer_name }}</td></tr>
                <tr><th>NPWP</th><td>{{ $taxInvoice->customer_npwp ?: '-' }}</td></tr>
                <tr><th>Alamat</th><td>{{ $taxInvoice->customer_address ?: '-' }}</td></tr>
            </table>
        </div>

        <div class="section">
            <div class="section-title">Detail Faktur</div>
            <table class="detail">
                <tr><th>Tanggal Faktur</th><td>{{ $taxInvoice->invoice_date->format('d F Y') }}</td></tr>
                <tr><th>No. Pesanan</th><td>{{ $taxInvoice->order?->order_number }}</td></tr>
                <tr><th>No. Referensi</th><td>{{ $taxInvoice->reference_number ?: '-' }}</td></tr>
                <tr><th>Catatan</th><td>{{ $taxInvoice->notes ?: '-' }}</td></tr>
            </table>
        </div>

        <div class="section">
            <div class="section-title">Perhitungan PPN</div>
            <table class="calc">
                <tr>
                    <td class="label">DPP (Dasar Pengenaan Pajak)</td>
                    <td>Rp {{ number_format($taxInvoice->dpp, 2, ',', '.') }}</td>
                </tr>
                <tr>
                    <td class="label">PPN (11%)</td>
                    <td>Rp {{ number_format($taxInvoice->ppn_amount, 2, ',', '.') }}</td>
                </tr>
                <tr class="total">
                    <td class="label">Total</td>
                    <td>Rp {{ number_format($taxInvoice->total_amount, 2, ',', '.') }}</td>
                </tr>
            </table>
        </div>

        <div class="footer">
            <p>Dokumen ini diterbitkan secara elektronik oleh {{ config('app.name') }} · {{ $taxInvoice->created_at?->format('d F Y H:i') }}</p>
        </div>
    </div>
</body>
</html>
