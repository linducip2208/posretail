@extends('app.layout')
@section('title','Sales Overview')

@php
$fmtDelta = fn($v, $suffix='') => ($v >= 0 ? '+' : '').number_format($v,1).'%'.$suffix;
@endphp

@section('content')
<div class="page-header">
    <div>
        <nav aria-label="Breadcrumb"><ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/app"><i class="bi bi-house-door"></i> Home</a></li>
            <li class="breadcrumb-item"><a href="/app">Dashboard</a></li>
            <li class="breadcrumb-item active" aria-current="page">Sales overview</li>
        </ol></nav>
        <h1 class="page-title">Sales overview</h1>
        <p class="page-subtitle">Revenue, order volume and basket behaviour · {{ $start->format('d M Y') }} – {{ $end->format('d M Y') }} · data asli.</p>
    </div>
    <div class="page-actions">
        <div class="segmented" role="group" aria-label="Date range">
            @foreach(['7d'=>'7D','30d'=>'30D','90d'=>'90D','12m'=>'12M'] as $k=>$lbl)
            <a href="{{ url('/app/sales-overview?range='.$k) }}" class="btn {{ $range===$k?'active':'' }}">{{ $lbl }}</a>
            @endforeach
        </div>
    </div>
</div>

<div class="row g-app mb-4">
    <div class="col-6 col-xl-3">
        <div class="card kpi-card grad-indigo">
            <div class="kpi-top"><div class="min-w-0"><div class="kpi-label">Net sales</div><div class="kpi-value">{{ tillora_rp($rev) }}</div></div><span class="icon-box tone-primary"><i class="bi bi-graph-up-arrow"></i></span></div>
            <div class="kpi-foot"><span class="kpi-delta {{ $pctRev>=0?'is-up':'is-down' }}"><i class="bi bi-arrow-{{ $pctRev>=0?'up':'down' }}-right"></i>{{ number_format(abs($pctRev),1) }}%</span><span>{{ $rangeLabel }}</span></div>
        </div>
    </div>
    <div class="col-6 col-xl-3">
        <div class="card kpi-card grad-cyan">
            <div class="kpi-top"><div class="min-w-0"><div class="kpi-label">Orders</div><div class="kpi-value">{{ number_format($cnt,0,',','.') }}</div></div><span class="icon-box tone-cyan"><i class="bi bi-bag-check"></i></span></div>
            <div class="kpi-foot"><span class="kpi-delta {{ $pctCnt>=0?'is-up':'is-down' }}"><i class="bi bi-arrow-{{ $pctCnt>=0?'up':'down' }}-right"></i>{{ number_format(abs($pctCnt),1) }}%</span><span>{{ $rangeLabel }}</span></div>
        </div>
    </div>
    <div class="col-6 col-xl-3">
        <div class="card kpi-card grad-mint">
            <div class="kpi-top"><div class="min-w-0"><div class="kpi-label">Average order value</div><div class="kpi-value">{{ tillora_rp($aov) }}</div></div><span class="icon-box tone-success"><i class="bi bi-basket"></i></span></div>
            <div class="kpi-foot"><span class="kpi-delta {{ $pctAov>=0?'is-up':'is-down' }}"><i class="bi bi-arrow-{{ $pctAov>=0?'up':'down' }}-right"></i>{{ number_format(abs($pctAov),1) }}%</span><span>{{ $rangeLabel }}</span></div>
        </div>
    </div>
    <div class="col-6 col-xl-3">
        <div class="card kpi-card grad-amber">
            <div class="kpi-top"><div class="min-w-0"><div class="kpi-label">Return rate</div><div class="kpi-value">{{ number_format($retRate,1) }}%</div></div><span class="icon-box tone-warning"><i class="bi bi-arrow-return-left"></i></span></div>
            <div class="kpi-foot"><span class="kpi-delta {{ $retDiff<=0?'is-up':'is-down' }}"><i class="bi bi-arrow-{{ $retDiff<=0?'up':'down' }}-right"></i>{{ number_format(abs($retDiff),1) }} pts</span><span>{{ $retCount }} retur · vs periode lalu</span></div>
        </div>
    </div>
</div>

