@extends('app.layout')
@section('title','Consignments')

@section('content')
<div class="page-header">
    <div>
        <nav aria-label="Breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="/app"><i class="bi bi-house-door"></i> Home</a></li>
                <li class="breadcrumb-item"><a href="/app/purchase-orders">Purchasing</a></li>
                <li class="breadcrumb-item active" aria-current="page">Consignments</li>
            </ol>
        </nav>
        <h1 class="page-title">Consignments</h1>
        <p class="page-subtitle">Barang titipan supplier terbaru beserta ringkasan status dan nilai.</p>
    </div>
    <div class="page-actions">
        <a class="btn btn-primary" href="/admin/consignments/create"><i class="bi bi-plus-lg"></i>Buat baru</a>
    </div>
</div>

<div class="row g-app mb-4">
    <div class="col-6 col-xl-3">
        <div class="card kpi-card grad-indigo">
            <div class="kpi-top"><div class="min-w-0"><div class="kpi-label">Total titipan</div><div class="kpi-value">{{ number_format($consignments->count()) }}</div></div><span class="icon-box tone-primary"><i class="bi bi-box-seam"></i></span></div>
        </div>
    </div>
    <div class="col-6 col-xl-3">
        <div class="card kpi-card grad-cyan">
            <div class="kpi-top"><div class="min-w-0"><div class="kpi-label">Nilai terjual</div><div class="kpi-value">{{ tillora_rp($totalValue) }}</div></div><span class="icon-box tone-cyan"><i class="bi bi-cash-stack"></i></span></div>
        </div>
    </div>
    <div class="col-6 col-xl-3">
        <div class="card kpi-card grad-mint">
            <div class="kpi-top"><div class="min-w-0"><div class="kpi-label">Sisa stok</div><div class="kpi-value">{{ number_format($totalRemaining) }}</div></div><span class="icon-box tone-success"><i class="bi bi-boxes"></i></span></div>
            <div class="kpi-foot"><span>{{ number_format($totalSold) }} terjual dari {{ number_format($totalQty) }}</span></div>
        </div>
    </div>
    <div class="col-6 col-xl-3">
        <div class="card kpi-card grad-amber">
            <div class="kpi-top"><div class="min-w-0"><div class="kpi-label">Status</div><div class="kpi-value">{{ number_format(count($statusSummary)) }}</div></div><span class="icon-box tone-warning"><i class="bi bi-flag"></i></span></div>
            <div class="kpi-foot"><span>variasi status aktif</span></div>
        </div>
    </div>
</div>

<div class="row g-app">
    <div class="col-xl-4">
        <div class="card mb-4">
            <div class="card-header"><h2 class="card-title h6 mb-0">Ringkasan per status</h2></div>
            <div class="card-body">
                <canvas id="consignChart" height="200"></canvas>
                <ul class="legend-list mt-3">
                    @forelse($statusSummary as $status => $count)
                    <li><span class="legend-dot" style="background:#6366f1"></span><span>{{ ucfirst($status) }}</span><strong class="ms-auto">{{ number_format($count) }}</strong></li>
                    @empty
                    <li><span>Belum ada data konsinyasi.</span></li>
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
                    <input type="search" class="form-control" placeholder="Cari nomor, supplier, produk…" data-table-search aria-label="Cari konsinyasi">
                </div>
            </div>
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead><tr><th>Nomor</th><th>Supplier</th><th>Produk</th><th>Status</th><th class="table-num">Qty / Terjual</th><th class="table-num">Nilai Terjual</th><th>Tanggal</th></tr></thead>
                    <tbody>
                        @forelse($consignments as $c)
                        <tr>
                            <td class="cell-title">{{ $c->consignment_number }}</td>
                            <td>{{ $c->supplier->name ?? '—' }}</td>
                            <td>{{ $c->product->name ?? '—' }}</td>
                            <td><span class="status-badge">{{ ucfirst($c->status) }}</span></td>
                            <td class="table-num">{{ number_format($c->quantity) }} / {{ number_format($c->sold_quantity) }}</td>
                            <td class="table-num fw-semibold">{{ tillora_rp($c->sold_value) }}</td>
                            <td>{{ $c->start_date?->format('d M Y') ?? $c->created_at?->format('d M Y') }}</td>
                        </tr>
                        @empty
                        <tr><td colspan="7" class="text-center text-muted py-4">Belum ada data konsinyasi.</td></tr>
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
    new Chart(document.getElementById('consignChart'), {type:'doughnut',
        data:{labels:@json($chartLabels), datasets:[{data:@json($chartValues), backgroundColor:['#6366f1','#06b6d4','#10b981','#f59e0b','#ef4444']}]},
        options:{plugins:{legend:{display:false}}}});
})();
</script>
@endpush
@endsection
