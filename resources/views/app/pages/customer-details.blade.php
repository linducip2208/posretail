@extends('app.layout')
@section('title','Customer Details')


@section('content')
<div class="page-header">
    <div>
        <nav aria-label="Breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="/app"><i class="bi bi-house-door"></i> Home</a></li>
                <li class="breadcrumb-item"><a href="/app/customers-list">Customers</a></li>
                <li class="breadcrumb-item active" aria-current="page">{{ $customer->name }}</li>
            </ol>
        </nav>
        <h1 class="page-title">{{ $customer->name }}</h1>
        <p class="page-subtitle">{{ $customer->email ?? '—' }} · {{ $customer->phone ?? '—' }}</p>
    </div>
    <div class="page-actions">
        <a class="btn btn-light" href="/app/customers-list"><i class="bi bi-arrow-left"></i>Back</a>
        <a class="btn btn-primary" href="/app/pos"><i class="bi bi-upc-scan"></i>Start sale</a>
    </div>
</div>

<div class="row g-app mb-4">
    <div class="col-6 col-xl-3">
        <div class="card kpi-card grad-indigo">
            <div class="kpi-top"><div class="min-w-0"><div class="kpi-label">Total spent</div><div class="kpi-value">{{ tillora_rp($totalSpent) }}</div></div><span class="icon-box tone-primary"><i class="bi bi-wallet2"></i></span></div>
            <div class="kpi-foot"><span>{{ $ordersCount }} completed orders</span></div>
        </div>
    </div>
    <div class="col-6 col-xl-3">
        <div class="card kpi-card grad-lavender">
            <div class="kpi-top"><div class="min-w-0"><div class="kpi-label">Loyalty points</div><div class="kpi-value">{{ number_format($customer->total_points ?? 0) }}</div></div><span class="icon-box tone-violet"><i class="bi bi-gem"></i></span></div>
            <div class="kpi-foot"><span>{{ number_format($pointsEarned) }} earned · {{ number_format($pointsRedeemed) }} redeemed</span></div>
        </div>
    </div>
    <div class="col-6 col-xl-3">
        <div class="card kpi-card grad-mint">
            <div class="kpi-top"><div class="min-w-0"><div class="kpi-label">Group</div><div class="kpi-value fs-5">{{ $customer->customerGroup->name ?? '—' }}</div></div><span class="icon-box tone-success"><i class="bi bi-diagram-3"></i></span></div>
            <div class="kpi-foot"><span>Tier: {{ $customer->membershipTier->name ?? '—' }}</span></div>
        </div>
    </div>
    <div class="col-6 col-xl-3">
        <div class="card kpi-card grad-cyan">
            <div class="kpi-top"><div class="min-w-0"><div class="kpi-label">Deposit balance</div><div class="kpi-value">{{ tillora_rp($customer->deposit_balance ?? 0) }}</div></div><span class="icon-box tone-cyan"><i class="bi bi-piggy-bank"></i></span></div>
            <div class="kpi-foot"><span>{{ $customer->address ?? 'No address' }}</span></div>
        </div>
    </div>
</div>

<div class="row g-app">
    <div class="col-12 col-xl-7">
        <div class="card table-card">
            <div class="table-toolbar"><h2 class="h6 mb-0">Recent orders</h2></div>
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead><tr><th>Order</th><th>Outlet</th><th>Status</th><th class="table-num">Total</th><th>Date</th></tr></thead>
                    <tbody>
                        @forelse($orders as $o)
                        <tr>
                            <td><a class="cell-title" href="/app/sale-details?id={{ $o->id }}">{{ $o->order_number }}</a></td>
                            <td>{{ $o->outlet->name ?? '—' }}</td>
                            <td><span class="status-badge {{ $o->order_status === 'completed' ? 'status-success' : 'status-neutral' }}">{{ ucfirst($o->order_status) }}</span></td>
                            <td class="table-num fw-semibold">{{ tillora_rp($o->total_amount) }}</td>
                            <td>{{ $o->created_at?->format('d M Y') }}</td>
                        </tr>
                        @empty
                        <tr><td colspan="5" class="text-center text-muted py-4">Belum ada order untuk pelanggan ini.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <div class="col-12 col-xl-5">
        <div class="card table-card">
            <div class="table-toolbar"><h2 class="h6 mb-0">Loyalty history</h2></div>
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead><tr><th>Date</th><th class="table-num">Earned</th><th class="table-num">Redeemed</th><th>Note</th></tr></thead>
                    <tbody>
                        @forelse($loyaltyHistory as $l)
                        <tr>
                            <td>{{ $l->created_at?->format('d M Y') }}</td>
                            <td class="table-num">+{{ number_format($l->points_earned) }}</td>
                            <td class="table-num">−{{ number_format($l->points_redeemed) }}</td>
                            <td class="text-muted small">{{ $l->description ?? '—' }}</td>
                        </tr>
                        @empty
                        <tr><td colspan="4" class="text-center text-muted py-4">Belum ada riwayat poin.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
