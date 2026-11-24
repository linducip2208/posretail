@extends('app.layout')
@section('title','Marketplace Orders')


@section('content')
<div class="page-header">
    <div>
        <nav aria-label="Breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="/app"><i class="bi bi-house-door"></i> Home</a></li>
                <li class="breadcrumb-item"><a href="/app/marketplace-orders">Sales</a></li>
                <li class="breadcrumb-item active" aria-current="page">Marketplace Orders</li>
            </ol>
        </nav>
        <h1 class="page-title">Marketplace orders</h1>
        <p class="page-subtitle">Pesanan dari semua marketplace — tersinkron dari database.</p>
    </div>
    <div class="page-actions">
    </div>
</div>

<div class="row g-app mb-4">
    <div class="col-6 col-xl-3">
        <div class="card kpi-card grad-indigo">
            <div class="kpi-top">
                <div class="min-w-0">
                    <div class="kpi-label">Total pesanan</div>
                    <div class="kpi-value">{{ number_format($totalCount) }}</div>
                </div>
                <span class="icon-box tone-primary"><i class="bi bi-bag"></i></span>
            </div>
            <div class="kpi-foot"><span>{{ $platformCount }} platform terhubung</span></div>
        </div>
    </div>
    <div class="col-6 col-xl-3">
        <div class="card kpi-card grad-cyan">
            <div class="kpi-top">
                <div class="min-w-0">
                    <div class="kpi-label">Nilai pesanan</div>
                    <div class="kpi-value">{{ tillora_rp($totalValue) }}</div>
                </div>
                <span class="icon-box tone-cyan"><i class="bi bi-currency-dollar"></i></span>
            </div>
            <div class="kpi-foot"><span>akumulasi semua marketplace</span></div>
        </div>
    </div>
    <div class="col-6 col-xl-3">
        <div class="card kpi-card grad-amber">
            <div class="kpi-top">
                <div class="min-w-0">
                    <div class="kpi-label">Platform aktif</div>
                    <div class="kpi-value">{{ $platformCount }}</div>
                </div>
                <span class="icon-box tone-warning"><i class="bi bi-shop"></i></span>
            </div>
            <div class="kpi-foot"><span>berdasarkan data pesanan</span></div>
        </div>
    </div>
    <div class="col-6 col-xl-3">
        <div class="card kpi-card grad-rose">
            <div class="kpi-top">
                <div class="min-w-0">
                    <div class="kpi-label">Tertaut ke order lokal</div>
                    <div class="kpi-value">{{ number_format($linkedCount) }}</div>
                </div>
                <span class="icon-box tone-danger"><i class="bi bi-link-45deg"></i></span>
            </div>
            <div class="kpi-foot"><span>pesanan sudah dipetakan ke POS</span></div>
        </div>
    </div>
</div>

<div class="row g-app mb-4">
    <div class="col-12 col-xl-5">
        <div class="card mb-0">
            <div class="card-body">
                <h6 class="card-title mb-3">Nilai per platform</h6>
                <div class="legend-list">
                    @forelse($platSummary as $p)
                    <div class="legend-row">
                        <span class="legend-dot"></span>
                        <span class="legend-label">{{ $p['platform'] }}</span>
                        <span class="legend-value">{{ tillora_rp($p['total']) }}</span>
                    </div>
                    <div class="rank-bar"><span style="width:{{ $platMax > 0 ? round($p['total'] / $platMax * 100) : 0 }}%"></span></div>
                    @empty
                    <p class="text-muted mb-0">Belum ada pesanan marketplace.</p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
    <div class="col-12 col-xl-7">
        <div class="card mb-0">
            <div class="card-body">
                <h6 class="card-title mb-3">Pesanan per platform</h6>
                <div style="height:220px;"><canvas id="mpCount"></canvas></div>
            </div>
        </div>
    </div>
</div>

<div class="card table-card" data-table data-page-size="10">
    <div class="table-toolbar">
        <form method="GET" action="/app/marketplace-orders" class="input-icon">
            <i class="bi bi-search"></i>
            <input type="search" name="q" value="{{ $q }}" class="form-control" placeholder="Cari platform, pelanggan, ID pesanan…" aria-label="Cari marketplace order">
        </form>
        <div class="toolbar-end">
            @if($q !== '')<a class="btn btn-light" href="/app/marketplace-orders"><i class="bi bi-x-circle"></i>Reset</a>@endif
        </div>
    </div>
    <div class="table-responsive">
        <table class="table table-hover align-middle">
            <thead>
                <tr>
                    <th class="col-main">Pesanan</th>
                    <th>Platform</th>
                    <th>Pelanggan</th>
                    <th class="table-num">Total</th>
                    <th>Status</th>
                    <th>Order lokal</th>
                </tr>
            </thead>
            <tbody>
                @forelse($orders as $o)
                @php
                    $badge = 'status-warning';
                    if ($o->status === 'completed' || $o->status === 'delivered' || $o->status === 'paid') { $badge = 'status-success'; }
                    if ($o->status === 'cancelled' || $o->status === 'refunded' || $o->status === 'failed') { $badge = 'status-danger'; }
                    if ($o->status === 'pending' || $o->status === 'processing') { $badge = 'status-info'; }
                @endphp
                <tr>
                    <td>
                        <div class="cell-receipt"><span class="receipt-icon"><i class="bi bi-bag"></i></span>
                        <div><span class="table-id">{{ $o->platform_order_id ?: $o->platform_invoice }}</span><span class="cell-sub">{{ $o->created_at ? $o->created_at->format('d M Y H:i') : '—' }}</span></div></div>
                    </td>
                    <td><span class="fw-medium text-heading">{{ $o->platform ?: 'Lainnya' }}</span></td>
                    <td>
                        <div class="cell-user"><span class="avatar tone-primary">{{ strtoupper(substr($o->customer_name ?: '-', 0, 1)) }}</span>
                        <div><span class="cell-title">{{ $o->customer_name ?: '—' }}</span><span class="cell-sub">{{ $o->customer_phone ?: '—' }}</span></div></div>
                    </td>
                    <td class="table-num fw-semibold">{{ tillora_rp($o->total_amount) }}</td>
                    <td><span class="status-badge {{ $badge }}">{{ ucfirst($o->status ?: 'pending') }}</span></td>
                    <td>
                        @if($o->order_id)<a class="table-id" href="/app/sale-details?id={{ optional($o->order)->order_number }}">{{ optional($o->order)->order_number ?: $o->order_id }}</a>@else<span class="text-muted">—</span>@endif
                    </td>
                </tr>
                @empty
                <tr><td colspan="6" class="text-center text-muted py-4">Belum ada pesanan marketplace.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection

@push('scripts')
<script>
(function(){
  if (!window.Chart) return;
  Chart.defaults.font.family = 'Inter, system-ui, sans-serif';
  new Chart(document.getElementById('mpCount'), {type:'bar',
    data:{labels:@json($platLabels),datasets:[{label:'Pesanan',data:@json($platCounts),backgroundColor:'#4F46E5',borderRadius:5}]},
    options:{plugins:{legend:{display:false}},scales:{y:{beginAtZero:true,ticks:{precision:0}}}}});
})();
</script>
@endpush
