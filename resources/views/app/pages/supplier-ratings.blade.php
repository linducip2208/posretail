@extends('app.layout')
@section('title','Supplier Ratings')

@section('content')
<div class="page-header">
    <div>
        <nav aria-label="Breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="/app"><i class="bi bi-house-door"></i> Home</a></li>
                <li class="breadcrumb-item"><a href="/app/purchase-orders">Purchasing</a></li>
                <li class="breadcrumb-item active" aria-current="page">Supplier Ratings</li>
            </ol>
        </nav>
        <h1 class="page-title">Supplier Ratings</h1>
        <p class="page-subtitle">Penilaian kinerja supplier berdasarkan ketepatan, kualitas, harga, dan komunikasi.</p>
    </div>
    <div class="page-actions">
        <a class="btn btn-light" href="/app/suppliers-list"><i class="bi bi-truck"></i>Suppliers</a>
        <a class="btn btn-primary" href="/admin/supplier-ratings/create"><i class="bi bi-plus-lg"></i>Tambah</a>
    </div>
</div>

<div class="row g-app mb-4">
    <div class="col-6 col-xl-3">
        <div class="card kpi-card grad-indigo">
            <div class="kpi-top"><div class="min-w-0"><div class="kpi-label">Rata-rata rating</div><div class="kpi-value">{{ number_format($avgRating, 1) }} / 5</div></div><span class="icon-box tone-primary"><i class="bi bi-star"></i></span></div>
        </div>
    </div>
    <div class="col-6 col-xl-3">
        <div class="card kpi-card grad-cyan">
            <div class="kpi-top"><div class="min-w-0"><div class="kpi-label">Total penilaian</div><div class="kpi-value">{{ number_format($totalRatings) }}</div></div><span class="icon-box tone-cyan"><i class="bi bi-chat-square-heart"></i></span></div>
        </div>
    </div>
    <div class="col-6 col-xl-3">
        <div class="card kpi-card grad-mint">
            <div class="kpi-top"><div class="min-w-0"><div class="kpi-label">Supplier dinilai</div><div class="kpi-value">{{ number_format(count($topSuppliers)) }}</div></div><span class="icon-box tone-success"><i class="bi bi-truck"></i></span></div>
            <div class="kpi-foot"><span>dalam 5 besar</span></div>
        </div>
    </div>
    <div class="col-6 col-xl-3">
        <div class="card kpi-card grad-amber">
            <div class="kpi-top"><div class="min-w-0"><div class="kpi-label">Perlu perhatian</div><div class="kpi-value">{{ number_format(count($worstSuppliers)) }}</div></div><span class="icon-box tone-warning"><i class="bi bi-exclamation-triangle"></i></span></div>
            <div class="kpi-foot"><span>rating terendah</span></div>
        </div>
    </div>
</div>

<div class="row g-app">
    <div class="col-xl-4">
        <div class="card mb-4">
            <div class="card-header"><h2 class="card-title h6 mb-0">Top supplier</h2></div>
            <div class="card-body">
                <canvas id="ratingChart" height="200"></canvas>
                <ul class="legend-list mt-3">
                    @forelse($topSuppliers as $t)
                    <li><span class="legend-dot" style="background:#f59e0b"></span><span>{{ $t['name'] }} ({{ number_format($t['count']) }}×)</span><strong class="ms-auto">{{ number_format($t['avg'], 1) }} / 5</strong></li>
                    @empty
                    <li><span>Belum ada penilaian supplier.</span></li>
                    @endforelse
                </ul>
            </div>
        </div>
        <div class="card mb-4">
            <div class="card-header"><h2 class="card-title h6 mb-0">Rating terendah</h2></div>
            <div class="card-body">
                @forelse($worstSuppliers as $w)
                <div class="d-flex justify-content-between mb-2"><span>{{ $w['name'] }}</span><strong>{{ number_format($w['avg'], 1) }} / 5</strong></div>
                @empty
                <p class="text-muted small mb-0">Belum ada penilaian supplier.</p>
                @endforelse
            </div>
        </div>
    </div>
    <div class="col-xl-8">
        <div class="card table-card" data-table data-page-size="10">
            <div class="table-toolbar">
                <div class="input-icon">
                    <i class="bi bi-search"></i>
                    <input type="search" class="form-control" placeholder="Cari supplier…" data-table-search aria-label="Cari rating">
                </div>
            </div>
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead><tr><th>Supplier</th><th class="table-num">Tepat Waktu</th><th class="table-num">Kualitas</th><th class="table-num">Harga</th><th class="table-num">Komunikasi</th><th class="table-num">Rata-rata</th><th>Tanggal</th></tr></thead>
                    <tbody>
                        @forelse($ratings as $rt)
                        <tr>
                            <td class="cell-title">{{ $rt->supplier->name ?? '—' }}</td>
                            <td class="table-num">{{ number_format($rt->on_time, 1) }}</td>
                            <td class="table-num">{{ number_format($rt->quality, 1) }}</td>
                            <td class="table-num">{{ number_format($rt->price_competitiveness, 1) }}</td>
                            <td class="table-num">{{ number_format($rt->communication, 1) }}</td>
                            <td class="table-num fw-semibold">{{ number_format($rt->avg_score, 1) }} / 5</td>
                            <td>{{ $rt->created_at?->format('d M Y') }}</td>
                        </tr>
                        @empty
                        <tr><td colspan="7" class="text-center text-muted py-4">Belum ada penilaian supplier.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@push('scripts')
<script>
(function(){
    if(!window.Chart) return;
    new Chart(document.getElementById('ratingChart'), {type:'bar',
        data:{labels:@json($chartLabels), datasets:[{data:@json($chartValues), backgroundColor:'#f59e0b'}]},
        options:{indexAxis:'y', plugins:{legend:{display:false}}, scales:{x:{beginAtZero:true, max:5}}}});
})();
</script>
@endpush
@endsection
