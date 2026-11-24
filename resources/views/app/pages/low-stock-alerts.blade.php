@extends('app.layout')
@section('title','Low Stock Alerts')

@section('content')


                <div class="page-header">
                    <div>
                        <nav aria-label="Breadcrumb">
                            <ol class="breadcrumb">
                                <li class="breadcrumb-item"><a href="/app"><i class="bi bi-house-door"></i> Home</a></li>
                                <li class="breadcrumb-item"><a href="/app/inventory-overview">Inventory</a></li>
                                <li class="breadcrumb-item active" aria-current="page">Low stock</li>
                            </ol>
                        </nav>
                        <h1 class="page-title">Low Stock Alerts</h1>
                        <p class="page-subtitle">{{ number_format($items->count()) }} products at or below reorder point.</p>
                    </div>
                    <div class="page-actions">
                        <a class="btn btn-light" href="/app/stock-levels"><i class="bi bi-boxes"></i>Stock levels</a>
                    </div>
                </div>

                <div class="row g-app mb-4">
                    <div class="col-6 col-xl-4">
                        <div class="card kpi-card grad-rose">
                            <div class="kpi-top"><div class="min-w-0"><div class="kpi-label">Out of stock</div><div class="kpi-value">{{ number_format($outOfStock->count()) }}</div></div><span class="icon-box tone-danger"><i class="bi bi-x-octagon"></i></span></div>
                            <div class="kpi-foot"><span>urgent reorder</span></div>
                        </div>
                    </div>
                    <div class="col-6 col-xl-4">
                        <div class="card kpi-card grad-amber">
                            <div class="kpi-top"><div class="min-w-0"><div class="kpi-label">Critical</div><div class="kpi-value">{{ number_format($critical->count()) }}</div></div><span class="icon-box tone-warning"><i class="bi bi-exclamation-triangle"></i></span></div>
                            <div class="kpi-foot"><span>below half of minimum</span></div>
                        </div>
                    </div>
                    <div class="col-12 col-xl-4">
                        <div class="card kpi-card grad-indigo">
                            <div class="kpi-top"><div class="min-w-0"><div class="kpi-label">Low</div><div class="kpi-value">{{ number_format($low->count()) }}</div></div><span class="icon-box tone-primary"><i class="bi bi-arrow-down-circle"></i></span></div>
                            <div class="kpi-foot"><span>at reorder point</span></div>
                        </div>
                    </div>
                </div>

                <div class="card table-card">
                    <div class="card-header"><h2 class="card-title h6 mb-0">Stock alerts</h2></div>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead><tr><th>Product</th><th>Category</th><th class="table-num">On hand</th><th class="table-num">Min</th><th>Status</th><th class="col-actions"></th></tr></thead>
                            <tbody>
                                @forelse($items as $p)
                                @php
                                    $st=(int)$p->current_stock;
                                    $half = max(1,(int)floor((float)($p->min_stock ?? 0)/2));
                                    $sev = $st<=0?'Out of stock':($st<=$half?'Critical':'Low');
                                    $badge=$st<=0?'status-danger':($st<=$half?'status-warning':'status-info');
                                @endphp
                                <tr>
                                    <td><a class="cell-title" href="/app/product-details?id={{ $p->id }}">{{ $p->name }}</a><span class="cell-sub">{{ $p->sku }}</span></td>
                                    <td>{{ $p->category->name ?? '-' }}</td>
                                    <td class="table-num fw-semibold">{{ number_format($st) }}</td>
                                    <td class="table-num text-muted">{{ number_format((int)($p->min_stock ?? 0)) }}</td>
                                    <td><span class="status-badge {{ $badge }}">{{ $sev }}</span></td>
                                    <td class="col-actions"><a class="btn btn-primary btn-sm" href="/app/purchase-create?product={{ $p->id }}"><i class="bi bi-cart-plus"></i>Reorder</a></td>
                                </tr>
                                @empty
                                <tr><td colspan="6" class="text-center text-muted py-4">Tidak ada peringatan stok. Semua produk di atas batas minimum.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="card stock-alerts mt-4">
                    <div class="card-header"><h2 class="card-title h6 mb-0">Out of stock now</h2></div>
                    <div class="card-body">
                        @forelse($outOfStock as $p)
                        <div class="d-flex justify-content-between align-items-center py-2 border-bottom">
                            <a class="cell-title" href="/app/product-details?id={{ $p->id }}">{{ $p->name }}</a>
                            <a class="btn btn-light btn-sm" href="/app/purchase-create?product={{ $p->id }}">Reorder</a>
                        </div>
                        @empty
                        <p class="text-muted small mb-0">Tidak ada produk yang habis.</p>
                        @endforelse
                    </div>
                </div>
@endsection
