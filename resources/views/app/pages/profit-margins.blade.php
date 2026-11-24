@extends('app.layout')
@section('title','Profit Margins')



@section('content')
<div class="page-header">
    <div>
        <nav aria-label="Breadcrumb"><ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/app"><i class="bi bi-house-door"></i> Home</a></li>
            <li class="breadcrumb-item"><a href="/app/reports-overview">Reports</a></li>
            <li class="breadcrumb-item active" aria-current="page">Profit margins</li>
        </ol></nav>
        <h1 class="page-title">Profit margins</h1>
        <p class="page-subtitle">Margin dari harga pokok (cost_price) vs harga jual · 30 hari terakhir · data asli.</p>
    </div>
</div>

<div class="row g-app mb-4">
    <div class="col-6 col-xl-3">
        <div class="card kpi-card grad-indigo">
            <div class="kpi-top"><div class="min-w-0"><div class="kpi-label">Revenue (30d)</div><div class="kpi-value">{{ tillora_rp($rev) }}</div></div><span class="icon-box tone-primary"><i class="bi bi-graph-up-arrow"></i></span></div>
            <div class="kpi-foot"><span>penjualan non-batal</span></div>
        </div>
    </div>
    <div class="col-6 col-xl-3">
        <div class="card kpi-card grad-amber">
            <div class="kpi-top"><div class="min-w-0"><div class="kpi-label">HPP (30d)</div><div class="kpi-value">{{ tillora_rp($cogs) }}</div></div><span class="icon-box tone-warning"><i class="bi bi-box-seam"></i></span></div>
            <div class="kpi-foot"><span>qty × cost_price</span></div>
        </div>
    </div>
    <div class="col-6 col-xl-3">
        <div class="card kpi-card grad-mint">
            <div class="kpi-top"><div class="min-w-0"><div class="kpi-label">Laba kotor (30d)</div><div class="kpi-value">{{ tillora_rp($profit) }}</div></div><span class="icon-box tone-success"><i class="bi bi-cash-coin"></i></span></div>
            <div class="kpi-foot"><span>revenue − HPP</span></div>
        </div>
    </div>
    <div class="col-6 col-xl-3">
        <div class="card kpi-card grad-cyan">
            <div class="kpi-top"><div class="min-w-0"><div class="kpi-label">Margin keseluruhan</div><div class="kpi-value">{{ number_format($margin,1) }}%</div></div><span class="icon-box tone-cyan"><i class="bi bi-pie-chart"></i></span></div>
            <div class="kpi-foot"><span>laba ÷ revenue</span></div>
        </div>
    </div>
</div>

<div class="card mb-4">
    <div class="card-header"><div><h2 class="card-title">Margin per kategori</h2><p class="card-subtitle">Laba &amp; margin % · 30 hari · data asli</p></div></div>
    <div class="card-body"><div class="chart-box chart-sm"><canvas id="pmCat"></canvas></div></div>
</div>

<div class="row g-app">
    <div class="col-xl-7">
        <div class="card table-card">
            <div class="card-header"><div><h2 class="card-title">20 produk margin tertinggi</h2><p class="card-subtitle">Berdasarkan laba kotor · 30 hari</p></div></div>
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead><tr><th class="col-main">Produk</th><th class="table-num">Revenue</th><th class="table-num">HPP</th><th class="table-num">Laba</th><th class="table-num">Margin</th></tr></thead>
                    <tbody>
                        @forelse($topProducts as $row)
                        <tr>
                            <td><span class="cell-title">{{ $row->product?->name ?? 'Produk #'.$row->product_id }}</span><span class="cell-sub">{{ number_format($row->qty,0,',','.') }} terjual</span></td>
                            <td class="table-num">{{ tillora_rp($row->revenue) }}</td>
                            <td class="table-num text-muted">{{ tillora_rp($row->cogs) }}</td>
                            <td class="table-num fw-semibold text-success">{{ tillora_rp($row->profit) }}</td>
                            <td class="table-num"><span class="status-badge status-success">{{ number_format($row->margin,1) }}%</span></td>
                        </tr>
                        @empty
                        <tr><td colspan="5" class="text-center text-muted py-4">Belum ada penjualan 30 hari terakhir.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <div class="col-xl-5">
        <div class="card table-card">
            <div class="card-header"><div><h2 class="card-title">10 margin terendah</h2><p class="card-subtitle">Perlu perhatian harga</p></div></div>
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead><tr><th class="col-main">Produk</th><th class="table-num">Laba</th><th class="table-num">Margin</th></tr></thead>
                    <tbody>
                        @forelse($lowProducts as $row)
                        <tr>
                            <td><span class="cell-title">{{ $row->product?->name ?? 'Produk #'.$row->product_id }}</span><span class="cell-sub">{{ tillora_rp($row->revenue) }} revenue</span></td>
                            <td class="table-num fw-semibold {{ $row->profit < 0 ? 'text-danger' : '' }}">{{ tillora_rp($row->profit) }}</td>
                            <td class="table-num"><span class="status-badge {{ $row->margin < 0 ? 'status-danger' : 'status-neutral' }}">{{ number_format($row->margin,1) }}%</span></td>
                        </tr>
                        @empty
                        <tr><td colspan="3" class="text-center text-muted py-4">Belum ada penjualan 30 hari terakhir.</td></tr>
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
  new Chart(document.getElementById('pmCat'), {type:'bar',
    data:{labels:@json($catNames),datasets:[
      {label:'Laba',data:@json($catProfits),backgroundColor:'#10B981',borderRadius:4},
      {label:'Margin %',data:@json($catMargins),backgroundColor:'#4F46E5',borderRadius:4}]},
    options:{indexAxis:'y',plugins:{legend:{display:false}},scales:{x:{ticks:{callback:v=>v>=1e6?(v/1e6)+'jt':v>=1e3?(v/1e3)+'rb':v}}}}});
})();
</script>
@endpush
