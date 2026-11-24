@extends('app.layout')
@section('title','Sales Reports')



@section('content')
<div class="page-header">
    <div>
        <nav aria-label="Breadcrumb"><ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/app"><i class="bi bi-house-door"></i> Home</a></li>
            <li class="breadcrumb-item"><a href="/app/reports-overview">Reports</a></li>
            <li class="breadcrumb-item active" aria-current="page">Sales reports</li>
        </ol></nav>
        <h1 class="page-title">Sales reports</h1>
        <p class="page-subtitle">Ringkasan harian 30 hari terakhir · data asli transaksi.</p>
    </div>
</div>

<div class="row g-app mb-4">
    <div class="col-6 col-xl-4">
        <div class="card kpi-card grad-indigo">
            <div class="kpi-top"><div class="min-w-0"><div class="kpi-label">Revenue (30d)</div><div class="kpi-value">{{ tillora_rp($rev) }}</div></div><span class="icon-box tone-primary"><i class="bi bi-graph-up-arrow"></i></span></div>
            <div class="kpi-foot"><span>{{ number_format($cnt,0,',','.') }} transaksi</span></div>
        </div>
    </div>
    <div class="col-6 col-xl-4">
        <div class="card kpi-card grad-cyan">
            <div class="kpi-top"><div class="min-w-0"><div class="kpi-label">Transaksi (30d)</div><div class="kpi-value">{{ number_format($cnt,0,',','.') }}</div></div><span class="icon-box tone-cyan"><i class="bi bi-bag-check"></i></span></div>
            <div class="kpi-foot"><span>order non-batal</span></div>
        </div>
    </div>
    <div class="col-6 col-xl-4">
        <div class="card kpi-card grad-mint">
            <div class="kpi-top"><div class="min-w-0"><div class="kpi-label">Rata-rata per transaksi</div><div class="kpi-value">{{ tillora_rp($aov) }}</div></div><span class="icon-box tone-success"><i class="bi bi-basket"></i></span></div>
            <div class="kpi-foot"><span>average order value</span></div>
        </div>
    </div>
</div>

<div class="card mb-4">
    <div class="card-header"><div><h2 class="card-title">Revenue &amp; AOV harian</h2><p class="card-subtitle">30 hari terakhir · data asli</p></div></div>
    <div class="card-body">
        <div class="chart-box chart-lg"><canvas id="srDaily"></canvas></div>
        <div class="chart-legend"><span><i class="legend-dot" style="background: var(--app-primary);"></i>Revenue</span><span><i class="legend-dot" style="background: #06B6D4;"></i>AOV</span></div>
    </div>
</div>

<div class="row g-app">
    <div class="col-xl-5">
        <div class="card h-100">
            <div class="card-header"><div><h2 class="card-title">Top 10 produk</h2><p class="card-subtitle">Berdasarkan revenue · 30 hari</p></div></div>
            <div class="card-body">
                <ul class="rank-list">
                    @forelse($topProducts as $i => $tp)
                    <li>
                        <span class="rank-num">{{ $i+1 }}</span>
                        <div class="min-w-0 flex-grow-1"><div class="fw-semibold text-heading text-truncate">{{ $tp->product?->name ?? 'Produk #'.$tp->product_id }}</div><div class="small text-muted">{{ number_format($tp->qty,0,',','.') }} terjual</div></div>
                        <strong>{{ tillora_rp($tp->revenue) }}</strong>
                    </li>
                    @empty
                    <li class="text-muted">Belum ada penjualan 30 hari terakhir.</li>
                    @endforelse
                </ul>
                <div class="mt-3"><a class="btn btn-light btn-sm" href="/app/product-performance">Lihat product performance<i class="bi bi-arrow-right ms-1"></i></a></div>
            </div>
        </div>
    </div>
    <div class="col-xl-7">
        <div class="card table-card">
            <div class="card-header"><div><h2 class="card-title">Tabel harian</h2><p class="card-subtitle">30 baris · terbaru dulu</p></div></div>
            <div class="table-responsive" style="max-height:520px;overflow:auto;">
                <table class="table table-hover align-middle">
                    <thead><tr><th>Tanggal</th><th class="table-num">Transaksi</th><th class="table-num">Revenue</th><th class="table-num">AOV</th></tr></thead>
                    <tbody>
                        @forelse($table as $row)
                        <tr>
                            <td>{{ \Carbon\Carbon::parse($row['date'])->format('d M Y') }}</td>
                            <td class="table-num">{{ number_format($row['orders'],0,',','.') }}</td>
                            <td class="table-num fw-semibold">{{ tillora_rp($row['revenue']) }}</td>
                            <td class="table-num">{{ tillora_rp($row['aov']) }}</td>
                        </tr>
                        @empty
                        <tr><td colspan="4" class="text-center text-muted py-4">Belum ada transaksi.</td></tr>
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
  const fmtRp = v => 'Rp ' + Number(v||0).toLocaleString('id-ID');
  new Chart(document.getElementById('srDaily'), {type:'line',
    data:{labels:@json(array_map(fn($d) => \Carbon\Carbon::parse($d)->format('d M'), $labels)),datasets:[
      {label:'Revenue',data:@json($revDaily),borderColor:'#4F46E5',backgroundColor:'rgba(79,70,229,.12)',fill:true,tension:.4,cubicInterpolationMode:'monotone',pointRadius:0,yAxisID:'y'},
      {label:'AOV',data:@json($aovDaily),borderColor:'#06B6D4',borderDash:[5,5],fill:false,tension:.4,cubicInterpolationMode:'monotone',pointRadius:0,yAxisID:'y1'}]},
    options:{plugins:{legend:{display:false},tooltip:{callbacks:{label:c=>' '+c.dataset.label+': '+fmtRp(c.parsed.y)}}},
      scales:{y:{ticks:{callback:v=>v>=1e6?(v/1e6)+'jt':v>=1e3?(v/1e3)+'rb':v}},y1:{position:'right',grid:{drawOnChartArea:false},ticks:{callback:v=>v>=1e6?(v/1e6)+'jt':v>=1e3?(v/1e3)+'rb':v}}}}});
})();
</script>
@endpush
