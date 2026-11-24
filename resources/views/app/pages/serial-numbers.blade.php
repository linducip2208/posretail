@extends('app.layout')
@section('title','Serial Numbers')

@section('content')
                <div class="page-header">
                    <div>
                        <nav aria-label="Breadcrumb">
                            <ol class="breadcrumb">
                                <li class="breadcrumb-item"><a href="/app"><i class="bi bi-house-door"></i> Home</a></li>
                                <li class="breadcrumb-item"><a href="/app/inventory-overview">Inventory</a></li>
                                <li class="breadcrumb-item active" aria-current="page">Serial numbers</li>
                            </ol>
                        </nav>
                        <h1 class="page-title">Serial Numbers</h1>
                        <p class="page-subtitle">100 nomor seri terbaru. Cari berdasarkan nomor seri atau nama produk.</p>
                    </div>
                    <div class="page-actions">
                        <a class="btn btn-light" href="/app/products-list"><i class="bi bi-box"></i>Products</a>
                    </div>
                </div>

                <div class="row g-app mb-4">
                    <div class="col-6 col-xl-3">
                        <div class="card kpi-card grad-indigo">
                            <div class="kpi-top"><div class="min-w-0"><div class="kpi-label">Total tercatat</div><div class="kpi-value">{{ number_format($totalCount) }}</div></div><span class="icon-box tone-primary"><i class="bi bi-upc-scan"></i></span></div>
                            <div class="kpi-foot"><span>semua status</span></div>
                        </div>
                    </div>
                    <div class="col-6 col-xl-3">
                        <div class="card kpi-card grad-emerald">
                            <div class="kpi-top"><div class="min-w-0"><div class="kpi-label">Tersedia</div><div class="kpi-value">{{ number_format($inStockCount) }}</div></div><span class="icon-box tone-success"><i class="bi bi-check-circle"></i></span></div>
                            <div class="kpi-foot"><span>status in stock</span></div>
                        </div>
                    </div>
                    <div class="col-6 col-xl-3">
                        <div class="card kpi-card grad-amber">
                            <div class="kpi-top"><div class="min-w-0"><div class="kpi-label">Terpakai</div><div class="kpi-value">{{ number_format($soldCount) }}</div></div><span class="icon-box tone-warning"><i class="bi bi-cart-check"></i></span></div>
                            <div class="kpi-foot"><span>status sold</span></div>
                        </div>
                    </div>
                    <div class="col-6 col-xl-3">
                        <div class="card kpi-card grad-rose">
                            <div class="kpi-top"><div class="min-w-0"><div class="kpi-label">Retur &amp; rusak</div><div class="kpi-value">{{ number_format($returnedCount + $defectiveCount) }}</div></div><span class="icon-box tone-danger"><i class="bi bi-exclamation-triangle"></i></span></div>
                            <div class="kpi-foot"><span>returned + defective</span></div>
                        </div>
                    </div>
                </div>

                <div class="card table-card">
                    <form method="GET" action="/app/serial-numbers" class="table-toolbar">
                        <div class="input-icon">
                            <i class="bi bi-search"></i>
                            <input type="search" name="q" class="form-control" placeholder="Cari serial atau produk…" value="{{ $q }}" aria-label="Cari serial atau produk…">
                        </div>
                        <div class="toolbar-end"><button type="submit" class="btn btn-light"><i class="bi bi-search"></i>Cari</button></div>
                    </form>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead><tr><th>Serial</th><th>Produk</th><th>Outlet</th><th>Status</th><th>Garansi s/d</th></tr></thead>
                            <tbody>
                                @forelse($serials as $s)
                                <tr>
                                    <td><span class="font-mono text-xs">{{ $s->serial_number }}</span></td>
                                    <td>
                                        @if($s->product)
                                        <a class="cell-title" href="/app/product-details?id={{ $s->product->id }}">{{ $s->product->name }}</a><span class="cell-sub">{{ $s->product->sku }}</span>
                                        @else
                                        <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td>{{ $s->outlet->name ?? '-' }}</td>
                                    <td>
                                        @if($s->status === 'in_stock')
                                        <span class="status-badge status-success">Tersedia</span>
                                        @elseif($s->status === 'sold')
                                        <span class="status-badge status-info">Terpakai</span>
                                        @elseif($s->status === 'returned')
                                        <span class="status-badge status-warning">Retur</span>
                                        @else
                                        <span class="status-badge status-danger">{{ $s->status }}</span>
                                        @endif
                                    </td>
                                    <td>{{ $s->warranty_expires_at ? $s->warranty_expires_at->format('d M Y') : '-' }}</td>
                                </tr>
                                @empty
                                <tr><td colspan="5" class="text-center text-muted py-4">Belum ada nomor seri yang cocok dengan pencarian.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
@endsection
