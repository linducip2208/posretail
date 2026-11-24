@extends('app.layout')
@section('title','Stores')


@section('content')
<div class="page-header">
    <div>
        <nav aria-label="Breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="/app"><i class="bi bi-house-door"></i> Home</a></li>
                <li class="breadcrumb-item active" aria-current="page">Stores</li>
            </ol>
        </nav>
        <h1 class="page-title">Stores</h1>
        <p class="page-subtitle">Outlet performance for {{ $month }}/{{ $year }} — {{ tillora_rp($totalRevenue) }} from {{ number_format($totalTrx) }} transactions.</p>
    </div>
</div>

<div class="row g-app">
    @forelse($stores as $s)
    @php
        $pct = $s['target'] > 0 ? min(100, round($s['revenue'] / $s['target'] * 100)) : 0;
    @endphp
    <div class="col-12 col-md-6 col-xl-4">
        <div class="card">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start mb-2">
                    <h2 class="h6 mb-0">{{ $s['outlet']->name }}</h2>
                    <span class="status-badge status-success no-dot">{{ $s['outlet']->code }}</span>
                </div>
                <p class="text-muted small mb-3">{{ $s['outlet']->address ?? '—' }} · {{ $s['outlet']->phone ?? '—' }}</p>
                <div class="rank-bar mb-2"><span style="width: {{ $pct }}%"></span></div>
                <ul class="legend-list">
                    <li><span>Revenue MTD</span><strong>{{ tillora_rp($s['revenue']) }}</strong></li>
                    <li><span>Transactions</span><strong>{{ number_format($s['trx']) }}</strong></li>
                    <li><span>Target</span><span class="text-muted">{{ $s['target'] > 0 ? tillora_rp($s['target']).' ('.$pct.'%)' : 'No target set' }}</span></li>
                </ul>
            </div>
        </div>
    </div>
    @empty
    <div class="col-12"><div class="card"><div class="card-body text-center text-muted py-4">Belum ada toko aktif.</div></div></div>
    @endforelse
</div>
@endsection
