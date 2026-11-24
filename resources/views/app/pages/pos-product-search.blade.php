@extends('app.layout')
@section('title','POS Product Search')


@section('content')
                <div class="page-header">
                    <div>
                        <nav aria-label="Breadcrumb">
                            <ol class="breadcrumb">
                                <li class="breadcrumb-item"><a href="/app"><i class="bi bi-house-door"></i> Home</a></li>
                                <li class="breadcrumb-item"><a href="/app/pos">POS</a></li>
                                <li class="breadcrumb-item active" aria-current="page">Product search</li>
                            </ol>
                        </nav>
                        <h1 class="page-title">Product search</h1>
                        <p class="page-subtitle">Cari produk asli berdasarkan SKU, nama, atau barcode.</p>
                    </div>
                    <div class="page-actions">
                        <a class="btn btn-primary" href="/pos"><i class="bi bi-upc-scan"></i>Buka Kasir</a>
                    </div>
                </div>

                <div class="card table-card">
                    <div class="table-toolbar">
                        <form method="GET" action="/app/pos-product-search" class="input-icon">
                            <i class="bi bi-search"></i>
                            <input type="search" name="q" value="{{ $q }}" class="form-control" placeholder="Search SKU, nama, barcode…" aria-label="Search produk">
                        </form>
                        <div class="toolbar-end">@if($q!=='')<a class="btn btn-light" href="/app/pos-product-search"><i class="bi bi-x-circle"></i>Reset</a>@endif</div>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead><tr><th>Produk</th><th>SKU</th><th>Barcode</th><th>Kategori</th><th class="table-num">Stok</th><th class="table-num">Harga</th><th class="col-actions"></th></tr></thead>
                            <tbody>
                                @forelse($products as $p)
                                <tr>
                                    <td><div class="cell-user"><span class="avatar tone-neutral"><i class="bi bi-box-seam"></i></span><div><span class="cell-title">{{ $p->name }}</span></div></div></td>
                                    <td><span class="table-id">{{ $p->sku }}</span></td>
                                    <td class="text-muted">{{ $p->barcode ?? '—' }}</td>
                                    <td>{{ optional($p->category)->name ?? '—' }}</td>
                                    <td class="table-num">{{ $p->current_stock }}</td>
                                    <td class="table-num fw-semibold">{{ tillora_rp($p->selling_price) }}</td>
                                    <td class="col-actions"><a class="btn btn-primary btn-sm" href="/pos"><i class="bi bi-cart-plus"></i>Jual</a></td>
                                </tr>
                                @empty
                                <tr><td colspan="7" class="text-center text-muted py-4">Tidak ada produk yang cocok{{ $q !== '' ? ' untuk “'.$q.'”' : '' }}.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
@endsection
