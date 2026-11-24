@extends('app.layout')
@section('title','Purchase Requisitions')

@section('content')
<div class="page-header">
    <div>
        <nav aria-label="Breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="/app"><i class="bi bi-house-door"></i> Home</a></li>
                <li class="breadcrumb-item"><a href="/app/purchase-orders">Purchasing</a></li>
                <li class="breadcrumb-item active" aria-current="page">Purchase Requisitions</li>
            </ol>
        </nav>
        <h1 class="page-title">Purchase Requisitions</h1>
        <p class="page-subtitle">Permintaan pembelian dari outlet beserta item dan status persetujuan.</p>
    </div>
    <div class="page-actions">
        <a class="btn btn-primary" href="/admin/purchase-requisitions/create"><i class="bi bi-plus-lg"></i>Buat baru</a>
    </div>
</div>

<div class="row g-app mb-4">
    <div class="col-6 col-xl-3">
        <div class="card kpi-card grad-indigo">
            <div class="kpi-top"><div class="min-w-0"><div class="kpi-label">Total PR</div><div class="kpi-value">{{ number_format($totalPr) }}</div></div><span class="icon-box tone-primary"><i class="bi bi-clipboard-check"></i></span></div>
        </div>
    </div>
    <div class="col-6 col-xl-3">
        <div class="card kpi-card grad-cyan">
            <div class="kpi-top"><div class="min-w-0"><div class="kpi-label">Total item</div><div class="kpi-value">{{ number_format($totalItems) }}</div></div><span class="icon-box tone-cyan"><i class="bi bi-list-check"></i></span></div>
        </div>
    </div>
    <div class="col-6 col-xl-3">
        <div class="card kpi-card grad-mint">
            <div class="kpi-top"><div class="min-w-0"><div class="kpi-label">Total qty diminta</div><div class="kpi-value">{{ number_format($totalQty) }}</div></div><span class="icon-box tone-success"><i class="bi bi-boxes"></i></span></div>
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
            <div class="card-header"><h2 class="card-title h6 mb-0">Ringkasan per status</h2></div>
            <div class="card-body">
                <canvas id="prChart" height="200"></canvas>
                <ul class="legend-list mt-3">
                    @forelse($statusSummary as $status => $count)
                    <li><span class="legend-dot" style="background:#06b6d4"></span><span>{{ ucfirst($status) }}</span><strong class="ms-auto">{{ number_format($count) }}</strong></li>
                    @empty
                    <li><span>Belum ada data permintaan pembelian.</span></li>
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
                    <input type="search" class="form-control" placeholder="Cari nomor PR, outlet…" data-table-search aria-label="Cari PR">
                </div>
            </div>
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead><tr><th>Nomor PR</th><th>Outlet</th><th>Pemohon</th><th>Status</th><th class="table-num">Item</th><th>Items</th><th>Dibutuhkan</th></tr></thead>
                    <tbody>
                        @forelse($requisitions as $pr)
                        <tr>
                            <td class="cell-title">{{ $pr->pr_number }}</td>
                            <td>{{ $pr->outlet->name ?? '—' }}</td>
                            <td>{{ $pr->requester->name ?? '—' }}</td>
                            <td><span class="status-badge">{{ ucfirst($pr->status) }}</span></td>
                            <td class="table-num">{{ number_format($pr->items->count()) }}</td>
                            <td><small class="text-muted">{{ $pr->item_summary }}</small></td>
                            <td>{{ $pr->date_needed?->format('d M Y') ?? '—' }}</td>
                        </tr>
                        @empty
                        <tr><td colspan="7" class="text-center text-muted py-4">Belum ada permintaan pembelian.</td></tr>
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
    new Chart(document.getElementById('prChart'), {type:'bar',
        data:{labels:@json($chartLabels), datasets:[{data:@json($chartValues), backgroundColor:'#06b6d4'}]},
        options:{plugins:{legend:{display:false}}, scales:{y:{beginAtZero:true}}}});
})();
</script>
@endpush
@endsection
