@extends('app.layout')
@section('title','Inventory Reports')

@section('content')


                <div class="page-header">
                    <div>
                        <nav aria-label="Breadcrumb">
                            <ol class="breadcrumb">
                                <li class="breadcrumb-item"><a href="/app"><i class="bi bi-house-door"></i> Home</a></li>
                                <li class="breadcrumb-item"><a href="/app/inventory-overview">Inventory</a></li>
                                <li class="breadcrumb-item active" aria-current="page">Reports</li>
                            </ol>
                        </nav>
                        <h1 class="page-title">Inventory Reports</h1>
                        <p class="page-subtitle">Valuation per category and slow-moving stock.</p>
                    </div>
                    <div class="page-actions">
                        <a class="btn btn-light" href="/app/inventory-overview"><i class="bi bi-arrow-left"></i>Overview</a>
                    </div>
                </div>

                <div class="row g-app mb-4">
                    <div class="col-6 col-xl-4">
                        <div class="card kpi-card grad-indigo">
                            <div class="kpi-top"><div class="min-w-0"><div class="kpi-label">Stock value (cost)</div><div class="kpi-value">{{ tillora_rp($totalCost) }}</div></div><span class="icon-box tone-primary"><i class="bi bi-boxes"></i></span></div>
                            <div class="kpi-foot"><span>capital in inventory</span></div>
                        </div>
                    </div>
                    <div class="col-6 col-xl-4">
                        <div class="card kpi-card grad-cyan">
                            <div class="kpi-top"><div class="min-w-0"><div class="kpi-label">Retail value</div><div class="kpi-value">{{ tillora_rp($totalRetail) }}</div></div><span class="icon-box tone-cyan"><i class="bi bi-tag"></i></span></div>
                            <div class="kpi-foot"><span>at selling price</span></div>
                        </div>
                    </div>
                    <div class="col-12 col-xl-4">
                        <div class="card kpi-card grad-mint">
                            <div class="kpi-top"><div class="min-w-0"><div class="kpi-label">Potential profit</div><div class="kpi-value">{{ tillora_rp($totalRetail - $totalCost) }}</div></div><span class="icon-box tone-success"><i class="bi bi-graph-up"></i></span></div>
                            <div class="kpi-foot"><span>retail minus cost</span></div>
                        </div>
                    </div>
                </div>

                <div class="row g-app">
                    <div class="col-xl-7">
                        <div class="card table-card">
                            <div class="card-header"><h2 class="card-title h6 mb-0">Valuation by category</h2></div>
                            <div class="table-responsive">
                                <table class="table table-hover align-middle mb-0">
                                    <thead><tr><th>Category</th><th class="table-num">SKUs</th><th class="table-num">Units</th><th class="table-num">Cost</th><th class="table-num">Retail</th><th class="table-num">Profit</th></tr></thead>
                                    <tbody>
                                        @forelse($valuation as $v)
                                        <tr>
                                            <td class="fw-semibold">{{ $v['name'] }}</td>
                                            <td class="table-num">{{ number_format($v['sku']) }}</td>
                                            <td class="table-num">{{ number_format($v['units']) }}</td>
                                            <td class="table-num text-muted">{{ tillora_rp($v['cost']) }}</td>
                                            <td class="table-num">{{ tillora_rp($v['retail']) }}</td>
                                            <td class="table-num fw-semibold">{{ tillora_rp($v['profit']) }}</td>
                                        </tr>
                                        @empty
                                        <tr><td colspan="6" class="text-center text-muted py-4">Belum ada data valuasi.</td></tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                            @if($valuation->isNotEmpty())
                            <div class="card-body"><canvas id="valChart" height="160"></canvas></div>
                            @endif
                        </div>
                    </div>
                    <div class="col-xl-5">
                        <div class="card mb-4">
                            <div class="card-header"><h2 class="card-title h6 mb-0">Slow-moving (no sales 60 days)</h2></div>
                            <div class="card-body">
                                @forelse($slow as $p)
                                <div class="d-flex justify-content-between align-items-center py-2 border-bottom">
                                    <div class="min-w-0"><a class="cell-title" href="/app/product-details?id={{ $p->id }}">{{ $p->name }}</a><span class="cell-sub">{{ $p->category->name ?? '-' }} · stok {{ number_format((int)$p->current_stock) }}</span></div>
                                    <div class="text-end small"><div class="fw-semibold">{{ tillora_rp((float)$p->current_stock * (float)$p->cost_price) }}</div><small class="text-muted">tied up</small></div>
                                </div>
                                @empty
                                <p class="text-muted small mb-0">Tidak ada produk slow-moving. Semua produk terjual dalam 60 hari terakhir.</p>
                                @endforelse
                            </div>
                        </div>
                    </div>
                </div>
@push('scripts')
<script>
(function(){
    if(!window.Chart) return;
    var el = document.getElementById('valChart');
    if(!el) return;
    new Chart(el, {type:'bar',
        data:{labels:@json($valuation->pluck('name')), datasets:[
            {label:'Cost', data:@json($valuation->pluck('cost')->map(fn($v)=>(float)$v)), backgroundColor:'#6366f1'},
            {label:'Retail', data:@json($valuation->pluck('retail')->map(fn($v)=>(float)$v)), backgroundColor:'#06b6d4'}]},
        options:{plugins:{legend:{position:'bottom'}}, scales:{y:{ticks:{callback:function(v){return 'Rp '+(v/1000000)+' jt';}}}}}});
})();
</script>
@endpush
@endsection
