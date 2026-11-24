@extends('app.layout')
@section('title','Supplier Contracts')

@section('content')
<div class="page-header">
    <div>
        <nav aria-label="Breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="/app"><i class="bi bi-house-door"></i> Home</a></li>
                <li class="breadcrumb-item"><a href="/app/purchase-orders">Purchasing</a></li>
                <li class="breadcrumb-item active" aria-current="page">Supplier Contracts</li>
            </ol>
        </nav>
        <h1 class="page-title">Supplier Contracts</h1>
        <p class="page-subtitle">Kontrak kerja sama supplier beserta nilai, masa berlaku, dan yang segera kedaluwarsa.</p>
    </div>
    <div class="page-actions">
        <a class="btn btn-primary" href="/admin/supplier-contracts/create"><i class="bi bi-plus-lg"></i>Tambah</a>
    </div>
</div>

<div class="row g-app mb-4">
    <div class="col-6 col-xl-3">
        <div class="card kpi-card grad-indigo">
            <div class="kpi-top"><div class="min-w-0"><div class="kpi-label">Total kontrak</div><div class="kpi-value">{{ number_format($contracts->count()) }}</div></div><span class="icon-box tone-primary"><i class="bi bi-file-earmark-text"></i></span></div>
        </div>
    </div>
    <div class="col-6 col-xl-3">
        <div class="card kpi-card grad-cyan">
            <div class="kpi-top"><div class="min-w-0"><div class="kpi-label">Total nilai kontrak</div><div class="kpi-value">{{ tillora_rp($totalValue) }}</div></div><span class="icon-box tone-cyan"><i class="bi bi-cash-stack"></i></span></div>
        </div>
    </div>
    <div class="col-6 col-xl-3">
        <div class="card kpi-card grad-mint">
            <div class="kpi-top"><div class="min-w-0"><div class="kpi-label">Kedaluwarsa ≤ 30 hari</div><div class="kpi-value">{{ number_format($expiringCount) }}</div></div><span class="icon-box tone-success"><i class="bi bi-alarm"></i></span></div>
            <div class="kpi-foot"><span>perlu perpanjangan</span></div>
        </div>
    </div>
    <div class="col-6 col-xl-3">
        <div class="card kpi-card grad-amber">
            <div class="kpi-top"><div class="min-w-0"><div class="kpi-label">Status</div><div class="kpi-value">{{ number_format(count($statusSummary)) }}</div></div><span class="icon-box tone-warning"><i class="bi bi-flag"></i></span></div>
            <div class="kpi-foot"><span>variasi status</span></div>
        </div>
    </div>
</div>

<div class="row g-app">
    <div class="col-xl-4">
        <div class="card mb-4">
            <div class="card-header"><h2 class="card-title h6 mb-0">Segera kedaluwarsa (≤ 30 hari)</h2></div>
            <div class="card-body">
                @forelse($expiring as $e)
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <div class="min-w-0"><div class="cell-title text-truncate">{{ $e->contract_number }}</div><small class="text-muted">{{ $e->supplier->name ?? '—' }}</small></div>
                    <span class="fw-semibold tabular-nums">{{ $e->end_date?->format('d M Y') ?? '—' }}</span>
                </div>
                @empty
                <p class="text-muted small mb-0">Tidak ada kontrak yang segera kedaluwarsa.</p>
                @endforelse
            </div>
        </div>
        <div class="card mb-4">
            <div class="card-header"><h2 class="card-title h6 mb-0">Ringkasan per status</h2></div>
            <div class="card-body">
                <canvas id="contractChart" height="180"></canvas>
                <ul class="legend-list mt-3">
                    @forelse($statusSummary as $status => $count)
                    <li><span class="legend-dot" style="background:#10b981"></span><span>{{ ucfirst($status) }}</span><strong class="ms-auto">{{ number_format($count) }}</strong></li>
                    @empty
                    <li><span>Belum ada data kontrak.</span></li>
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
                    <input type="search" class="form-control" placeholder="Cari nomor kontrak, supplier…" data-table-search aria-label="Cari kontrak">
                </div>
            </div>
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead><tr><th>Nomor Kontrak</th><th>Supplier</th><th>Status</th><th class="table-num">Nilai</th><th>Masa Berlaku</th></tr></thead>
                    <tbody>
                        @forelse($contracts as $ct)
                        <tr>
                            <td class="cell-title">{{ $ct->contract_number }}</td>
                            <td>{{ $ct->supplier->name ?? '—' }}</td>
                            <td><span class="status-badge">{{ ucfirst($ct->status) }}</span></td>
                            <td class="table-num fw-semibold">{{ tillora_rp($ct->value) }}</td>
                            <td>{{ $ct->start_date?->format('d M Y') ?? '—' }} – {{ $ct->end_date?->format('d M Y') ?? '—' }}</td>
                        </tr>
                        @empty
                        <tr><td colspan="5" class="text-center text-muted py-4">Belum ada kontrak supplier.</td></tr>
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
    new Chart(document.getElementById('contractChart'), {type:'doughnut',
        data:{labels:@json($chartLabels), datasets:[{data:@json($chartValues), backgroundColor:['#6366f1','#06b6d4','#10b981','#f59e0b','#ef4444']}]},
        options:{plugins:{legend:{display:false}}}});
})();
</script>
@endpush
@endsection
