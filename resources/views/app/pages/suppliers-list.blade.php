@extends('app.layout')
@section('title','Suppliers')


@section('content')
<div class="page-header">
    <div>
        <nav aria-label="Breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="/app"><i class="bi bi-house-door"></i> Home</a></li>
                <li class="breadcrumb-item"><a href="/app/purchase-orders">Purchasing</a></li>
                <li class="breadcrumb-item active" aria-current="page">Suppliers</li>
            </ol>
        </nav>
        <h1 class="page-title">Suppliers</h1>
        <p class="page-subtitle">Supplier directory with orders, debt and ratings.</p>
    </div>
</div>

<div class="row g-app mb-4">
    <div class="col-6 col-xl-4">
        <div class="card kpi-card grad-indigo">
            <div class="kpi-top"><div class="min-w-0"><div class="kpi-label">Suppliers</div><div class="kpi-value">{{ $suppliers->count() }}</div></div><span class="icon-box tone-primary"><i class="bi bi-truck"></i></span></div>
        </div>
    </div>
    <div class="col-6 col-xl-4">
        <div class="card kpi-card grad-cyan">
            <div class="kpi-top"><div class="min-w-0"><div class="kpi-label">Total outstanding debt</div><div class="kpi-value">{{ tillora_rp($totalDebt) }}</div></div><span class="icon-box tone-cyan"><i class="bi bi-cash-stack"></i></span></div>
            <div class="kpi-foot"><span>unpaid payables</span></div>
        </div>
    </div>
</div>

<div class="card table-card" data-table data-page-size="10">
    <div class="table-toolbar">
        <div class="input-icon">
            <i class="bi bi-search"></i>
            <input type="search" class="form-control" placeholder="Search supplier…" data-table-search aria-label="Search suppliers">
        </div>
    </div>
    <div class="table-responsive">
        <table class="table table-hover align-middle">
            <thead><tr><th class="col-main">Supplier</th><th>Contact</th><th class="table-num">POs</th><th class="table-num">Debt</th><th class="table-num">Rating</th><th class="col-actions"></th></tr></thead>
            <tbody>
                @forelse($suppliers as $s)
                @php
                    $debt = ($s->payables_total ?? 0) - ($s->payables_paid ?? 0);
                    $rating = $latestRatings[$s->id] ?? null;
                @endphp
                <tr>
                    <td><div class="cell-user"><span class="avatar">{{ strtoupper(substr($s->name,0,1)) }}</span><div><a class="cell-title" href="/app/supplier-details?id={{ $s->id }}">{{ $s->name }}</a><span class="cell-sub">{{ $s->active ? 'Active' : 'Inactive' }}</span></div></div></td>
                    <td>{{ $s->contact_person ?? '—' }}<small class="d-block text-muted">{{ $s->phone ?? '—' }}</small></td>
                    <td class="table-num">{{ number_format($s->purchase_orders_count) }}</td>
                    <td class="table-num fw-semibold">{{ tillora_rp(max($debt, 0)) }}</td>
                    <td class="table-num">{{ $rating ? number_format($rating->avg_score, 1).' / 5' : '—' }}</td>
                    <td class="col-actions"><a class="btn btn-ghost btn-icon btn-sm" href="/app/supplier-details?id={{ $s->id }}" aria-label="View details"><i class="bi bi-eye"></i></a></td>
                </tr>
                @empty
                <tr><td colspan="6" class="text-center text-muted py-4">Belum ada supplier.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
