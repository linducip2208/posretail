@extends('app.layout')
@section('title','Registers')


@section('content')
                <div class="page-header">
                    <div>
                        <nav aria-label="Breadcrumb">
                            <ol class="breadcrumb">
                                <li class="breadcrumb-item"><a href="/app"><i class="bi bi-house-door"></i> Home</a></li>
                                <li class="breadcrumb-item"><a href="/app/sales-list">Sales</a></li>
                                <li class="breadcrumb-item active" aria-current="page">Registers</li>
                            </ol>
                        </nav>
                        <h1 class="page-title">Registers</h1>
                        <p class="page-subtitle">Status register per outlet dari database — shift open dan revenue hari ini.</p>
                    </div>
                    <div class="page-actions">
                        <a class="btn btn-primary" href="/pos"><i class="bi bi-upc-scan"></i>Buka Kasir</a>
                    </div>
                </div>

                <div class="row g-app mb-4">
                    <div class="col-6 col-xl-4">
                        <div class="card kpi-card grad-indigo">
                            <div class="kpi-top"><div class="min-w-0"><div class="kpi-label">Total outlet</div><div class="kpi-value">{{ $registers->count() }}</div></div><span class="icon-box tone-primary"><i class="bi bi-shop"></i></span></div>
                            <div class="kpi-foot"><span>{{ $registers->where('open', true)->count() }} register open</span></div>
                        </div>
                    </div>
                    <div class="col-6 col-xl-4">
                        <div class="card kpi-card grad-cyan">
                            <div class="kpi-top"><div class="min-w-0"><div class="kpi-label">Revenue hari ini</div><div class="kpi-value">{{ tillora_rp($totalRevenueToday) }}</div></div><span class="icon-box tone-cyan"><i class="bi bi-currency-dollar"></i></span></div>
                            <div class="kpi-foot"><span>semua outlet digabung</span></div>
                        </div>
                    </div>
                    <div class="col-6 col-xl-4">
                        <div class="card kpi-card grad-amber">
                            <div class="kpi-top"><div class="min-w-0"><div class="kpi-label">Transaksi hari ini</div><div class="kpi-value">{{ $registers->sum('count_today') }}</div></div><span class="icon-box tone-warning"><i class="bi bi-receipt"></i></span></div>
                            <div class="kpi-foot"><span>order hari ini semua outlet</span></div>
                        </div>
                    </div>
                </div>

                <div class="row g-app">
                    @forelse($registers as $reg)
                    <div class="col-md-6 col-xl-4">
                        <div class="card mb-4">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-start mb-3">
                                    <div class="cell-user"><span class="avatar tone-primary"><i class="bi bi-shop"></i></span><div><span class="cell-title">{{ $reg['outlet']->name }}</span><span class="cell-sub">{{ $reg['outlet']->code ?? '' }} · {{ $reg['outlet']->address ?? '' }}</span></div></div>
                                    <span class="status-badge {{ $reg['open'] ? 'status-success' : 'status-danger' }}">{{ $reg['open'] ? 'Open' : 'Closed' }}</span>
                                </div>
                                <ul class="legend-list">
                                    <li><span>Shift aktif</span><strong>{{ $reg['shift'] ? 'Shift #'.$reg['shift']->id.' · '.(optional($reg['shift']->user)->name ?? '—') : 'Tidak ada' }}</strong></li>
                                    <li><span>Revenue hari ini</span><strong>{{ tillora_rp($reg['revenue_today']) }}</strong></li>
                                    <li><span>Transaksi hari ini</span><strong>{{ $reg['count_today'] }}</strong></li>
                                </ul>
                                <div class="rank-bar mt-3"><span style="width: {{ $totalRevenueToday > 0 ? round($reg['revenue_today'] / $totalRevenueToday * 100) : 0 }}%;"></span></div>
                                <div class="d-flex gap-2 mt-3">
                                    <a class="btn btn-light btn-sm" href="/app/shift-management"><i class="bi bi-clock-history"></i>Shifts</a>
                                    <a class="btn btn-primary btn-sm" href="/pos"><i class="bi bi-upc-scan"></i>Kasir</a>
                                </div>
                            </div>
                        </div>
                    </div>
                    @empty
                    <div class="col-12"><div class="card"><div class="card-body text-center text-muted py-4">Belum ada outlet terdaftar.</div></div></div>
                    @endforelse
                </div>
@endsection

@push('scripts')
<script>
(function(){
  if (!window.Chart) return;
  Chart.defaults.font.family = 'Inter, system-ui, sans-serif';
})();
</script>
@endpush
