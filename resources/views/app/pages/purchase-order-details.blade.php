@extends('app.layout')
@section('title','Purchase Order Details')


@section('content')
<div class="page-header">
    <div>
        <nav aria-label="Breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="/app"><i class="bi bi-house-door"></i> Home</a></li>
                <li class="breadcrumb-item"><a href="/app/purchase-orders">Purchase Orders</a></li>
                <li class="breadcrumb-item active" aria-current="page">{{ $po->po_number }}</li>
            </ol>
        </nav>
        <h1 class="page-title">{{ $po->po_number }}</h1>
        <p class="page-subtitle">{{ $po->supplier->name ?? '—' }} · {{ $po->created_at?->format('d M Y') }}</p>
    </div>
    <div class="page-actions">
        <a class="btn btn-light" href="/app/purchase-orders"><i class="bi bi-arrow-left"></i>Back</a>
    </div>
</div>

<div class="row g-app mb-4">
    <div class="col-6 col-xl-3">
        <div class="card kpi-card grad-indigo">
            <div class="kpi-top"><div class="min-w-0"><div class="kpi-label">Total amount</div><div class="kpi-value">{{ tillora_rp($po->total_amount) }}</div></div><span class="icon-box tone-primary"><i class="bi bi-receipt"></i></span></div>
            <div class="kpi-foot"><span>{{ $po->purchaseOrderItems->sum('quantity') }} items qty</span></div>
        </div>
    </div>
    <div class="col-6 col-xl-3">
        <div class="card kpi-card grad-mint">
            <div class="kpi-top"><div class="min-w-0"><div class="kpi-label">Status</div><div class="kpi-value fs-5">{{ ucfirst($po->status) }}</div></div><span class="icon-box tone-success"><i class="bi bi-flag"></i></span></div>
            <div class="kpi-foot"><span>Outlet: {{ $po->outlet->name ?? '—' }}</span></div>
        </div>
    </div>
    <div class="col-12 col-xl-6">
        <div class="card"><div class="card-body">
            <h2 class="h6 mb-3">Status timeline</h2>
            @if($po->status === 'cancelled')
                <span class="status-badge status-danger">Cancelled</span>
            @else
                <ol class="d-flex gap-3 list-unstyled mb-0">
                    @foreach($steps as $i => $step)
                        <li class="d-flex align-items-center gap-2">
                            <span class="status-badge {{ $i <= $currentStep ? 'status-success' : 'status-neutral' }}">{{ ucfirst($step) }}</span>
                            @if(!$loop->last)<i class="bi bi-chevron-right text-muted"></i>@endif
                        </li>
                    @endforeach
                </ol>
            @endif
            @if($po->notes)<p class="text-muted small mt-3 mb-0">{{ $po->notes }}</p>@endif
        </div></div>
    </div>
</div>

<div class="card table-card">
    <div class="table-toolbar"><h2 class="h6 mb-0">Items</h2></div>
    <div class="table-responsive">
        <table class="table table-hover align-middle">
            <thead><tr><th>Product</th><th class="table-num">Qty</th><th class="table-num">Unit price</th><th class="table-num">Subtotal</th></tr></thead>
            <tbody>
                @forelse($po->purchaseOrderItems as $it)
                <tr>
                    <td><span class="cell-title">{{ $it->product->name ?? 'Product #'.$it->product_id }}</span><small class="d-block text-muted">{{ $it->product->sku ?? '' }}</small></td>
                    <td class="table-num">{{ number_format($it->quantity) }}</td>
                    <td class="table-num">{{ tillora_rp($it->unit_price) }}</td>
                    <td class="table-num fw-semibold">{{ tillora_rp($it->subtotal) }}</td>
                </tr>
                @empty
                <tr><td colspan="4" class="text-center text-muted py-4">Tidak ada item pada PO ini.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
