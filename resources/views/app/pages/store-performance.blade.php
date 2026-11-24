@extends('app.layout')
@section('title','Store Performance')

@php use App\Services\OverviewData; @endphp

@section('content')
<div class="page-header">
    <div>
        <nav aria-label="Breadcrumb"><ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/app"><i class="bi bi-house-door"></i> Home</a></li>
            <li class="breadcrumb-item"><a href="/app">Dashboard</a></li>
            <li class="breadcrumb-item active" aria-current="page">Store performance</li>
        </ol></nav>
        <h1 class="page-title">Store performance</h1>
        <p class="page-subtitle">Branch targets and comparisons · MTD {{ now()->format('F Y') }} · data asli.</p>
    </div>
    <div class="page-actions">
        <a class="btn btn-light" href="/app/business-overview"><i class="bi bi-briefcase"></i>Business overview</a>
        <a class="btn btn-primary" href="/pos"><i class="bi bi-upc-scan"></i>Open checkout</a>
    </div>
</div>

<div class="row g-app mb-4">
    <div class="col-6 col-xl-3">
        <div class="card kpi-card grad-indigo">
            <div class="kpi-top"><div class="min-w-0"><div class="kpi-label">MTD revenue (all stores)</div><div class="kpi-value">{{ OverviewData::rp($totalMtd) }}</div></div><span class="icon-box tone-primary"><i class="bi bi-shop"></i></span></div>
            <div class="kpi-foot"><span class="kpi-delta is-up"><i class="bi bi-arrow-up-right"></i>{{ round($avgPct) }}%</span><span>avg target attainment</span></div>
        </div>
    </div>
    <div class="col-6 col-xl-3">
        <div class="card kpi-card grad-cyan">
            <div class="kpi-top"><div class="min-w-0"><div class="kpi-label">Best store MTD</div><div class="kpi-value">{{ \Illuminate\Support\Str::limit($best['outlet']->name ?? '-', 16) }}</div></div><span class="icon-box tone-cyan"><i class="bi bi-trophy"></i></span></div>
            <div class="kpi-foot"><span class="kpi-delta is-up"><i class="bi bi-arrow-up-right"></i>{{ OverviewData::rp($best['mtd'] ?? 0) }}</span><span>revenue MTD</span></div>
        </div>
    </div>
    <div class="col-6 col-xl-3">
        <div class="card kpi-card grad-mint">
            <div class="kpi-top"><div class="min-w-0"><div class="kpi-label">Stores on track</div><div class="kpi-value">{{ $onTrack }} / {{ $stores->count() }}</div></div><span class="icon-box tone-success"><i class="bi bi-check2-circle"></i></span></div>
            <div class="kpi-foot"><span class="kpi-delta is-up"><i class="bi bi-arrow-up-right"></i>≥80%</span><span>of monthly target</span></div>
        </div>
    </div>
    <div class="col-6 col-xl-3">
        <div class="card kpi-card grad-amber">
            <div class="kpi-top"><div class="min-w-0"><div class="kpi-label">Active branches</div><div class="kpi-value">{{ $stores->count() }}</div></div><span class="icon-box tone-warning"><i class="bi bi-diagram-3"></i></span></div>
            <div class="kpi-foot"><span class="kpi-delta is-up"><i class="bi bi-arrow-up-right"></i>live</span><span>reporting stores</span></div>
        </div>
    </div>
</div>

<div class="row g-app mb-4">
    <div class="col-xl-6">
        <div class="card h-100">
            <div class="card-header"><div><h2 class="card-title">Revenue vs target</h2><p class="card-subtitle">MTD per branch</p></div></div>
            <div class="card-body"><div class="chart-box chart-sm"><canvas id="spTarget"></canvas></div></div>
        </div>
    </div>
    <div class="col-xl-6">
        <div class="card h-100">
            <div class="card-header"><div><h2 class="card-title">Daily revenue by store</h2><p class="card-subtitle">Last 30 days</p></div></div>
            <div class="card-body"><div class="chart-box chart-sm"><canvas id="spTrend"></canvas></div>
            <div class="chart-legend" id="spLegend"></div></div>
        </div>
    </div>
</div>

