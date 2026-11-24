@extends('app.layout')
@section('title','Product Performance')

@section('content')


                <div class="page-header">
                    <div>
                        <nav aria-label="Breadcrumb">
                            <ol class="breadcrumb">
                                <li class="breadcrumb-item"><a href="/app"><i class="bi bi-house-door"></i> Home</a></li>
                                <li class="breadcrumb-item"><a href="/app/products-list">Catalog</a></li>
                                <li class="breadcrumb-item active" aria-current="page">Performance</li>
                            </ol>
                        </nav>
                        <h1 class="page-title">Product Performance</h1>
                        <p class="page-subtitle">Best and slowest sellers over the last {{ $days }} days (completed orders).</p>
                    </div>
                </div>

                <div class="row g-app">
                    <div class="col-xl-6">
                        <div class="card mb-4">
                            <div class="card-header"><h2 class="card-title h6 mb-0">Top 10 by quantity</h2></div>
                            <div class="card-body">
                                @php $mx = max(1, (int)($topQty->max('perf_qty') ?? 1)); @endphp
                                @forelse($topQty as $i => $p)
                                <div class="mb-3">
                                    <div class="d-flex justify-content-between"><a class="cell-title" href="/app/product-details?id={{ $p->id }}">{{ $i+1 }}. {{ $p->name }}</a><span class="fw-semibold tabular-nums">{{ number_format($p->perf_qty) }} pcs</span></div>
                                    <div class="rank-bar mt-1"><div class="rank-fill" style="width: {{ round($p->perf_qty/$mx*100) }}%;"></div></div>
                                </div>
                                @empty
                                <p class="text-muted small mb-0">Belum ada penjualan dalam {{ $days }} hari terakhir.</p>
                                @endforelse
                            </div>
                        </div>
                        <div class="card mb-4">
                            <div class="card-header"><h2 class="card-title h6 mb-0">Top 10 by revenue</h2></div>
                            <div class="table-responsive">
                                <table class="table table-hover align-middle mb-0">
                                    <thead><tr><th>Product</th><th class="table-num">Qty</th><th class="table-num">Revenue</th></tr></thead>
                                    <tbody>
                                        @forelse($topRevenue as $p)
                                        <tr><td><a class="cell-title" href="/app/product-details?id={{ $p->id }}">{{ $p->name }}</a></td><td class="table-num">{{ number_format($p->perf_qty) }}</td><td class="table-num fw-semibold">{{ tillora_rp($p->perf_revenue) }}</td></tr>
                                        @empty
                                        <tr><td colspan="3" class="text-center text-muted py-4">Belum ada data penjualan.</td></tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    <div class="col-xl-6">
                        <div class="card mb-4">
                            <div class="card-header"><h2 class="card-title h6 mb-0">Bottom 10 by quantity</h2></div>
                            <div class="table-responsive">
                                <table class="table table-hover align-middle mb-0">
                                    <thead><tr><th>Product</th><th class="table-num">Qty</th><th class="table-num">Revenue</th></tr></thead>
                                    <tbody>
                                        @forelse($bottomQty as $p)
                                        <tr><td><a class="cell-title" href="/app/product-details?id={{ $p->id }}">{{ $p->name }}</a></td><td class="table-num">{{ number_format($p->perf_qty) }}</td><td class="table-num fw-semibold">{{ tillora_rp($p->perf_revenue) }}</td></tr>
                                        @empty
                                        <tr><td colspan="3" class="text-center text-muted py-4">Belum ada data penjualan.</td></tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <div class="card mb-4">
                            <div class="card-header"><h2 class="card-title h6 mb-0">Bottom 10 by revenue</h2></div>
                            <div class="table-responsive">
                                <table class="table table-hover align-middle mb-0">
                                    <thead><tr><th>Product</th><th class="table-num">Qty</th><th class="table-num">Revenue</th></tr></thead>
                                    <tbody>
                                        @forelse($bottomRevenue as $p)
                                        <tr><td><a class="cell-title" href="/app/product-details?id={{ $p->id }}">{{ $p->name }}</a></td><td class="table-num">{{ number_format($p->perf_qty) }}</td><td class="table-num fw-semibold">{{ tillora_rp($p->perf_revenue) }}</td></tr>
                                        @empty
                                        <tr><td colspan="3" class="text-center text-muted py-4">Belum ada data penjualan.</td></tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
@endsection
