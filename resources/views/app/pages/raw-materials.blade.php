@extends('app.layout')
@section('title','Raw Materials')

@section('content')
<div class="page-header">
    <div>
        <nav aria-label="Breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="/app"><i class="bi bi-house-door"></i> Home</a></li>
                <li class="breadcrumb-item"><a href="/app/inventory-overview">Inventory</a></li>
                <li class="breadcrumb-item active" aria-current="page">Raw Materials</li>
            </ol>
        </nav>
        <h1 class="page-title">Raw Materials</h1>
        <p class="page-subtitle">Stok dan nilai bahan baku produksi beserta yang perlu restock.</p>
    </div>
    <div class="page-actions">
        <a class="btn btn-primary" href="/admin/raw-materials/create"><i class="bi bi-plus-lg"></i>Tambah</a>
    </div>
</div>

<div class="row g-app mb-4">
    <div class="col-6 col-xl-3">
        <div class="card kpi-card grad-indigo">
            <div class="kpi-top"><div class="min-w-0"><div class="kpi-label">Jenis material</div><div class="kpi-value">{{ number_format($materials->count()) }}</div></div><span class="icon-box tone-primary"><i class="bi bi-boxes"></i></span></div>
        </div>
    </div>
    <div class="col-6 col-xl-3">
        <div class="card kpi-card grad-cyan">
            <div class="kpi-top"><div class="min-w-0"><div class="kpi-label">Nilai stok</div><div class="kpi-value">{{ tillora_rp($totalValue) }}</div></div><span class="icon-box tone-cyan"><i class="bi bi-cash-stack"></i></span></div>
        </div>
    </div>
    <div class="col-6 col-xl-6">
        <div class="card kpi-card grad-amber">
            <div class="kpi-top"><div class="min-w-0"><div class="kpi-label">Perlu restock</div><div class="kpi-value">{{ number_format($lowCount) }}</div></div><span class="icon-box tone-warning"><i class="bi bi-exclamation-triangle"></i></span></div>
            <div class="kpi-foot"><span>stok ≤ stok minimum</span></div>
        </div>
    </div>
</div>

<div class="row g-app">
    <div class="col-xl-4">
        <div class="card mb-4">
            <div class="card-header"><h2 class="card-title h6 mb-0">Nilai stok terbesar</h2></div>
            <div class="card-body">
                <canvas id="materialChart" height="200"></canvas>
                <ul class="legend-list mt-3">
                    @forelse($lowStock as $m)
                    <li><span class="legend-dot" style="background:#ef4444"></span><span>{{ $m->name }} ({{ number_format($m->current_stock) }} {{ $m->unit->symbol ?? $m->unit->name ?? '' }})</span><strong class="ms-auto">min {{ number_format($m->min_stock) }}</strong></li>
                    @empty
                    <li><span>Semua stok bahan baku aman.</span></li>
                    @endforelse
                </ul>
            </div>
        </div>
    </div>
    <div class="col-xl-8">
        <div class="card table-card" data-table data-page-size="10">
            <div class="table-toolbar">
                <div class="input-icon">
                    <i class="bi bi-search"></i>
                    <input type="search" class="form-control" placeholder="Cari bahan baku…" data-table-search aria-label="Cari bahan baku">
                </div>
            </div>
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead><tr><th>Material</th><th>Outlet</th><th class="table-num">Stok</th><th class="table-num">Min</th><th class="table-num">Biaya / Satuan</th><th class="table-num">Nilai Stok</th><th>Status</th></tr></thead>
                    <tbody>
                        @forelse($materials as $m)
                        <tr>
                            <td><div class="cell-title">{{ $m->name }}</div><small class="text-muted">{{ $m->code ?? '' }}</small></td>
                            <td>{{ $m->outlet->name ?? '—' }}</td>
                            <td class="table-num">{{ number_format($m->current_stock) }} {{ $m->unit->symbol ?? $m->unit->name ?? '' }}</td>
                            <td class="table-num">{{ number_format($m->min_stock) }}</td>
                            <td class="table-num">{{ tillora_rp($m->cost_per_unit) }}</td>
                            <td class="table-num fw-semibold">{{ tillora_rp($m->stock_value) }}</td>
                            <td>
                                @if($m->current_stock <= $m->min_stock)
                                <span class="status-badge status-danger">Restock</span>
                                @else
                                <span class="status-badge status-success">Aman</span>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="7" class="text-center text-muted py-4">Belum ada data bahan baku.</td></tr>
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
    new Chart(document.getElementById('materialChart'), {type:'bar',
        data:{labels:@json($chartLabels), datasets:[{data:@json($chartValues), backgroundColor:'#06b6d4'}]},
        options:{indexAxis:'y', plugins:{legend:{display:false}}}});
})();
</script>
@endpush
@endsection
