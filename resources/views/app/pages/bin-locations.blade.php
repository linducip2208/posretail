@extends('app.layout')
@section('title','Bin Locations')

@section('content')
<div class="page-header">
    <div>
        <nav aria-label="Breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="/app"><i class="bi bi-house-door"></i> Home</a></li>
                <li class="breadcrumb-item"><a href="/app/inventory-overview">Inventory</a></li>
                <li class="breadcrumb-item active" aria-current="page">Bin Locations</li>
            </ol>
        </nav>
        <h1 class="page-title">Bin Locations</h1>
        <p class="page-subtitle">Lokasi rak/bin gudang beserta jumlah produk di tiap lokasi.</p>
    </div>
    <div class="page-actions">
        <a class="btn btn-primary" href="/admin/bin-locations/create"><i class="bi bi-plus-lg"></i>Tambah</a>
    </div>
</div>

<div class="row g-app mb-4">
    <div class="col-6 col-xl-4">
        <div class="card kpi-card grad-indigo">
            <div class="kpi-top"><div class="min-w-0"><div class="kpi-label">Total lokasi</div><div class="kpi-value">{{ number_format($bins->count()) }}</div></div><span class="icon-box tone-primary"><i class="bi bi-grid-1x2"></i></span></div>
        </div>
    </div>
    <div class="col-6 col-xl-4">
        <div class="card kpi-card grad-cyan">
            <div class="kpi-top"><div class="min-w-0"><div class="kpi-label">Produk terpetakan</div><div class="kpi-value">{{ number_format($totalProducts) }}</div></div><span class="icon-box tone-cyan"><i class="bi bi-box-seam"></i></span></div>
        </div>
    </div>
    <div class="col-6 col-xl-4">
        <div class="card kpi-card grad-mint">
            <div class="kpi-top"><div class="min-w-0"><div class="kpi-label">Lokasi kosong</div><div class="kpi-value">{{ number_format($emptyBinCount) }}</div></div><span class="icon-box tone-success"><i class="bi bi-inbox"></i></span></div>
            <div class="kpi-foot"><span>belum ada produk</span></div>
        </div>
    </div>
</div>

<div class="row g-app">
    <div class="col-xl-4">
        <div class="card mb-4">
            <div class="card-header"><h2 class="card-title h6 mb-0">Produk per lokasi (Top 10)</h2></div>
            <div class="card-body">
                <canvas id="binChart" height="220"></canvas>
            </div>
        </div>
    </div>
    <div class="col-xl-8">
        <div class="card table-card" data-table data-page-size="10">
            <div class="table-toolbar">
                <div class="input-icon">
                    <i class="bi bi-search"></i>
                    <input type="search" class="form-control" placeholder="Cari kode, nama, zona…" data-table-search aria-label="Cari lokasi">
                </div>
            </div>
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead><tr><th>Kode</th><th>Nama</th><th>Zona</th><th>Outlet</th><th class="table-num">Produk</th><th>Status</th></tr></thead>
                    <tbody>
                        @forelse($bins as $b)
                        <tr>
                            <td class="cell-title">{{ $b->code }}</td>
                            <td>{{ $b->name ?? '—' }}</td>
                            <td>{{ $b->zone ?? '—' }}</td>
                            <td>{{ $b->outlet->name ?? '—' }}</td>
                            <td class="table-num fw-semibold">{{ number_format($b->products_count) }}</td>
                            <td>
                                @if($b->active)
                                <span class="status-badge status-success">Aktif</span>
                                @else
                                <span class="status-badge status-danger">Nonaktif</span>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="6" class="text-center text-muted py-4">Belum ada lokasi bin.</td></tr>
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
    new Chart(document.getElementById('binChart'), {type:'bar',
        data:{labels:@json($chartLabels), datasets:[{data:@json($chartValues), backgroundColor:'#6366f1'}]},
        options:{indexAxis:'y', plugins:{legend:{display:false}}, scales:{x:{beginAtZero:true}}}});
})();
</script>
@endpush
@endsection
