@extends('app.layout')
@section('title','Inventory History')

@section('content')

                <div class="page-header">
                    <div>
                        <nav aria-label="Breadcrumb">
                            <ol class="breadcrumb">
                                <li class="breadcrumb-item"><a href="/app"><i class="bi bi-house-door"></i> Home</a></li>
                                <li class="breadcrumb-item"><a href="/app/inventory-overview">Inventory</a></li>
                                <li class="breadcrumb-item active" aria-current="page">History</li>
                            </ol>
                        </nav>
                        <h1 class="page-title">Inventory History</h1>
                        <p class="page-subtitle">Every stock movement in and out, latest 100 records.</p>
                    </div>
                    <div class="page-actions">
                        <a class="btn btn-light" href="/app/stock-levels"><i class="bi bi-boxes"></i>Stock levels</a>
                    </div>
                </div>

                <div class="row g-app mb-4">
                    <div class="col-6">
                        <div class="card kpi-card grad-mint">
                            <div class="kpi-top"><div class="min-w-0"><div class="kpi-label">Total in</div><div class="kpi-value">+{{ number_format($inQty) }}</div></div><span class="icon-box tone-success"><i class="bi bi-arrow-down-circle"></i></span></div>
                            <div class="kpi-foot"><span>units received</span></div>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="card kpi-card grad-rose">
                            <div class="kpi-top"><div class="min-w-0"><div class="kpi-label">Total out</div><div class="kpi-value">-{{ number_format($outQty) }}</div></div><span class="icon-box tone-danger"><i class="bi bi-arrow-up-circle"></i></span></div>
                            <div class="kpi-foot"><span>units issued</span></div>
                        </div>
                    </div>
                </div>

                <div class="card table-card">
                    <div class="card-header"><h2 class="card-title h6 mb-0">Latest movements</h2></div>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead><tr><th>Date</th><th>Product</th><th>Type</th><th class="table-num">Qty</th><th>Outlet</th><th>Reference</th></tr></thead>
                            <tbody>
                                @forelse($movements as $m)
                                <tr>
                                    <td class="text-muted small">{{ $m->created_at?->format('d M Y H:i') }}</td>
                                    <td>@if($m->product)<a class="cell-title" href="/app/product-details?id={{ $m->product->id }}">{{ $m->product->name }}</a>@if($m->productVariant)<span class="cell-sub">{{ $m->productVariant->name }}</span>@endif@else<span class="text-muted">-</span>@endif</td>
                                    <td><span class="status-badge {{ $m->type === 'in' ? 'status-success' : 'status-danger' }}">{{ $m->type === 'in' ? 'In' : 'Out' }}</span></td>
                                    <td class="table-num fw-semibold">{{ $m->type === 'out' ? '-' : '+' }}{{ number_format((int)$m->quantity) }}</td>
                                    <td>{{ $m->outlet->name ?? '-' }}</td>
                                    <td class="text-muted small">{{ $m->reference_type ? $m->reference_type.' #'.$m->reference_id : ($m->notes ?? '-') }}</td>
                                </tr>
                                @empty
                                <tr><td colspan="6" class="text-center text-muted py-4">Belum ada riwayat mutasi stok.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
@endsection
