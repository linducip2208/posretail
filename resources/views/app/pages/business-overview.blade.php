@extends('app.layout')
@section('title','Business Overview')

@php use App\Services\OverviewData; @endphp

@section('content')
<div class="page-header">
    <div>
        <nav aria-label="Breadcrumb"><ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/app"><i class="bi bi-house-door"></i> Home</a></li>
            <li class="breadcrumb-item"><a href="/app">Dashboard</a></li>
            <li class="breadcrumb-item active" aria-current="page">Business overview</li>
        </ol></nav>
        <h1 class="page-title">Business overview</h1>
        <p class="page-subtitle">Executive KPIs and outlook · YTD {{ date('Y') }} · data asli.</p>
    </div>
    <div class="page-actions">
        <a class="btn btn-light" href="/app/store-performance"><i class="bi bi-shop"></i>Store performance</a>
        <a class="btn btn-primary" href="/app/sales-overview"><i class="bi bi-graph-up-arrow"></i>Sales overview</a>
    </div>
</div>

<div class="row g-app mb-4">
    <div class="col-6 col-xl-3">
        <div class="card kpi-card grad-indigo">
            <div class="kpi-top"><div class="min-w-0"><div class="kpi-label">YTD revenue</div><div class="kpi-value">{{ OverviewData::rp($ytdRev) }}</div></div><span class="icon-box tone-primary"><i class="bi bi-currency-dollar"></i></span></div>
            <div class="kpi-foot"><span class="kpi-delta is-up"><i class="bi bi-arrow-up-right"></i>{{ $annualGoal > 0 ? round($ytdRev/$annualGoal*100) : 0 }}%</span><span>of {{ OverviewData::rp($annualGoal) }} annual goal</span></div>
            <div class="kpi-spark"><canvas id="sparkYtd"></canvas></div>
        </div>
    </div>
    <div class="col-6 col-xl-3">
        <div class="card kpi-card grad-mint">
            <div class="kpi-top"><div class="min-w-0"><div class="kpi-label">YTD gross profit</div><div class="kpi-value">{{ OverviewData::rp($ytdProfit) }}</div></div><span class="icon-box tone-success"><i class="bi bi-graph-up"></i></span></div>
            <div class="kpi-foot"><span class="kpi-delta is-up"><i class="bi bi-arrow-up-right"></i>{{ number_format($ytdMargin,1) }}%</span><span>gross margin YTD</span></div>
            <div class="kpi-spark"><canvas id="sparkProfit"></canvas></div>
        </div>
    </div>
    <div class="col-6 col-xl-3">
        <div class="card kpi-card grad-cyan">
            <div class="kpi-top"><div class="min-w-0"><div class="kpi-label">Active stores</div><div class="kpi-value">{{ $outletCount }}</div></div><span class="icon-box tone-cyan"><i class="bi bi-shop-window"></i></span></div>
            <div class="kpi-foot"><span class="kpi-delta {{ $lowCount > 0 ? 'is-down' : 'is-up' }}"><i class="bi bi-arrow-{{ $lowCount > 0 ? 'down' : 'up' }}-right"></i>{{ $lowCount }}</span><span>products below min stock</span></div>
            <div class="kpi-spark"><canvas id="sparkStores"></canvas></div>
        </div>
    </div>
    <div class="col-6 col-xl-3">
        <div class="card kpi-card grad-amber">
            <div class="kpi-top"><div class="min-w-0"><div class="kpi-label">Pending payments</div><div class="kpi-value">{{ $pendingPay }}</div></div><span class="icon-box tone-warning"><i class="bi bi-wallet2"></i></span></div>
            <div class="kpi-foot"><span class="kpi-delta {{ $pendingPay > 0 ? 'is-down' : 'is-up' }}">follow-up</span><span>orders need attention</span></div>
            <div class="kpi-spark"><canvas id="sparkPay"></canvas></div>
        </div>
    </div>
</div>

<div class="row g-app mb-4">
    <div class="col-xl-8">
        <div class="card h-100">
            <div class="card-header"><div><h2 class="card-title">Monthly revenue & gross profit</h2><p class="card-subtitle">Last 12 months · data asli</p></div></div>
            <div class="card-body"><div class="chart-box chart-lg"><canvas id="boMonthly"></canvas></div>
            <div class="chart-legend"><span><i class="legend-dot" style="background: var(--app-primary);"></i>Revenue</span><span><i class="legend-dot" style="background: #06B6D4;"></i>Gross profit</span></div></div>
        </div>
    </div>
    <div class="col-xl-4">
        <div class="card h-100 grad-neutral">
            <div class="card-header"><div><h2 class="card-title">FY{{ date('Y') }} goals</h2><p class="card-subtitle">Progress to date</p></div></div>
            <div class="card-body">
                <div class="welcome-stat mb-3"><span>Annual revenue goal</span><strong>{{ OverviewData::rp($ytdRev) }} <small class="fw-normal opacity-75">/ {{ OverviewData::rp($annualGoal) }}</small></strong><div class="progress"><div class="progress-bar" style="width: {{ min(100, round($annualGoal ? $ytdRev/$annualGoal*100 : 0)) }}%;"></div></div></div>
                <div class="welcome-stat mb-3"><span>Gross margin target ({{ number_format($marginGoal,0) }}%)</span><strong>{{ number_format($ytdMargin,1) }}%</strong><div class="progress"><div class="progress-bar" style="width: {{ min(100, round($marginGoal ? $ytdMargin/$marginGoal*100 : 0)) }}%;"></div></div></div>
                <div class="welcome-stat"><span>Stock health</span><strong>{{ $lowCount }} <small class="fw-normal opacity-75">low-stock SKUs</small></strong><div class="progress"><div class="progress-bar" style="width: {{ $lowCount > 0 ? 100 - min(100, $lowCount*5) : 100 }}%;"></div></div></div>
            </div>
        </div>
    </div>
