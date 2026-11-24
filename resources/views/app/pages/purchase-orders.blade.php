@extends('app.layout')
@section('title','Purchase Orders')


@section('content')
<div class="page-header">
    <div>
        <nav aria-label="Breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="/app"><i class="bi bi-house-door"></i> Home</a></li>
                <li class="breadcrumb-item"><a href="/app/purchase-orders">Purchasing</a></li>
                <li class="breadcrumb-item active" aria-current="page">Purchase Orders</li>
            </ol>
        </nav>
        <h1 class="page-title">Purchase Orders</h1>
        <p class="page-subtitle">Latest {{ $pos->count() }} purchase orders with status summary.</p>
    </div>
    <div class="page-actions">
        <a class="btn btn-primary" href="/app/purchase-create"><i class="bi bi-plus-lg"></i>New order</a>
    </div>
</div>

<div class="row g-app mb-4">
    @forelse($statusSummary as $status => $count)
    <div class="col-6 col-xl-3">
        <div class="card kpi-card">
            <div class="kpi-top"><div class="min-w-0"><div class="kpi-label">{{ ucfirst($status) }}</div><div class="kpi-value">{{ number_format($count) }}</div></div><span class="icon-box tone-primary"><i class="bi bi-receipt"></i></span></div>
        </div>
    </div>
    @empty
    <div class="col-12"><div class="card"><div class="card-body text-center text-muted">Belum ada purchase order.</div></div></div>
    @endforelse
</div>

<div class="card table-card" data-table data-page-size="10">
    <div class="table-toolbar">
        <div class="input-icon">
            <i class="bi bi-search"></i>
            <input type="search" class="form-control" placeholder="Search PO number, supplier…" data-table-search aria-label="Search purchase orders">
        </div>
    </div>
    <div class="table-responsive">
        <table class="table table-hover align-middle">
            <thead><tr><th>PO Number</th><th>Supplier</th><th>Outlet</th><th>Status</th><th class="table-num">Total</th><th>Date</th><th class="col-actions"></th></tr></thead>
            <tbody>
                @forelse($pos as $po)
                <tr>
                    <td><a class="cell-title" href="/app/purchase-order-details?id={{ $po->id }}">{{ $po->po_number }}</a></td>
                    <td>{{ $po->supplier->name ?? '—' }}</td>
                    <td>{{ $po->outlet->name ?? '—' }}</td>
                    <td><span class="status-badge {{ $po->status === 'received' ? 'status-success' : ($po->status === 'cancelled' ? 'status-danger' : 'status-warning') }}">{{ ucfirst($po->status) }}</span></td>
                    <td class="table-num fw-semibold">{{ tillora_rp($po->total_amount) }}</td>
                    <td>{{ $po->created_at?->format('d M Y') }}</td>
                    <td class="col-actions"><a class="btn btn-ghost btn-icon btn-sm" href="/app/purchase-order-details?id={{ $po->id }}" aria-label="View details"><i class="bi bi-eye"></i></a></td>
                </tr>
                @empty
                <tr><td colspan="7" class="text-center text-muted py-4">Belum ada purchase order.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
