@extends('app.layout')
@section('title','Discounts')



@section('content')
<div class="page-header">
    <div>
        <nav aria-label="Breadcrumb"><ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/app"><i class="bi bi-house-door"></i> Home</a></li>
            <li class="breadcrumb-item"><a href="/app/promotions">Marketing</a></li>
            <li class="breadcrumb-item active" aria-current="page">Discounts</li>
        </ol></nav>
        <h1 class="page-title">Discounts</h1>
        <p class="page-subtitle">{{ number_format($totalTemplates,0,',','.') }} template diskon &amp; {{ number_format($volumes->count(),0,',','.') }} aturan harga bertingkat · data asli.</p>
    </div>
</div>

<div class="row g-app mb-4">
    <div class="col-6 col-xl-4">
        <div class="card kpi-card grad-indigo">
            <div class="kpi-top"><div class="min-w-0"><div class="kpi-label">Template aktif</div><div class="kpi-value">{{ number_format($activeTemplates,0,',','.') }}</div></div><span class="icon-box tone-primary"><i class="bi bi-percent"></i></span></div>
            <div class="kpi-foot"><span>dari {{ number_format($totalTemplates,0,',','.') }} template</span></div>
        </div>
    </div>
    <div class="col-6 col-xl-4">
        <div class="card kpi-card grad-cyan">
            <div class="kpi-top"><div class="min-w-0"><div class="kpi-label">Harga bertingkat aktif</div><div class="kpi-value">{{ number_format($activeVolumes,0,',','.') }}</div></div><span class="icon-box tone-cyan"><i class="bi bi-layers"></i></span></div>
            <div class="kpi-foot"><span>aturan volume pricing</span></div>
        </div>
    </div>
    <div class="col-6 col-xl-4">
        <div class="card kpi-card grad-mint">
            <div class="kpi-top"><div class="min-w-0"><div class="kpi-label">Diskon diberikan (30d)</div><div class="kpi-value">{{ tillora_rp($discount30d) }}</div></div><span class="icon-box tone-success"><i class="bi bi-tag"></i></span></div>
            <div class="kpi-foot"><span>total dari transaksi</span></div>
        </div>
    </div>
</div>

<div class="card table-card mb-4">
    <div class="card-header"><div><h2 class="card-title">Discount templates</h2><p class="card-subtitle">Data asli · {{ number_format($totalTemplates,0,',','.') }} template</p></div></div>
    <div class="table-responsive">
        <table class="table table-hover align-middle">
            <thead><tr><th class="col-main">Nama</th><th>Tipe</th><th class="table-num">Nilai</th><th class="table-num">Min. belanja</th><th>Periode</th><th>Outlet</th><th>Status</th></tr></thead>
            <tbody>
                @forelse($templates as $t)
                <tr>
                    <td><span class="cell-title">{{ $t->name }}</span>@if($t->buy_quantity)<span class="cell-sub">Beli {{ $t->buy_quantity }} gratis {{ $t->get_quantity }}</span>@endif</td>
                    <td>{{ $t->type }}</td>
                    <td class="table-num fw-semibold">{{ $t->type === 'percent' ? rtrim(rtrim(number_format((float)$t->value,2),'0'),'.').'%' : tillora_rp($t->value) }}</td>
                    <td class="table-num">{{ $t->min_purchase ? tillora_rp($t->min_purchase) : '—' }}</td>
                    <td class="text-muted">{{ $t->start_date ? \Carbon\Carbon::parse($t->start_date)->format('d M Y') : '—' }} – {{ $t->end_date ? \Carbon\Carbon::parse($t->end_date)->format('d M Y') : '—' }}</td>
                    <td>{{ $t->outlet?->name ?? 'Semua' }}</td>
                    <td><span class="status-badge {{ $t->active ? 'status-success' : 'status-neutral' }}">{{ $t->active ? 'Active' : 'Inactive' }}</span></td>
                </tr>
                @empty
                <tr><td colspan="7" class="text-center text-muted py-4">Belum ada template diskon di database.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<div class="card table-card">
    <div class="card-header"><div><h2 class="card-title">Volume pricing</h2><p class="card-subtitle">Aturan harga bertingkat per produk · data asli</p></div></div>
    <div class="table-responsive">
        <table class="table table-hover align-middle">
            <thead><tr><th class="col-main">Produk</th><th>Grup pelanggan</th><th class="table-num">Min qty</th><th class="table-num">Maks qty</th><th class="table-num">Harga satuan</th><th>Diskon</th><th>Status</th></tr></thead>
            <tbody>
                @forelse($volumes as $v)
                <tr>
                    <td><span class="cell-title">{{ $v->product?->name ?? 'Produk #'.$v->product_id }}</span><span class="cell-sub">{{ $v->product?->sku ?? '' }}</span></td>
                    <td>{{ $v->customerGroup?->name ?? 'Semua' }}</td>
                    <td class="table-num">{{ number_format($v->min_qty,0,',','.') }}</td>
                    <td class="table-num">{{ $v->max_qty ? number_format($v->max_qty,0,',','.') : '∞' }}</td>
                    <td class="table-num fw-semibold">{{ $v->unit_price ? tillora_rp($v->unit_price) : '—' }}</td>
                    <td>{{ $v->discount_type === 'percent' ? rtrim(rtrim(number_format((float)$v->discount_value,2),'0'),'.').'%' : ($v->discount_value ? tillora_rp($v->discount_value) : '—') }}</td>
                    <td><span class="status-badge {{ $v->active ? 'status-success' : 'status-neutral' }}">{{ $v->active ? 'Active' : 'Inactive' }}</span></td>
                </tr>
                @empty
                <tr><td colspan="7" class="text-center text-muted py-4">Belum ada aturan volume pricing di database.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
