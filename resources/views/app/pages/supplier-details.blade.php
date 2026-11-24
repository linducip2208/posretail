@extends('app.layout')
@section('title','Supplier Details')


@section('content')
<div class="page-header">
    <div>
        <nav aria-label="Breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="/app"><i class="bi bi-house-door"></i> Home</a></li>
                <li class="breadcrumb-item"><a href="/app/suppliers-list">Suppliers</a></li>
                <li class="breadcrumb-item active" aria-current="page">{{ $supplier->name }}</li>
            </ol>
        </nav>
        <h1 class="page-title">{{ $supplier->name }}</h1>
        <p class="page-subtitle">{{ $supplier->contact_person ?? '—' }} · {{ $supplier->phone ?? '—' }} · {{ $supplier->email ?? '—' }}</p>
    </div>
    <div class="page-actions">
        <a class="btn btn-light" href="/app/suppliers-list"><i class="bi bi-arrow-left"></i>Back</a>
    </div>
</div>

<div class="row g-app mb-4">
    <div class="col-6 col-xl-3">
        <div class="card kpi-card grad-indigo">
            <div class="kpi-top"><div class="min-w-0"><div class="kpi-label">Purchase orders</div><div class="kpi-value">{{ number_format($poCount) }}</div></div><span class="icon-box tone-primary"><i class="bi bi-receipt"></i></span></div>
            <div class="kpi-foot"><span>{{ tillora_rp($poTotal) }} total value</span></div>
        </div>
    </div>
    <div class="col-6 col-xl-3">
        <div class="card kpi-card grad-cyan">
            <div class="kpi-top"><div class="min-w-0"><div class="kpi-label">Outstanding debt</div><div class="kpi-value">{{ tillora_rp($debt) }}</div></div><span class="icon-box tone-cyan"><i class="bi bi-cash-stack"></i></span></div>
            <div class="kpi-foot"><span>{{ $payables->count() }} payables shown</span></div>
        </div>
    </div>
    <div class="col-6 col-xl-3">
        <div class="card kpi-card grad-mint">
            <div class="kpi-top"><div class="min-w-0"><div class="kpi-label">Contracts</div><div class="kpi-value">{{ $contracts->count() }}</div></div><span class="icon-box tone-success"><i class="bi bi-file-earmark-text"></i></span></div>
        </div>
    </div>
    <div class="col-6 col-xl-3">
        <div class="card kpi-card grad-lavender">
            <div class="kpi-top"><div class="min-w-0"><div class="kpi-label">Latest rating</div><div class="kpi-value">{{ $ratings->first() ? number_format($ratings->first()->avg_score, 1).' / 5' : '—' }}</div></div><span class="icon-box tone-violet"><i class="bi bi-star"></i></span></div>
        </div>
    </div>
</div>

<div class="row g-app">
    <div class="col-12 col-xl-6">
        <div class="card table-card">
            <div class="table-toolbar"><h2 class="h6 mb-0">Recent purchase orders</h2></div>
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead><tr><th>PO</th><th>Status</th><th class="table-num">Total</th><th>Date</th></tr></thead>
                    <tbody>
                        @forelse($pos as $po)
                        <tr>
                            <td><a class="cell-title" href="/app/purchase-order-details?id={{ $po->id }}">{{ $po->po_number }}</a></td>
                            <td><span class="status-badge {{ $po->status === 'received' ? 'status-success' : 'status-warning' }}">{{ ucfirst($po->status) }}</span></td>
                            <td class="table-num fw-semibold">{{ tillora_rp($po->total_amount) }}</td>
                            <td>{{ $po->created_at?->format('d M Y') }}</td>
                        </tr>
                        @empty
                        <tr><td colspan="4" class="text-center text-muted py-4">Belum ada PO dari supplier ini.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        <div class="card table-card mt-4">
            <div class="table-toolbar"><h2 class="h6 mb-0">Payables</h2></div>
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead><tr><th>Invoice</th><th>Status</th><th class="table-num">Total</th><th class="table-num">Paid</th><th>Due</th></tr></thead>
                    <tbody>
                        @forelse($payables as $p)
                        <tr>
                            <td>{{ $p->invoice_number ?? '—' }}</td>
                            <td><span class="status-badge {{ $p->status === 'paid' ? 'status-success' : 'status-warning' }}">{{ ucfirst($p->status) }}</span></td>
                            <td class="table-num">{{ tillora_rp($p->total_amount) }}</td>
                            <td class="table-num">{{ tillora_rp($p->paid_amount) }}</td>
                            <td>{{ $p->due_date?->format('d M Y') ?? '—' }}</td>
                        </tr>
                        @empty
                        <tr><td colspan="5" class="text-center text-muted py-4">Belum ada hutang tercatat.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <div class="col-12 col-xl-6">
        <div class="card table-card">
            <div class="table-toolbar"><h2 class="h6 mb-0">Contracts</h2></div>
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead><tr><th>Contract</th><th>Status</th><th class="table-num">Value</th><th>Period</th></tr></thead>
                    <tbody>
                        @forelse($contracts as $ct)
                        <tr>
                            <td>{{ $ct->contract_number }}</td>
                            <td><span class="status-badge status-neutral">{{ ucfirst($ct->status ?? '—') }}</span></td>
                            <td class="table-num">{{ tillora_rp($ct->value ?? 0) }}</td>
                            <td>{{ $ct->start_date?->format('d M Y') }} – {{ $ct->end_date?->format('d M Y') }}</td>
                        </tr>
                        @empty
                        <tr><td colspan="4" class="text-center text-muted py-4">Belum ada kontrak.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        <div class="card table-card mt-4">
            <div class="table-toolbar"><h2 class="h6 mb-0">Ratings</h2></div>
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead><tr><th>Date</th><th class="table-num">Score</th><th>Note</th></tr></thead>
                    <tbody>
                        @forelse($ratings as $rt)
                        <tr>
                            <td>{{ $rt->created_at?->format('d M Y') }}</td>
                            <td class="table-num fw-semibold">{{ number_format($rt->avg_score, 1) }} / 5</td>
                            <td class="text-muted small">{{ $rt->notes ?? '—' }}</td>
                        </tr>
                        @empty
                        <tr><td colspan="3" class="text-center text-muted py-4">Belum ada penilaian.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