</div>

<div class="row g-app mb-4">
    <div class="col-xl-6">
        <div class="card h-100">
            <div class="card-header"><div><h2 class="card-title">Risks</h2><p class="card-subtitle">What needs attention</p></div></div>
            <div class="card-body">
                @foreach($risks as $r)<div class="alert-strip is-warning mb-2 py-2"><i class="bi bi-exclamation-triangle text-warning-app"></i><span class="small flex-grow-1">{{ $r }}</span></div>@endforeach
            </div>
        </div>
    </div>
    <div class="col-xl-6">
        <div class="card h-100">
            <div class="card-header"><div><h2 class="card-title">Opportunities</h2><p class="card-subtitle">Where to grow</p></div></div>
            <div class="card-body">
                @foreach($opps as $o)<div class="alert-strip is-info mb-2 py-2"><i class="bi bi-lightbulb text-warning-app"></i><span class="small flex-grow-1">{{ $o }}</span></div>@endforeach
            </div>
        </div>
    </div>
</div>

<div class="card table-card">
    <div class="card-header"><div><h2 class="card-title">Department P&L</h2><p class="card-subtitle">YTD by category · data asli</p></div></div>
    <div class="table-responsive">
        <table class="table table-hover align-middle">
            <thead><tr><th class="col-main">Department</th><th class="table-num">Revenue</th><th class="table-num">COGS</th><th class="table-num">Gross profit</th><th>Margin</th></tr></thead>
            <tbody>
                @forelse($pnl as $row)
                <tr>
                    <td><div class="cell-user"><span class="avatar tone-neutral"><i class="bi bi-box-seam"></i></span><div><span class="cell-title">{{ $row['name'] }}</span></div></div></td>
                    <td class="table-num fw-semibold">{{ OverviewData::rp($row['rev']) }}</td>
                    <td class="table-num text-muted">{{ OverviewData::rp($row['cost']) }}</td>
                    <td class="table-num fw-semibold">{{ OverviewData::rp($row['profit']) }}</td>
                    <td><div class="d-flex align-items-center gap-2"><div class="progress" style="width:110px;"><div class="progress-bar" style="width: {{ min(100, max(0, round($row['margin']))) }}%;"></div></div><strong>{{ number_format($row['margin'],1) }}%</strong></div></td>
                </tr>
                @empty
                <tr><td colspan="5" class="text-center text-muted py-4">Belum ada data penjualan tahun ini.</td></tr>
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
  const short = v => v>=1e9 ? (v/1e9).toFixed(1)+'M' : v>=1e6 ? (v/1e6).toFixed(1)+'jt' : v>=1e3 ? (v/1e3).toFixed(0)+'rb' : v;
  const fmtRp = v => 'Rp ' + Number(v||0).toLocaleString('id-ID');
  new Chart(document.getElementById('boMonthly'), {type:'bar',
    data:{labels:@json($mLabels),datasets:[
      {label:'Revenue',data:@json($mRev),backgroundColor:'#4F46E5',borderRadius:5},
      {label:'Gross profit',data:@json($mProfit),backgroundColor:'#06B6D4',borderRadius:5}]},
    options:{plugins:{legend:{display:false},tooltip:{callbacks:{label:c=>' '+c.dataset.label+': '+fmtRp(c.parsed.y)}}},scales:{y:{ticks:{callback:short}}}}});
  const spark = (id, data) => { const el=document.getElementById(id); if(!el) return;
    new Chart(el,{type:'line',data:{labels:data.map((_,i)=>i),datasets:[{data,borderColor:'#fff',fill:false,tension:0,pointRadius:0}]},options:{plugins:{legend:{display:false},tooltip:{enabled:false}},scales:{x:{display:false},y:{display:false}},animation:false}}); };
  spark('sparkYtd', @json($mRev)); spark('sparkProfit', @json($mProfit));
  spark('sparkStores', @json($mMargin)); spark('sparkPay', @json($mRev).slice().reverse());
})();
</script>
@endpush
