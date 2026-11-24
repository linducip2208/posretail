@extends('app.layout')
@section('title','Customer Groups')


@section('content')
<div class="page-header">
    <div>
        <nav aria-label="Breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="/app"><i class="bi bi-house-door"></i> Home</a></li>
                <li class="breadcrumb-item"><a href="/app/customers-list">People</a></li>
                <li class="breadcrumb-item active" aria-current="page">Customer Groups</li>
            </ol>
        </nav>
        <h1 class="page-title">Customer Groups</h1>
        <p class="page-subtitle">Segments with member counts and lifetime spend.</p>
    </div>
    <div class="page-actions">
        <a class="btn btn-light" href="/app/customers-list"><i class="bi bi-arrow-left"></i>Customers</a>
    </div>
</div>

<div class="row g-app mb-4">
    <div class="col-6 col-xl-4">
        <div class="card kpi-card grad-indigo">
            <div class="kpi-top"><div class="min-w-0"><div class="kpi-label">Groups</div><div class="kpi-value">{{ $groups->count() }}</div></div><span class="icon-box tone-primary"><i class="bi bi-diagram-3"></i></span></div>
        </div>
    </div>
    <div class="col-6 col-xl-4">
        <div class="card kpi-card grad-mint">
            <div class="kpi-top"><div class="min-w-0"><div class="kpi-label">Total members</div><div class="kpi-value">{{ number_format($totalMembers) }}</div></div><span class="icon-box tone-success"><i class="bi bi-people"></i></span></div>
        </div>
    </div>
    <div class="col-6 col-xl-4">
        <div class="card kpi-card grad-cyan">
            <div class="kpi-top"><div class="min-w-0"><div class="kpi-label">Combined spend</div><div class="kpi-value">{{ tillora_rp($totalSpentAll) }}</div></div><span class="icon-box tone-cyan"><i class="bi bi-wallet2"></i></span></div>
        </div>
    </div>
</div>

<div class="row g-app">
    @forelse($groups as $g)
    <div class="col-12 col-md-6 col-xl-4">
        <div class="card">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start mb-2">
                    <h2 class="h6 mb-0">{{ $g->name }}</h2>
                    <span class="status-badge status-primary no-dot">{{ $g->discount_percent }}% off</span>
                </div>
                <p class="text-muted small">{{ $g->description ?? '—' }}</p>
                <div class="rank-bar mb-2"><span style="width: {{ $totalMembers > 0 ? round($g->customers_count / max($totalMembers,1) * 100) : 0 }}%"></span></div>
                <ul class="legend-list">
                    <li><span>Members</span><strong>{{ number_format($g->customers_count) }}</strong></li>
                    <li><span>Total spent</span><strong>{{ tillora_rp($g->customers_sum_total_spent ?? 0) }}</strong></li>
                    <li><span>Min. spend</span><span class="text-muted">{{ tillora_rp($g->min_spent ?? 0) }}</span></li>
                </ul>
            </div>
        </div>
    </div>
    @empty
    <div class="col-12"><div class="card"><div class="card-body text-center text-muted py-4">Belum ada grup pelanggan.</div></div></div>
    @endforelse
</div>
@endsection