<div class="mb-4">
    <div class="card" id="soDailyCard">
        <div class="card-header">
            <div><h2 class="card-title">Daily revenue & gross profit</h2><p class="card-subtitle">Data asli · {{ $rangeLabel }}</p></div>
        </div>
        <div class="card-body">
            <div class="chart-box chart-lg"><canvas id="soDaily"></canvas></div>
            <div class="chart-legend"><span><i class="legend-dot" style="background: var(--app-primary);"></i>Revenue</span><span><i class="legend-dot" style="background: #06B6D4;"></i>Gross profit</span></div>
        </div>
    </div>
</div>

<div class="row g-app mb-4">
    <div class="col-md-6 col-xl-4">
        <div class="card h-100">
            <div class="card-header"><div><h2 class="card-title">Payment methods</h2><p class="card-subtitle">Share of revenue</p></div></div>
            <div class="card-body">
                <div class="chart-box chart-donut"><canvas id="soPay"></canvas></div>
                <ul class="legend-list mt-3">
                    @forelse($payNames as $i => $nm)
                    <li><span><i class="legend-dot" style="background: {{ ['#4F46E5','#06B6D4','#10B981','#F59E0B','#8b5cf6','#E11D48'][$i % 6] }};"></i>{{ $nm }}</span><strong>{{ tillora_rp($payTotals[$i]) }}</strong></li>
                    @empty
                    <li class="text-muted">Belum ada pembayaran di periode ini.</li>
                    @endforelse
                </ul>
            </div>
        </div>
    </div>
    <div class="col-md-6 col-xl-4">
        <div class="card h-100">
            <div class="card-header"><div><h2 class="card-title">Sales by category</h2><p class="card-subtitle">Revenue · {{ $rangeLabel }}</p></div></div>
            <div class="card-body">
                <div class="chart-box chart-donut"><canvas id="soCat"></canvas></div>
                <ul class="legend-list mt-3">
                    @forelse($catNames as $i => $nm)
                    <li><span><i class="legend-dot" style="background: {{ ['#4F46E5','#06B6D4','#10B981','#F59E0B','#8b5cf6','#E11D48','#0ea5e9','#84cc16'][$i % 8] }};"></i>{{ $nm }}</span><strong>{{ tillora_rp($catTotals[$i]) }}</strong></li>
                    @empty
                    <li class="text-muted">Belum ada penjualan di periode ini.</li>
                    @endforelse
                </ul>
            </div>
        </div>
    </div>
    <div class="col-xl-4">
        <div class="card h-100">
            <div class="card-header"><div><h2 class="card-title">Basket size</h2><p class="card-subtitle">12-month trend</p></div></div>
            <div class="card-body">
                <div class="chart-box chart-sm"><canvas id="soBasket"></canvas></div>
                <div class="chart-legend"><span><i class="legend-dot" style="background: var(--app-primary);"></i>Avg basket value</span><span><i class="legend-dot" style="background: #06B6D4;"></i>Items per basket</span></div>
            </div>
        </div>
    </div>
</div>

