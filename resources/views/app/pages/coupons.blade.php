@extends('app.layout')
@section('title','Coupons')



@section('content')
<div class="page-header">
    <div>
        <nav aria-label="Breadcrumb"><ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/app"><i class="bi bi-house-door"></i> Home</a></li>
            <li class="breadcrumb-item"><a href="/app/promotions">Marketing</a></li>
            <li class="breadcrumb-item active" aria-current="page">Coupons</li>
        </ol></nav>
        <h1 class="page-title">Coupons</h1>
        <p class="page-subtitle">Gift card &amp; kode kupon dari database — {{ number_format($totalCards,0,',','.') }} kode tercatat.</p>
    </div>
</div>

<div class="row g-app mb-4">
    <div class="col-6 col-xl-3">
        <div class="card kpi-card grad-indigo">
            <div class="kpi-top"><div class="min-w-0"><div class="kpi-label">Total kode</div><div class="kpi-value">{{ number_format($totalCards,0,',','.') }}</div></div><span class="icon-box tone-primary"><i class="bi bi-upc-scan"></i></span></div>
            <div class="kpi-foot"><span>{{ number_format($activeCards,0,',','.') }} aktif saat ini</span></div>
        </div>
    </div>
    <div class="col-6 col-xl-3">
        <div class="card kpi-card grad-mint">
            <div class="kpi-top"><div class="min-w-0"><div class="kpi-label">Aktif</div><div class="kpi-value">{{ number_format($activeCards,0,',','.') }}</div></div><span class="icon-box tone-success"><i class="bi bi-check-circle"></i></span></div>
            <div class="kpi-foot"><span>Bisa dipakai di POS</span></div>
        </div>
    </div>
    <div class="col-6 col-xl-3">
        <div class="card kpi-card grad-cyan">
            <div class="kpi-top"><div class="min-w-0"><div class="kpi-label">Total pemakaian</div><div class="kpi-value">{{ number_format($usedTotal,0,',','.') }}</div></div><span class="icon-box tone-cyan"><i class="bi bi-arrow-repeat"></i></span></div>
            <div class="kpi-foot"><span>Seluruh kode</span></div>
        </div>
    </div>
    <div class="col-6 col-xl-3">
        <div class="card kpi-card grad-amber">
            <div class="kpi-top"><div class="min-w-0"><div class="kpi-label">Kedaluwarsa</div><div class="kpi-value">{{ number_format($expiredCards,0,',','.') }}</div></div><span class="icon-box tone-warning"><i class="bi bi-clock-history"></i></span></div>
            <div class="kpi-foot"><span>Lewat masa berlaku</span></div>
        </div>
    </div>
</div>

<div class="row g-app mb-4">
    @forelse($featured as $f)
    <div class="col-lg-4">
        <div class="coupon-ticket">
            <div class="coupon-value"><strong>{{ $f->type === 'percent' ? rtrim(rtrim(number_format((float)$f->value,2), '0'), '.').'%' : tillora_rp($f->value) }}</strong><small>{{ $f->type === 'percent' ? 'OFF' : 'NOMINAL' }}</small></div>
            <div class="coupon-body">
                <div class="d-flex justify-content-between align-items-center mb-1">
                    <span class="coupon-code">{{ $f->code }}</span>
                    <button type="button" class="btn btn-ghost btn-icon btn-sm" data-copy="{{ $f->code }}" aria-label="Copy code {{ $f->code }}"><i class="bi bi-copy"></i></button>
                </div>
                <p class="small text-muted mb-2">{{ $f->customer?->name ? 'Untuk '.$f->customer->name : 'Untuk semua pelanggan' }}</p>
                <div class="d-flex justify-content-between small mb-1"><span>{{ number_format($f->used_count,0,',','.') }} dipakai / {{ $f->max_usage ?? '—' }}</span><span class="text-muted">{{ $f->valid_until ? \Carbon\Carbon::parse($f->valid_until)->format('d M Y') : 'Tanpa batas' }}</span></div>
                <div class="progress"><div class="progress-bar" style="width: {{ $f->max_usage ? round($f->used_count/$f->max_usage*100) : 0 }}%;"></div></div>
            </div>
        </div>
    </div>
    @empty
    <div class="col-12"><div class="alert-strip is-info"><i class="bi bi-info-circle"></i><span class="small">Belum ada gift card / kupon di database.</span></div></div>
    @endforelse
</div>

<div class="row g-app">
    <div class="col-xl-7">
        <div class="card table-card" data-table data-page-size="10">
            <div class="card-header"><div><h2 class="card-title">All coupon codes</h2><p class="card-subtitle">Data asli gift card</p></div></div>
            <div class="table-toolbar">
                <div class="input-icon"><i class="bi bi-search"></i><input type="search" class="form-control" placeholder="Cari kode…" data-table-search aria-label="Cari kode…"></div>
                <select class="form-select" data-table-filter="status" aria-label="Semua status"><option value="">Semua status</option><option value="active">Active</option><option value="expired">Expired</option><option value="used">Used</option></select>
            </div>
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead><tr><th>Code</th><th>Value</th><th class="col-main">Pelanggan</th><th class="table-num">Terpakai</th><th>Berlaku s/d</th><th>Status</th></tr></thead>
                    <tbody>
                        @forelse($cards as $c)
                        <tr data-status="{{ $c->status }}">
                            <td><span class="coupon-code">{{ $c->code }}</span></td>
                            <td class="fw-semibold">{{ $c->type === 'percent' ? rtrim(rtrim(number_format((float)$c->value,2),'0'),'.').'% off' : tillora_rp($c->value) }}</td>
                            <td>{{ $c->customer?->name ?? '—' }}</td>
                            <td class="table-num">{{ number_format($c->used_count,0,',','.') }}{{ $c->max_usage ? ' / '.number_format($c->max_usage,0,',','.') : '' }}</td>
                            <td>{{ $c->valid_until ? \Carbon\Carbon::parse($c->valid_until)->format('d M Y') : '—' }}</td>
                            <td><span class="status-badge {{ $c->status === 'active' ? 'status-success' : ($c->status === 'expired' ? 'status-neutral' : 'status-info') }}">{{ ucfirst($c->status) }}</span></td>
                        </tr>
                        @empty
                        <tr><td colspan="6" class="text-center text-muted py-4">Belum ada kupon di database.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <div class="col-xl-5">
        <div class="card h-100">
            <div class="card-header"><div><h2 class="card-title">Pemakaian</h2><p class="card-subtitle">Harian, 30 hari terakhir · data asli</p></div></div>
            <div class="card-body"><div class="chart-box chart-sm"><canvas id="couponUsage"></canvas></div></div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
(function(){
  if (!window.Chart) return;
  new Chart(document.getElementById('couponUsage'), {type:'bar',
    data:{labels:@json($usageLabels),datasets:[{label:'Pemakaian',data:@json($usageData),backgroundColor:'#4F46E5',borderRadius:4}]},
    options:{plugins:{legend:{display:false}},scales:{y:{beginAtZero:true,ticks:{precision:0}}}}});
})();
</script>
@endpush
