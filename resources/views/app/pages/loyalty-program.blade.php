@extends('app.layout')
@section('title','Loyalty Program')

@section('content')
<div class="page-header">
    <div>
        <nav aria-label="Breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="/app"><i class="bi bi-house-door"></i> Home</a></li>
                <li class="breadcrumb-item"><a href="/app/customers-list">People</a></li>
                <li class="breadcrumb-item active" aria-current="page">Loyalty Program</li>
            </ol>
        </nav>
        <h1 class="page-title">Loyalty Program</h1>
        <p class="page-subtitle">Membership tiers, rewards and circulating points.</p>
    </div>
</div>

<div class="row g-app mb-4">
    <div class="col-6 col-xl-4">
        <div class="card kpi-card grad-indigo">
            <div class="kpi-top"><div class="min-w-0"><div class="kpi-label">Active members</div><div class="kpi-value">{{ number_format($activeMembers) }}</div></div><span class="icon-box tone-primary"><i class="bi bi-people"></i></span></div>
            <div class="kpi-foot"><span>customers holding points</span></div>
        </div>
    </div>
    <div class="col-6 col-xl-4">
        <div class="card kpi-card grad-lavender">
            <div class="kpi-top"><div class="min-w-0"><div class="kpi-label">Points in circulation</div><div class="kpi-value">{{ number_format($totalPoints) }}</div></div><span class="icon-box tone-violet"><i class="bi bi-gem"></i></span></div>
            <div class="kpi-foot"><span>sum of customer balances</span></div>
        </div>
    </div>
    <div class="col-6 col-xl-4">
        <div class="card kpi-card grad-mint">
            <div class="kpi-top"><div class="min-w-0"><div class="kpi-label">Most popular tier</div><div class="kpi-value fs-5">{{ $popularTier->name ?? '—' }}</div></div><span class="icon-box tone-success"><i class="bi bi-trophy"></i></span></div>
            <div class="kpi-foot"><span>{{ number_format($popularTier->customers_count ?? 0) }} members</span></div>
        </div>
    </div>
</div>

<div class="row g-app">
    <div class="col-12 col-xl-6">
        <div class="card table-card">
            <div class="table-toolbar"><h2 class="h6 mb-0">Membership tiers</h2></div>
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead><tr><th>Tier</th><th class="table-num">Min. spent</th><th class="table-num">Members</th><th class="table-num">Multiplier</th></tr></thead>
                    <tbody>
                        @forelse($tiers as $t)
                        <tr>
                            <td><span class="cell-title">{{ $t->name }}</span><small class="d-block text-muted">{{ $t->discount_percent }}% discount</small></td>
                            <td class="table-num">Rp {{ number_format($t->min_spent,0,',','.') }}</td>
                            <td class="table-num">{{ number_format($t->customers_count) }}</td>
                            <td class="table-num">×{{ $t->point_multiplier }}</td>
                        </tr>
                        @empty
                        <tr><td colspan="4" class="text-center text-muted py-4">Belum ada tier keanggotaan.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <div class="col-12 col-xl-6">
        <div class="card table-card">
            <div class="table-toolbar"><h2 class="h6 mb-0">Rewards</h2></div>
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead><tr><th>Reward</th><th class="table-num">Points</th><th>Status</th></tr></thead>
                    <tbody>
                        @forelse($rewards as $rw)
                        <tr>
                            <td><span class="cell-title">{{ $rw->name }}</span><small class="d-block text-muted">{{ $rw->discount_type }} · {{ $rw->discount_value }}</small></td>
                            <td class="table-num">{{ number_format($rw->points_required) }}</td>
                            <td><span class="status-badge {{ $rw->active ? 'status-success' : 'status-neutral' }}">{{ $rw->active ? 'Active' : 'Inactive' }}</span></td>
                        </tr>
                        @empty
                        <tr><td colspan="3" class="text-center text-muted py-4">Belum ada reward loyalitas.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($tiers->isNotEmpty())
            <div class="card-body">
                <canvas id="tierChart" height="120"></canvas>
            </div>
            @endif
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
(function(){
    if (!window.Chart) return;
    var el = document.getElementById('tierChart');
    if (!el) return;
    new Chart(el, {
        type: 'bar',
        data: {
            labels: @json($tiers->pluck('name')),
            datasets: [{ label: 'Members', data: @json($tiers->pluck('customers_count')), backgroundColor: '#6366f1' }]
        },
        options: { responsive: true, plugins: { legend: { display: false } } }
    });
})();
</script>
@endpush