<div class="row g-app">
    <div class="col-xl-5">
        <div class="card h-100">
            <div class="card-header"><div><h2 class="card-title">Busiest trading hours</h2><p class="card-subtitle">Revenue per hour · {{ $rangeLabel }}</p></div></div>
            <div class="card-body">
                <div class="heat-grid">
                    <span></span>
                    @foreach(['9a','10a','11a','12p','1p','2p','3p','4p','5p','6p','7p','8p'] as $hl)<span class="text-center">{{ $hl }}</span>@endforeach
                    @foreach(['Mon','Tue','Wed','Thu','Fri','Sat','Sun'] as $di => $dn)
                        <span class="heat-label">{{ $dn }}</span>
                        @for($h=0;$h<12;$h++)
                            @php $v = $heat[$di][$h] ?? 0; $intensity = $heatMax > 0 ? round($v/$heatMax,2) : 0; @endphp
                            <span class="heat-cell" style="--heat: {{ $intensity }};" title="{{ $dn }} {{ ['9a','10a','11a','12p','1p','2p','3p','4p','5p','6p','7p','8p'][$h] }}: {{ tillora_rp($v) }}"></span>
                        @endfor
                    @endforeach
                </div>
                <div class="d-flex align-items-center gap-2 mt-3 small text-muted">Quieter <span class="heat-cell d-inline-block" style="width:60px;height:8px;background:linear-gradient(90deg, rgba(var(--app-primary-rgb),.12), rgba(var(--app-primary-rgb),.95));border-radius:4px;"></span> Busier</div>
            </div>
        </div>
    </div>
    <div class="col-xl-7">
        <div class="card table-card">
            <div class="card-header"><div><h2 class="card-title">Sales by team member</h2><p class="card-subtitle">{{ $rangeLabel }} · data asli</p></div></div>
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead><tr><th class="col-main">Employee</th><th>Role</th><th class="table-num">Transactions</th><th class="table-num">Sales</th><th class="table-num">Avg sale</th><th>Share of top</th></tr></thead>
                    <tbody>
                        @forelse($team as $row)
                        @php $u = $row->user; @endphp
                        <tr>
                            <td><div class="cell-user"><span class="avatar tone-primary">{{ strtoupper(substr($u?->name ?? '?',0,2)) }}</span><div><span class="cell-title">{{ $u?->name ?? 'User #'.$row->user_id }}</span><span class="cell-sub">{{ $u?->email ?? '' }}</span></div></div></td>
                            <td>{{ ucfirst($u?->role ?? '-') }}</td>
                            <td class="table-num text-muted">{{ number_format($row->cnt,0,',','.') }}</td>
                            <td class="table-num fw-semibold">{{ tillora_rp($row->total) }}</td>
                            <td class="table-num">{{ tillora_rp($row->cnt ? $row->total/$row->cnt : 0) }}</td>
                            <td><div class="progress" style="width:120px;"><div class="progress-bar" style="width: {{ round($row->total/$topTeam*100) }}%;"></div></div></td>
                        </tr>
                        @empty
                        <tr><td colspan="6" class="text-center text-muted py-4">Belum ada penjualan di periode ini.</td></tr>
                        @endforelse
                    </tbody>
                </table>
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
  const fmtRp = v => 'Rp ' + Number(v||0).toLocaleString('id-ID');
  const short = v => v>=1e9 ? (v/1e9).toFixed(1)+'M' : v>=1e6 ? (v/1e6).toFixed(1)+'jt' : v>=1e3 ? (v/1e3).toFixed(0)+'rb' : v;
  new Chart(document.getElementById('soDaily'), {type:'line',
    data:{labels:@json($labels),datasets:[
      {label:'Revenue',data:@json($revDaily),borderColor:'#4F46E5',backgroundColor:'rgba(79,70,229,.12)',fill:true,tension:.4,cubicInterpolationMode:'monotone',pointRadius:0},
      {label:'Gross profit',data:@json($profitDaily),borderColor:'#06B6D4',borderDash:[5,5],fill:false,tension:.4,cubicInterpolationMode:'monotone',pointRadius:0}]},
    options:{plugins:{legend:{display:false},tooltip:{callbacks:{label:c=>' '+c.dataset.label+': '+fmtRp(c.parsed.y)}}},scales:{y:{ticks:{callback:short}}}}});
  const donut = (id, labels, data, colors) => { const el=document.getElementById(id); if(!el) return;
    new Chart(el,{type:'doughnut',data:{labels:labels.length?labels:['Belum ada data'],datasets:[{data:data.length?data:[1],backgroundColor:colors,borderWidth:0}]},options:{cutout:'68%',plugins:{legend:{display:false}}}}); };
  donut('soPay', @json($payNames), @json($payTotals), ['#4F46E5','#06B6D4','#10B981','#F59E0B','#8b5cf6','#E11D48']);
  donut('soCat', @json($catNames), @json($catTotals), ['#4F46E5','#06B6D4','#10B981','#F59E0B','#8b5cf6','#E11D48','#0ea5e9','#84cc16']);
  new Chart(document.getElementById('soBasket'), {type:'bar',
    data:{labels:@json($basketLabels),datasets:[
      {label:'Avg basket value',data:@json($basketValue),backgroundColor:'#4F46E5',borderRadius:4},
      {label:'Items per basket',data:@json($basketItems),backgroundColor:'#06B6D4',borderRadius:4}]},
    options:{plugins:{legend:{display:false}},scales:{y:{ticks:{callback:short}}}}});
})();
</script>
@endpush
