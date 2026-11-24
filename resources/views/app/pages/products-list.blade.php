@extends('app.layout')
@section('title','Products')

@section('content')


                <div class="page-header">
                    <div>
                        <nav aria-label="Breadcrumb">
                            <ol class="breadcrumb">
                                <li class="breadcrumb-item"><a href="/app"><i class="bi bi-house-door"></i> Home</a></li>
                                <li class="breadcrumb-item"><a href="/app/products-list">Catalog</a></li>
                                <li class="breadcrumb-item active" aria-current="page">Products</li>
                            </ol>
                        </nav>
                        <h1 class="page-title">Products</h1>
                        <p class="page-subtitle">Your full catalog with pricing, stock health and suppliers.</p>
                    </div>
                    <div class="page-actions">
                        <a class="btn btn-primary" href="/app/product-create"><i class="bi bi-plus-lg"></i>Add product</a>
                    </div>
                </div>

                <div class="row g-app mb-4">
                    <div class="col-6 col-xl-3">
                        <div class="card kpi-card grad-indigo">
                            <div class="kpi-top">
                                <div class="min-w-0">
                                    <div class="kpi-label">Active products</div>
                                    <div class="kpi-value">{{ number_format($totalActive) }}</div>
                                </div>
                                <span class="icon-box tone-primary"><i class="bi bi-box-seam"></i></span>
                            </div>
                            <div class="kpi-foot"><span>{{ $products->total() }} tampil pada filter ini</span></div>
                        </div>
                    </div>
                    <div class="col-6 col-xl-3">
                        <div class="card kpi-card grad-cyan">
                            <div class="kpi-top">
                                <div class="min-w-0">
                                    <div class="kpi-label">Inventory value</div>
                                    <div class="kpi-value">{{ tillora_rp($invValue) }}</div>
                                </div>
                                <span class="icon-box tone-cyan"><i class="bi bi-currency-dollar"></i></span>
                            </div>
                            <div class="kpi-foot"><span>at cost</span></div>
                        </div>
                    </div>
                    <div class="col-6 col-xl-3">
                        <div class="card kpi-card grad-amber">
                            <div class="kpi-top">
                                <div class="min-w-0">
                                    <div class="kpi-label">Low stock</div>
                                    <div class="kpi-value">{{ number_format($lowCount) }}</div>
                                </div>
                                <span class="icon-box tone-warning"><i class="bi bi-exclamation-triangle"></i></span>
                            </div>
                            <div class="kpi-foot"><span>at or below reorder point</span></div>
                        </div>
                    </div>
                    <div class="col-6 col-xl-3">
                        <div class="card kpi-card grad-rose">
                            <div class="kpi-top">
                                <div class="min-w-0">
                                    <div class="kpi-label">Out of stock</div>
                                    <div class="kpi-value">{{ number_format($outCount) }}</div>
                                </div>
                                <span class="icon-box tone-danger"><i class="bi bi-x-octagon"></i></span>
                            </div>
                            <div class="kpi-foot"><span>lost sales risk</span></div>
                        </div>
                    </div>
                </div>

                <div class="card table-card">
                    <form method="GET" action="/app/products-list" class="table-toolbar">
                        <div class="input-icon">
                            <i class="bi bi-search"></i>
                            <input type="search" name="q" class="form-control" placeholder="Search name, SKU, barcode…" value="{{ $q }}" aria-label="Search name, SKU, barcode…">
                        </div>
                        <select class="form-select" name="category" aria-label="All categories" onchange="this.form.submit()">
                            <option value="">All categories</option>
                            @foreach($categories as $c)
                            <option value="{{ $c->id }}" @selected((string)$category === (string)$c->id)>{{ $c->name }}</option>
                            @endforeach
                        </select>
                        <div class="toolbar-end">
                            <button type="submit" class="btn btn-light"><i class="bi bi-search"></i>Cari</button>
                        </div>
                    </form>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle" id="productsTable">
                            <thead>
                                <tr>
                                    <th class="col-main">Product</th>
                                    <th>Barcode</th>
                                    <th>Category</th>
                                    <th class="table-num">Cost</th>
                                    <th class="table-num">Price</th>
                                    <th class="table-num">Margin</th>
                                    <th>Stock</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($products as $p)
                                @php
                                    $st = (int)$p->current_stock; $mn = (int)($p->min_stock ?? 0);
                                    $cls = $st <= 0 ? 'is-out' : ($st <= $mn ? 'is-low' : 'is-ok');
                                    $badge = $st <= 0 ? 'status-danger' : ($st <= $mn ? 'status-warning' : 'status-success');
                                    $lbl = $st <= 0 ? 'Out of stock' : ($st <= $mn ? 'Low stock' : 'In stock');
                                    $pct = $mn > 0 ? min(100, round($st / max(1,$mn) * 50)) : ($st > 0 ? 100 : 0);
                                    $mgn = (float)$p->selling_price > 0 ? ((float)$p->selling_price - (float)$p->cost_price) / (float)$p->selling_price * 100 : 0;
                                @endphp
                                <tr>
                                    <td><div class="cell-product"><img class="product-thumb" src="{{ tillora_pimg($p->image) }}" alt="" loading="lazy" onerror="this.src='/app-assets/images/products/mug.svg'"><div><a class="cell-title" href="/app/product-details?id={{ $p->id }}">{{ $p->name }}</a><span class="cell-sub">{{ $p->sku }}</span></div></div></td>
                                    <td><span class="font-mono text-xs">{{ $p->barcode ?? '-' }}</span></td>
                                    <td>{{ $p->category->name ?? '-' }}</td>
                                    <td class="table-num text-muted">{{ tillora_rp($p->cost_price) }}</td>
                                    <td class="table-num fw-semibold">{{ tillora_rp($p->selling_price) }}</td>
                                    <td class="table-num">{{ number_format($mgn,1) }}%</td>
                                    <td><div class="stock-meter {{ $cls }}"><span class="fw-semibold text-heading tabular-nums">{{ number_format($st) }}</span><div class="progress"><div class="progress-bar" style="width: {{ $pct }}%;"></div></div></div></td>
                                    <td><span class="status-badge {{ $badge }}">{{ $lbl }}</span></td>
                                </tr>
                                @empty
                                <tr><td colspan="8" class="text-center text-muted py-4">Belum ada produk yang cocok dengan pencarian.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    @if($products->hasPages())
                    <div class="card-footer d-flex justify-content-between align-items-center">
                        <span class="text-muted small">Menampilkan {{ $products->firstItem() }}–{{ $products->lastItem() }} dari {{ number_format($products->total()) }} produk</span>
                        <div class="d-flex gap-2">
                            @if($products->currentPage() > 1)<a class="btn btn-light btn-sm" href="{{ $products->previousPageUrl() }}">Sebelumnya</a>@endif
                            @if($products->hasMorePages())<a class="btn btn-light btn-sm" href="{{ $products->nextPageUrl() }}">Berikutnya</a>@endif
                        </div>
                    </div>
                    @endif
                </div>
@endsection
