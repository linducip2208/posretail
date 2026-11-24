@extends('app.layout')
@section('title','Categories')

@section('content')


                <div class="page-header">
                    <div>
                        <nav aria-label="Breadcrumb">
                            <ol class="breadcrumb">
                                <li class="breadcrumb-item"><a href="/app"><i class="bi bi-house-door"></i> Home</a></li>
                                <li class="breadcrumb-item"><a href="/app/products-list">Catalog</a></li>
                                <li class="breadcrumb-item active" aria-current="page">Categories</li>
                            </ol>
                        </nav>
                        <h1 class="page-title">Categories</h1>
                        <p class="page-subtitle">How your catalog is grouped, with product counts and stock value.</p>
                    </div>
                    <div class="page-actions">
                        <a class="btn btn-primary" href="/app/product-create"><i class="bi bi-plus-lg"></i>Add product</a>
                    </div>
                </div>

                <div class="row g-app mb-4">
                    <div class="col-6 col-xl-4">
                        <div class="card kpi-card grad-indigo">
                            <div class="kpi-top"><div class="min-w-0"><div class="kpi-label">Total categories</div><div class="kpi-value">{{ number_format($categories->count()) }}</div></div><span class="icon-box tone-primary"><i class="bi bi-diagram-3"></i></span></div>
                            <div class="kpi-foot"><span>{{ number_format($totalProducts) }} products overall</span></div>
                        </div>
                    </div>
                    <div class="col-6 col-xl-4">
                        <div class="card kpi-card grad-cyan">
                            <div class="kpi-top"><div class="min-w-0"><div class="kpi-label">Total stock value</div><div class="kpi-value">{{ tillora_rp($totalValue) }}</div></div><span class="icon-box tone-cyan"><i class="bi bi-currency-dollar"></i></span></div>
                            <div class="kpi-foot"><span>at cost across categories</span></div>
                        </div>
                    </div>
                    <div class="col-12 col-xl-4">
                        <div class="card kpi-card grad-mint">
                            <div class="kpi-top"><div class="min-w-0"><div class="kpi-label">Top category</div><div class="kpi-value fs-5">{{ $categories->sortByDesc('stock_value')->first()->name ?? '-' }}</div></div><span class="icon-box tone-success"><i class="bi bi-trophy"></i></span></div>
                            <div class="kpi-foot"><span>{{ tillora_rp($categories->max('stock_value')) }} stock value</span></div>
                        </div>
                    </div>
                </div>

                <div class="row g-app">
                    <div class="col-xl-7">
                        <div class="card table-card">
                            <div class="card-header"><h2 class="card-title h6 mb-0">All categories</h2></div>
                            <div class="table-responsive">
                                <table class="table table-hover align-middle mb-0">
                                    <thead><tr><th>Category</th><th class="table-num">Products</th><th class="table-num">Stock value</th><th class="table-num">Retail value</th></tr></thead>
                                    <tbody>
                                        @forelse($categories as $c)
                                        <tr>
                                            <td><a class="cell-title" href="/app/products-list?category={{ $c->id }}">{{ $c->name }}</a></td>
                                            <td class="table-num">{{ number_format($c->product_count) }}</td>
                                            <td class="table-num">{{ tillora_rp($c->stock_value) }}</td>
                                            <td class="table-num fw-semibold">{{ tillora_rp($c->retail_value) }}</td>
                                        </tr>
                                        @empty
                                        <tr><td colspan="4" class="text-center text-muted py-4">Belum ada kategori.</td></tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    <div class="col-xl-5">
                        <div class="card mb-4">
                            <div class="card-header"><h2 class="card-title h6 mb-0">Value by category</h2></div>
                            <div class="card-body">
                                <canvas id="catChart" height="220"></canvas>
                                <ul class="legend-list mt-3">
                                    @foreach($categories->sortByDesc('stock_value')->take(8) as $c)
                                    <li><span class="legend-dot" style="background:#6366f1"></span><span>{{ $c->name }}</span><strong class="ms-auto">{{ tillora_rp($c->stock_value) }}</strong></li>
                                    @endforeach
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
@push('scripts')
<script>
(function(){
    if(!window.Chart) return;
    new Chart(document.getElementById('catChart'), {type:'doughnut',
        data:{labels:@json($categories->pluck('name')), datasets:[{data:@json($categories->pluck('stock_value')->map(fn($v)=>(float)$v)), backgroundColor:['#6366f1','#06b6d4','#10b981','#f59e0b','#ef4444','#8b5cf6','#ec4899','#14b8a6']}]},
        options:{plugins:{legend:{display:false}}}});
})();
</script>
@endpush
@endsection
