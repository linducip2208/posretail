@extends('app.layout')
@section('title','Assembly Orders')

@section('content')
<div class="page-header">
    <div>
        <nav aria-label="Breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="/app"><i class="bi bi-house-door"></i> Home</a></li>
                <li class="breadcrumb-item"><a href="/app/inventory-overview">Production</a></li>
                <li class="breadcrumb-item active" aria-current="page">Assembly Orders</li>
            </ol>
        </nav>
        <h1 class="page-title">Assembly Orders</h1>
        <p class="page-subtitle">Perintah perakitan produk beserta bahan yang dipakai dan status pengerjaan.</p>
    </div>
    <div class="page-actions">
        <a class="btn btn-primary" href="/admin/assembly-orders/create"><i class="bi bi-plus-lg"></i>Buat baru</a>
    </div>
</div>

<div class="row g-app mb-4">
    <div class="col-6 col-xl-3">
        <div class="card kpi-card grad-indigo">
            <div class="kpi-top"><div class="min-w-0"><div class="kpi-label">Total order</div><div class="kpi-value">{{ number_format($orders->count()) }}</div></div><span class="icon-box tone-primary"><i class="bi bi-gear"></i></span></div>
        </div>
    </div>
    <div class="col-6 col-xl-3">
        <div class="card kpi-card grad-cyan">
            <div class="kpi-top"><div class="min-w-0"><div class="kpi-label">Total qty rakit</div><div class="kpi-value">{{ number_format($totalQty) }}</div></div><span class="icon-box tone-cyan"><i class="bi bi-boxes"></i></span></div>
        </div>
    </div>
    <div class="col-6 col-xl-6">
        <div class="card kpi-card grad-mint">
            <div class="kpi-top"><div class="min-w-0"><div class="kpi-label">Status</div><div class="kpi-value">{{ number_format(count($statusSummary)) }}</div></div><span class="icon-box tone-success"><i class="bi bi-flag"></i></span></div>
            <div class="kpi-foot"><span>variasi status pengerjaan</span></div>
        </div>
    </div>
</div>

<div class="row g-app">
    <div class="col-xl-4">
        <div class="card mb-4">
            <div class="card-header"><h2 class="card-title h6 mb-0">Ringkasan per status</h2></div>
            <div class="card-body">
                <canvas id="assemblyChart" height="200"></canvas>
                <ul class="legend-list mt-3">
                    @forelse($statusSummary as $status => $count)
                    <li><span class="legend-dot" style="background:#6366f1"></span><span>{{ ucfirst($status) }}</span><strong class="ms-auto">{{ number_format($count) }}</strong></li>
                    @empty
                    <li><span>Belum ada data assembly order.</span></li>
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
                    <input type="search" class="form-control" placeholder="Cari nomor assembly, produk…" data-table-search aria-label="Cari assembly">
                </div>
            </div>
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead><tr><th>Nomor</th><th>Produk</th><th>Outlet</th><th>Status</th><th class="table-num">Qty</th><th>Bahan</th><th>Selesai</th></tr></thead>
                    <tbody>
                        @forelse($orders as $a)
                        <tr>
                            <td class="cell-title">{{ $a->assembly_number }}</td>
                            <td>{{ $a->product->name ?? '—' }}</td>
                            <td>{{ $a->outlet->name ?? '—' }}</td>
                            <td><span class="status-badge">{{ ucfirst($a->status) }}</span></td>
                            <td class="table-num">{{ number_format($a->quantity) }}</td>
                            <td><small class="text-muted">{{ $a->material_summary }}</small></td>
                            <td>{{ $a->completed_at?->format('d M Y') ?? '—' }}</td>
                        </tr>
                        @empty
                        <tr><td colspan="7" class="text-center text-muted py-4">Belum ada assembly order.</td></tr>
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
    new Chart(document.getElementById('assemblyChart'), {type:'doughnut',
        data:{labels:@json($chartLabels), datasets:[{data:@json($chartValues), backgroundColor:['#6366f1','#06b6d4','#10b981','#f59e0b','#ef4444']}]},
        options:{plugins:{legend:{display:false}}}});
})();
</script>
@endpush
@endsection
