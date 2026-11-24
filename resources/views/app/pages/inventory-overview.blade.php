@extends('app.layout')
@section('title','Inventory Overview')

@section('content')


                <div class="page-header">
                    <div>
                        <nav aria-label="Breadcrumb">
                            <ol class="breadcrumb">
                                <li class="breadcrumb-item"><a href="/app"><i class="bi bi-house-door"></i> Home</a></li>
                                <li class="breadcrumb-item"><a href="/app/inventory-overview">Inventory</a></li>
                                <li class="breadcrumb-item active" aria-current="page">Overview</li>
                            </ol>
                        </nav>
                        <h1 class="page-title">Inventory Overview</h1>
                        <p class="page-subtitle">Stock health, valuation and items that need attention.</p>
                    </div>
                    <div class="page-actions">
                        <a class="btn btn-light" href="/app/stock-levels"><i class="bi bi-boxes"></i>Stock levels</a>
                        <a class="btn btn-primary" href="/app/inventory-reports"><i class="bi bi-bar-chart"></i>Reports</a>
                    </div>
                </div>

                <div class="row g-app mb-4">
                    <div class="col-6 col-xl-3">
                        <div class="card kpi-card grad-indigo">
                            <div class="kpi-top"><div class="min-w-0"><div class="kpi-label">Total SKUs</div><div class="kpi-value">{{ number_format($totalSku) }}</div></div><span class="icon-box tone-primary"><i class="bi bi-box-seam"></i></span></div>
                            <div class="kpi-foot"><span>active products</span></div>
                        </div>
                    </div>
                    <div class="col-6 col-xl-3">
                        <div class="card kpi-card grad-cyan">
                            <div class="kpi-top"><div class="min-w-0"><div class="kpi-label">Stock value (cost)</div><div class="kpi-value">{{ tillora_rp($costValue) }}</div></div><span class="icon-box tone-cyan"><i class="bi bi-currency-dollar"></i></span></div>
                            <div class="kpi-foot"><span>capital tied in stock</span></div>
                        </div>
                    </div>
                    <div class="col-6 col-xl-3">
                        <div class="card kpi-card grad-mint">
                            <div class="kpi-top"><div class="min-w-0"><div class="kpi-label">Retail value</div><div class="kpi-value">{{ tillora_rp($retailValue) }}</div></div><span class="icon-box tone-success"><i class="bi bi-tag"></i></span></div>
                            <div class="kpi-foot"><span>at selling price</span></div>
                        </div>
                    </div>
                    <div class="col-6 col-xl-3">
                        <div class="card kpi-card grad-rose">
                            <div class="kpi-top"><div class="min-w-0"><div class="kpi-label">Need attention</div><div class="kpi-value">{{ number_format($lowCount + $outCount) }}</div></div><span class="icon-box tone-danger"><i class="bi bi-exclamation-triangle"></i></span></div>
                            <div class="kpi-foot"><span>{{ number_format($lowCount) }} low · {{ number_format($outCount) }} out</span></div>
                        </div>
                    </div>
                </div>

                <div class="row g-app">
                    <div class="col-xl-6">
                        <div class="card mb-4">
                            <div class="card-header"><h2 class="card-title h6 mb-0">Stock value by category</h2></div>
                            <div class="card-body">
                                <canvas id="invCatChart" height="200"></canvas>
                                <ul class="legend-list mt-3">
                                    @foreach($byCategory->take(8) as $c)
                                    <li><span class="legend-dot" style="background:#6366f1"></span><span>{{ $c['name'] }} ({{ number_format($c['count']) }})</span><strong class="ms-auto">{{ tillora_rp($c['value']) }}</strong></li>
                                    @endforeach
                                </ul>
                                @if($byCategory->isEmpty())<p class="text-muted small mb-0">Belum ada data kategori.</p>@endif
                            </div>
                        </div>
                    </div>
                    <div class="col-xl-6">
                        <div class="card mb-4">
                            <div class="card-header d-flex justify-content-between align-items-center"><h2 class="card-title h6 mb-0">Top 5 running low</h2><a class="btn btn-light btn-sm" href="/app/low-stock-alerts">View all</a></div>
                            <div class="card-body">
                                @forelse($topLow as $p)
                                @php $mn = max(1,(int)($p->min_stock ?? 1)); $pct = min(100, round((int)$p->current_stock / $mn * 50)); @endphp
                                <div class="mb-3">
                                    <div class="d-flex justify-content-between"><a class="cell-title" href="/app/product-details?id={{ $p->id }}">{{ $p->name }}</a><span class="fw-semibold tabular-nums">{{ number_format((int)$p->current_stock) }} / {{ number_format((int)($p->min_stock ?? 0)) }}</span></div>
                                    <div class="rank-bar mt-1"><div class="rank-fill" style="width: {{ $pct }}%;"></div></div>
                                </div>
                                @empty
                                <p class="text-muted small mb-0">Tidak ada produk menipis. Stok dalam kondisi baik.</p>
                                @endforelse
                            </div>
                        </div>
                    </div>
                </div>
@push('scripts')
<script>
(function(){
    if(!window.Chart) return;
    new Chart(document.getElementById('invCatChart'), {type:'doughnut',
        data:{labels:@json($byCategory->pluck('name')), datasets:[{data:@json($byCategory->pluck('value')->map(fn($v)=>(float)$v)), backgroundColor:['#6366f1','#06b6d4','#10b981','#f59e0b','#ef4444','#8b5cf6','#ec4899','#14b8a6']}]},
        options:{plugins:{legend:{display:false}}}});
})();
</script>
@endpush
@endsection