<div class="row g-app mb-4">
    <div class="col-xxl-8">
        <div class="card table-card h-100">
            <div class="card-header"><div><h2 class="card-title">Branch scorecard</h2><p class="card-subtitle">Month to date · data asli</p></div></div>
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead><tr><th class="col-main">Store</th><th class="table-num">Revenue</th><th class="table-num">Transactions</th><th class="table-num">Avg basket</th><th class="table-num">Target</th><th>Attainment</th></tr></thead>
                    <tbody>
                        @foreach($stores as $s)
                        <tr>
                            <td><div class="cell-user"><span class="avatar tone-primary"><i class="bi bi-shop"></i></span><div><span class="cell-title">{{ $s['outlet']->name }}</span><span class="cell-sub">{{ $s['outlet']->code ?? '' }}</span></div></div></td>
                            <td class="table-num fw-semibold">{{ OverviewData::rp($s['mtd']) }}</td>
                            <td class="table-num text-muted">{{ number_format($s['count'],0,',','.') }}</td>
                            <td class="table-num text-muted">{{ OverviewData::rp($s['avg']) }}</td>
                            <td class="table-num text-muted">{{ OverviewData::rp($s['target']) }}</td>
                            <td><div class="d-flex align-items-center gap-2"><div class="progress" style="width:110px;"><div class="progress-bar" style="width: {{ min(100, round($s['pct'])) }}%;"></div></div><strong>{{ round($s['pct']) }}%</strong></div></td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <div class="col-xxl-4">
        <div class="card h-100 grad-neutral">
            <div class="card-header"><div><h2 class="card-title">Insights</h2><p class="card-subtitle">Generated from live data</p></div></div>
            <div class="card-body">
                @foreach($insights as $ins)
                <div class="alert-strip is-{{ $ins['tone'] === 'success' ? 'success' : ($ins['tone'] === 'warning' ? 'warning' : 'info') }} mb-2 py-2"><i class="bi bi-{{ $ins['tone'] === 'success' ? 'check-circle' : ($ins['tone'] === 'warning' ? 'exclamation-triangle' : 'info-circle') }}"></i><span class="small flex-grow-1">{{ $ins['text'] }}</span></div>
                @endforeach
                @if(empty($insights))<p class="text-muted">Belum ada data untuk insight.</p>@endif
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
(function(){
  if (!window.Chart) return;
  Chart.defaults.font.family = 'Inter, system-ui, sans-serif';
  const short = v => v>=1e6 ? (v/1e6).toFixed(1)+'jt' : v>=1e3 ? (v/1e3).toFixed(0)+'rb' : v;
  const colors = ['#4F46E5','#06B6D4','#10B981','#F59E0B','#8b5cf6'];
  const names = @json($stores->map(fn($s) => $s['outlet']->name)->values());
  new Chart(document.getElementById('spTarget'), {type:'bar',
    data:{labels:names,datasets:[
      {label:'Revenue',data:@json($stores->map(fn($s) => round($s['mtd']))->values()),backgroundColor:'#4F46E5',borderRadius:6},
      {label:'Target',data:@json($stores->map(fn($s) => round($s['target']))->values()),backgroundColor:'#C9D2FE',borderRadius:6}]},
    options:{plugins:{legend:{display:false}},scales:{y:{ticks:{callback:short}}}}});
  const storeIds = @json($stores->map(fn($s) => $s['outlet']->id)->values());
  const byStore = @json($byStore);
  const trendSets = names.map((n,i)=>({label:n,data:byStore[storeIds[i]]||[],borderColor:colors[i%colors.length],fill:false,tension:.4,cubicInterpolationMode:'monotone',pointRadius:0}));
  new Chart(document.getElementById('spTrend'), {type:'line',
    data:{labels:@json($labels),datasets:trendSets},
    options:{plugins:{legend:{display:false}},scales:{y:{ticks:{callback:short}},x:{ticks:{maxTicksLimit:8}}}}});
  document.getElementById('spLegend').innerHTML = names.map((n,i)=>'<span><i class="legend-dot" style="background:'+colors[i%colors.length]+';"></i>'+n+'</span>').join('');
})();
</script>
@endpush
