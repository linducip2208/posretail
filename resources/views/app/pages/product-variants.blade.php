@extends('app.layout')
@section('title','Variants')

@section('content')


                <div class="page-header">
                    <div>
                        <nav aria-label="Breadcrumb">
                            <ol class="breadcrumb">
                                <li class="breadcrumb-item"><a href="/app"><i class="bi bi-house-door"></i> Home</a></li>
                                <li class="breadcrumb-item"><a href="/app/products-list">Catalog</a></li>
                                <li class="breadcrumb-item active" aria-current="page">Variants</li>
                            </ol>
                        </nav>
                        <h1 class="page-title">Variants</h1>
                        <p class="page-subtitle">Size, color and pack options across your catalog.</p>
                    </div>
                    <div class="page-actions">
                        <a class="btn btn-light" href="/app/products-list"><i class="bi bi-arrow-left"></i>Products</a>
                    </div>
                </div>

                <div class="row g-app mb-4">
                    <div class="col-6 col-xl-4">
                        <div class="card kpi-card grad-indigo">
                            <div class="kpi-top"><div class="min-w-0"><div class="kpi-label">Total variants</div><div class="kpi-value">{{ number_format($totalVariants) }}</div></div><span class="icon-box tone-primary"><i class="bi bi-layers"></i></span></div>
                            <div class="kpi-foot"><span>latest 60 shown below</span></div>
                        </div>
                    </div>
                    <div class="col-6 col-xl-4">
                        <div class="card kpi-card grad-cyan">
                            <div class="kpi-top"><div class="min-w-0"><div class="kpi-label">Variant stock</div><div class="kpi-value">{{ number_format($totalStock) }}</div></div><span class="icon-box tone-cyan"><i class="bi bi-boxes"></i></span></div>
                            <div class="kpi-foot"><span>units across variants</span></div>
                        </div>
                    </div>
                    <div class="col-12 col-xl-4">
                        <div class="card kpi-card grad-mint">
                            <div class="kpi-top"><div class="min-w-0"><div class="kpi-label">Products with variants</div><div class="kpi-value">{{ number_format($productsWithVariants) }}</div></div><span class="icon-box tone-success"><i class="bi bi-box-seam"></i></span></div>
                            <div class="kpi-foot"><span>flagged has_variants</span></div>
                        </div>
                    </div>
                </div>

                <div class="card table-card">
                    <div class="card-header"><h2 class="card-title h6 mb-0">Latest variants</h2></div>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead><tr><th>Variant</th><th>Product</th><th>SKU</th><th>Barcode</th><th class="table-num">Price</th><th class="table-num">Stock</th></tr></thead>
                            <tbody>
                                @forelse($variants as $v)
                                <tr>
                                    <td class="fw-semibold">{{ $v->name }}</td>
                                    <td>@if($v->product)<a class="cell-title" href="/app/product-details?id={{ $v->product->id }}">{{ $v->product->name }}</a>@else<span class="text-muted">-</span>@endif</td>
                                    <td><span class="font-mono text-xs">{{ $v->sku }}</span></td>
                                    <td><span class="font-mono text-xs">{{ $v->barcode ?? '-' }}</span></td>
                                    <td class="table-num fw-semibold">{{ tillora_rp($v->selling_price) }}</td>
                                    <td class="table-num">{{ number_format((int)$v->current_stock) }}</td>
                                </tr>
                                @empty
                                <tr><td colspan="6" class="text-center text-muted py-4">Belum ada varian produk.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
@endsection
