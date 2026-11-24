@extends('app.layout')
@section('title','Product Details')

@section('content')


                <div class="page-header">
                    <div>
                        <nav aria-label="Breadcrumb">
                            <ol class="breadcrumb">
                                <li class="breadcrumb-item"><a href="/app"><i class="bi bi-house-door"></i> Home</a></li>
                                <li class="breadcrumb-item"><a href="/app/products-list">Catalog</a></li>
                                <li class="breadcrumb-item"><a href="/app/products-list">Products</a></li>
                                <li class="breadcrumb-item active" aria-current="page">{{ $product->name }}</li>
                            </ol>
                        </nav>
                        <h1 class="page-title">{{ $product->name }}</h1>
                        <p class="page-subtitle">{{ $product->sku }} @if($product->barcode)· {{ $product->barcode }}@endif</p>
                    </div>
                    <div class="page-actions">
                        <a class="btn btn-light" href="/app/products-list"><i class="bi bi-arrow-left"></i>Back</a>
                        <a class="btn btn-primary" href="/app/purchase-create?product={{ $product->id }}"><i class="bi bi-cart-plus"></i>Reorder</a>
                    </div>
                </div>

                @if(session('success'))
                <div class="alert-strip success mb-3"><i class="bi bi-check-circle"></i>{{ session('success') }}</div>
                @endif

                <div class="row g-app mb-4">
                    <div class="col-6 col-xl-3">
                        <div class="card kpi-card grad-indigo">
                            <div class="kpi-top"><div class="min-w-0"><div class="kpi-label">Selling price</div><div class="kpi-value">{{ tillora_rp($product->selling_price) }}</div></div><span class="icon-box tone-primary"><i class="bi bi-tag"></i></span></div>
                            <div class="kpi-foot"><span>Cost {{ tillora_rp($product->cost_price) }}</span></div>
                        </div>
                    </div>
                    <div class="col-6 col-xl-3">
                        <div class="card kpi-card grad-mint">
                            <div class="kpi-top"><div class="min-w-0"><div class="kpi-label">Margin</div><div class="kpi-value">{{ number_format($margin,1) }}%</div></div><span class="icon-box tone-success"><i class="bi bi-graph-up"></i></span></div>
                            <div class="kpi-foot"><span>{{ tillora_rp((float)$product->selling_price - (float)$product->cost_price) }} per unit</span></div>
                        </div>
                    </div>
                    <div class="col-6 col-xl-3">
                        <div class="card kpi-card grad-amber">
                            <div class="kpi-top"><div class="min-w-0"><div class="kpi-label">Current stock</div><div class="kpi-value">{{ number_format((int)$product->current_stock) }}</div></div><span class="icon-box tone-warning"><i class="bi bi-boxes"></i></span></div>
                            <div class="kpi-foot"><span>Min {{ number_format((int)($product->min_stock ?? 0)) }} · Max {{ number_format((int)($product->max_stock ?? 0)) }}</span></div>
                        </div>
                    </div>
                    <div class="col-6 col-xl-3">
                        <div class="card kpi-card grad-cyan">
                            <div class="kpi-top"><div class="min-w-0"><div class="kpi-label">Units sold</div><div class="kpi-value">{{ number_format($soldQty) }}</div></div><span class="icon-box tone-cyan"><i class="bi bi-receipt"></i></span></div>
                            <div class="kpi-foot"><span>Revenue {{ tillora_rp($soldRev) }}</span></div>
                        </div>
                    </div>
                </div>

                <div class="row g-app">
                    <div class="col-xl-4">
                        <div class="card mb-4">
                            <div class="card-body text-center">
                                <img src="{{ tillora_pimg($product->image) }}" alt="{{ $product->name }}" class="img-fluid rounded mb-3" style="max-height:220px" onerror="this.src='/app-assets/images/products/mug.svg'">
                                <h2 class="h5 mb-1">{{ $product->name }}</h2>
                                <p class="text-muted small mb-3">{{ $product->category->name ?? 'Tanpa kategori' }} · {{ $product->brand->name ?? 'Tanpa merek' }} · {{ $product->unit->name ?? '-' }}</p>
                                @if($product->description)<p class="text-muted small text-start">{{ $product->description }}</p>@endif
                            </div>
                        </div>
                        <div class="card mb-4">
                            <div class="card-header"><h2 class="card-title h6 mb-0">Variants ({{ $product->variants->count() }})</h2></div>
                            <div class="card-body">
                                @forelse($product->variants as $v)
                                <div class="d-flex justify-content-between align-items-center py-2 border-bottom">
                                    <div><div class="fw-semibold">{{ $v->name }}</div><span class="cell-sub">{{ $v->sku }}</span></div>
                                    <div class="text-end"><div class="fw-semibold">{{ tillora_rp($v->selling_price) }}</div><small class="text-muted">Stok {{ number_format((int)$v->current_stock) }}</small></div>
                                </div>
                                @empty
                                <p class="text-muted small mb-0">Produk ini belum memiliki varian.</p>
                                @endforelse
                            </div>
                        </div>
                    </div>
                    <div class="col-xl-8">
                        <div class="card mb-4">
                            <div class="card-header"><h2 class="card-title h6 mb-0">Recent stock movements</h2></div>
                            <div class="table-responsive">
                                <table class="table table-hover align-middle mb-0">
                                    <thead><tr><th>Date</th><th>Type</th><th class="table-num">Qty</th><th>Outlet</th><th>Notes</th></tr></thead>
                                    <tbody>
                                        @forelse($movements as $m)
                                        <tr>
                                            <td class="text-muted small">{{ $m->created_at?->format('d M Y H:i') }}</td>
                                            <td><span class="status-badge {{ $m->type === 'in' ? 'status-success' : 'status-danger' }}">{{ ucfirst($m->type) }}</span></td>
                                            <td class="table-num fw-semibold">{{ $m->type === 'out' ? '-' : '+' }}{{ number_format((int)$m->quantity) }}</td>
                                            <td>{{ $m->outlet->name ?? '-' }}</td>
                                            <td class="text-muted small">{{ $m->notes ?? ($m->reference_type ? $m->reference_type.' #'.$m->reference_id : '-') }}</td>
                                        </tr>
                                        @empty
                                        <tr><td colspan="5" class="text-center text-muted py-4">Belum ada mutasi stok untuk produk ini.</td></tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <div class="card mb-4">
                            <div class="card-header"><h2 class="card-title h6 mb-0">Recent sales</h2></div>
                            <div class="table-responsive">
                                <table class="table table-hover align-middle mb-0">
                                    <thead><tr><th>Order</th><th>Date</th><th class="table-num">Qty</th><th class="table-num">Price</th><th class="table-num">Subtotal</th></tr></thead>
                                    <tbody>
                                        @forelse($sales as $s)
                                        <tr>
                                            <td><a class="cell-title" href="/app/sale-details?id={{ $s->order->id ?? '' }}">{{ $s->order->order_number ?? '-' }}</a></td>
                                            <td class="text-muted small">{{ $s->created_at?->format('d M Y') }}</td>
                                            <td class="table-num">{{ number_format((int)$s->quantity) }}</td>
                                            <td class="table-num text-muted">{{ tillora_rp($s->unit_price) }}</td>
                                            <td class="table-num fw-semibold">{{ tillora_rp($s->subtotal) }}</td>
                                        </tr>
                                        @empty
                                        <tr><td colspan="5" class="text-center text-muted py-4">Belum ada penjualan tercatat untuk produk ini.</td></tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
@endsection
