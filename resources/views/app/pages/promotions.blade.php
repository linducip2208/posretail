@extends('app.layout')
@section('title','Promotions')



@section('content')
<div class="page-header">
    <div>
        <nav aria-label="Breadcrumb"><ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/app"><i class="bi bi-house-door"></i> Home</a></li>
            <li class="breadcrumb-item active" aria-current="page">Promotions</li>
        </ol></nav>
        <h1 class="page-title">Promotions</h1>
        <p class="page-subtitle">Paket bundle &amp; promo gift card yang sedang berjalan · data asli.</p>
    </div>
</div>

<div class="row g-app mb-4">
    <div class="col-6 col-xl-4">
        <div class="card kpi-card grad-indigo">
            <div class="kpi-top"><div class="min-w-0"><div class="kpi-label">Bundle aktif</div><div class="kpi-value">{{ number_format($activeBundles,0,',','.') }}</div></div><span class="icon-box tone-primary"><i class="bi bi-box2-heart"></i></span></div>
            <div class="kpi-foot"><span>dari {{ number_format($bundleCount,0,',','.') }} bundle</span></div>
        </div>
    </div>
    <div class="col-6 col-xl-4">
        <div class="card kpi-card grad-mint">
            <div class="kpi-top"><div class="min-w-0"><div class="kpi-label">Gift card aktif</div><div class="kpi-value">{{ number_format($giftActiveCount,0,',','.') }}</div></div><span class="icon-box tone-success"><i class="bi bi-gift"></i></span></div>
            <div class="kpi-foot"><span>promo berjalan · <a href="/app/coupons">lihat kupon</a></span></div>
        </div>
    </div>
    <div class="col-6 col-xl-4">
        <div class="card kpi-card grad-cyan">
            <div class="kpi-top"><div class="min-w-0"><div class="kpi-label">Total promo berjalan</div><div class="kpi-value">{{ number_format($activeBundles + $giftActiveCount,0,',','.') }}</div></div><span class="icon-box tone-cyan"><i class="bi bi-megaphone"></i></span></div>
            <div class="kpi-foot"><span>bundle + gift card</span></div>
        </div>
    </div>
</div>

<div class="card table-card mb-4">
    <div class="card-header"><div><h2 class="card-title">Bundles</h2><p class="card-subtitle">Paket hemat beserta hematnya vs harga normal · data asli</p></div></div>
    <div class="table-responsive">
        <table class="table table-hover align-middle">
            <thead><tr><th class="col-main">Bundle</th><th class="table-num">Item</th><th class="table-num">Harga bundle</th><th class="table-num">Harga normal</th><th class="table-num">Hemat</th><th>Periode</th><th>Status</th></tr></thead>
            <tbody>
                @forelse($bundles as $b)
                <tr>
                    <td><span class="cell-title">{{ $b->name }}</span><span class="cell-sub">{{ $b->items->map(fn($it) => ($it->product?->name ?? '?').' ×'.$it->quantity)->take(3)->join(', ') }}{{ $b->items_count > 3 ? ' +' . ($b->items_count - 3) . ' lainnya' : '' }}</span></td>
                    <td class="table-num">{{ $b->items_count }}</td>
                    <td class="table-num fw-semibold">{{ tillora_rp($b->bundle_price) }}</td>
                    <td class="table-num text-muted">{{ tillora_rp($b->normal_total) }}</td>
                    <td class="table-num text-success fw-semibold">{{ tillora_rp($b->saving) }} <small>({{ $b->saving_pct }}%)</small></td>
                    <td class="text-muted">{{ $b->start_date ? \Carbon\Carbon::parse($b->start_date)->format('d M Y') : '—' }} – {{ $b->end_date ? \Carbon\Carbon::parse($b->end_date)->format('d M Y') : '—' }}</td>
                    <td><span class="status-badge {{ $b->active ? 'status-success' : 'status-neutral' }}">{{ $b->active ? 'Active' : 'Inactive' }}</span></td>
                </tr>
                @empty
                <tr><td colspan="7" class="text-center text-muted py-4">Belum ada bundle di database.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<div class="card table-card">
    <div class="card-header"><div><h2 class="card-title">Promo berjalan — gift card aktif</h2><p class="card-subtitle">Tidak ada tabel broadcast di database, jadi promo berjalan diambil dari gift card aktif · <a href="/app/coupons">kelola kupon</a></p></div></div>
    <div class="table-responsive">
        <table class="table table-hover align-middle">
            <thead><tr><th>Kode</th><th>Nilai</th><th class="col-main">Pelanggan</th><th class="table-num">Terpakai</th><th>Berlaku s/d</th></tr></thead>
            <tbody>
                @forelse($giftPromos as $g)
                <tr>
                    <td><span class="coupon-code">{{ $g->code }}</span></td>
                    <td class="fw-semibold">{{ $g->type === 'percent' ? rtrim(rtrim(number_format((float)$g->value,2),'0'),'.').'% off' : tillora_rp($g->value) }}</td>
                    <td>{{ $g->customer?->name ?? 'Semua pelanggan' }}</td>
                    <td class="table-num">{{ number_format($g->used_count,0,',','.') }}{{ $g->max_usage ? ' / '.number_format($g->max_usage,0,',','.') : '' }}</td>
                    <td>{{ $g->valid_until ? \Carbon\Carbon::parse($g->valid_until)->format('d M Y') : 'Tanpa batas' }}</td>
                </tr>
                @empty
                <tr><td colspan="5" class="text-center text-muted py-4">Tidak ada gift card aktif saat ini.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
