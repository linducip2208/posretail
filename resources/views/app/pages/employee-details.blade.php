@extends('app.layout')
@section('title','Employee Details')


@section('content')
<div class="page-header">
    <div>
        <nav aria-label="Breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="/app"><i class="bi bi-house-door"></i> Home</a></li>
                <li class="breadcrumb-item"><a href="/app/employees-list">Employees</a></li>
                <li class="breadcrumb-item active" aria-current="page">{{ $user->name }}</li>
            </ol>
        </nav>
        <h1 class="page-title">{{ $user->name }}</h1>
        <p class="page-subtitle">{{ $user->email }} · {{ $user->roles->pluck('name')->join(', ') ?: ($user->role ?? '—') }}</p>
    </div>
    <div class="page-actions">
        <a class="btn btn-light" href="/app/employees-list"><i class="bi bi-arrow-left"></i>Back</a>
    </div>
</div>

<div class="row g-app mb-4">
    <div class="col-6 col-xl-3">
        <div class="card kpi-card grad-indigo">
            <div class="kpi-top"><div class="min-w-0"><div class="kpi-label">Total sales</div><div class="kpi-value">{{ tillora_rp($totalSales) }}</div></div><span class="icon-box tone-primary"><i class="bi bi-cart-check"></i></span></div>
            <div class="kpi-foot"><span>{{ $salesCount }} completed orders</span></div>
        </div>
    </div>
    <div class="col-6 col-xl-3">
        <div class="card kpi-card grad-mint">
            <div class="kpi-top"><div class="min-w-0"><div class="kpi-label">Commission</div><div class="kpi-value">{{ tillora_rp($totalCommission) }}</div></div><span class="icon-box tone-success"><i class="bi bi-percent"></i></span></div>
            <div class="kpi-foot"><span>{{ $user->commission_percent ?? 0 }}% rate</span></div>
        </div>
    </div>
    <div class="col-6 col-xl-3">
        <div class="card kpi-card grad-lavender">
            <div class="kpi-top"><div class="min-w-0"><div class="kpi-label">Outlet</div><div class="kpi-value fs-6">{{ $user->outlets->pluck('name')->join(', ') ?: '—' }}</div></div><span class="icon-box tone-violet"><i class="bi bi-shop"></i></span></div>
            <div class="kpi-foot"><span>{{ $user->active ? 'Active' : 'Inactive' }}</span></div>
        </div>
    </div>
    <div class="col-6 col-xl-3">
        <div class="card kpi-card grad-cyan">
            <div class="kpi-top"><div class="min-w-0"><div class="kpi-label">Current shift</div><div class="kpi-value fs-6">{{ $openShift ? 'Open since '.$openShift->started_at?->format('H:i') : 'No open shift' }}</div></div><span class="icon-box tone-cyan"><i class="bi bi-clock-history"></i></span></div>
            <div class="kpi-foot"><span>{{ $openShift->outlet->name ?? '' }}</span></div>
        </div>
    </div>
</div>

<div class="card table-card">
    <div class="table-toolbar"><h2 class="h6 mb-0">Recent shifts</h2></div>
    <div class="table-responsive">
        <table class="table table-hover align-middle">
            <thead><tr><th>Started</th><th>Ended</th><th>Outlet</th><th>Status</th><th class="table-num">Difference</th></tr></thead>
            <tbody>
                @forelse($shifts as $s)
                <tr>
                    <td>{{ $s->started_at?->format('d M Y H:i') }}</td>
                    <td>{{ $s->ended_at?->format('d M Y H:i') ?? '—' }}</td>
                    <td>{{ $s->outlet->name ?? '—' }}</td>
                    <td><span class="status-badge {{ $s->status === 'open' ? 'status-success' : 'status-neutral' }}">{{ ucfirst($s->status) }}</span></td>
                    <td class="table-num">{{ tillora_rp($s->difference ?? 0) }}</td>
                </tr>
                @empty
                <tr><td colspan="5" class="text-center text-muted py-4">Belum ada riwayat shift.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
