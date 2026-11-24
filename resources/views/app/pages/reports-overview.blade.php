@extends('app.layout')
@section('title','Reports Overview')



@section('content')
<div class="page-header">
    <div>
        <nav aria-label="Breadcrumb"><ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/app"><i class="bi bi-house-door"></i> Home</a></li>
            <li class="breadcrumb-item"><a href="/app/reports-overview">Reports</a></li>
            <li class="breadcrumb-item active" aria-current="page">Overview</li>
        </ol></nav>
        <h1 class="page-title">Reports</h1>
        <p class="page-subtitle">Semua laporan dalam satu tempat · angka 30 hari terakhir dari data asli.</p>
    </div>
    <div class="page-actions">
        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#scheduleModal"><i class="bi bi-calendar-plus"></i>Schedule report</button>
    </div>
</div>

<div class="row g-app mb-4">
    <div class="col-6 col-xl-3">
        <div class="card kpi-card grad-indigo">
            <div class="kpi-top"><div class="min-w-0"><div class="kpi-label">Revenue (30d)</div><div class="kpi-value">{{ tillora_rp($rev30) }}</div></div><span class="icon-box tone-primary"><i class="bi bi-graph-up-arrow"></i></span></div>
            <div class="kpi-foot"><span class="kpi-delta {{ $pctRev>=0?'is-up':'is-down' }}"><i class="bi bi-arrow-{{ $pctRev>=0?'up':'down' }}-right"></i>{{ number_format(abs($pctRev),1) }}%</span><span>vs 30 hari sebelumnya</span></div>
            <div class="kpi-spark"><canvas id="roSpark"></canvas></div>
        </div>
    </div>
    <div class="col-6 col-xl-3">
        <div class="card kpi-card grad-mint">
            <div class="kpi-top"><div class="min-w-0"><div class="kpi-label">Laba kotor (30d)</div><div class="kpi-value">{{ tillora_rp($profit30) }}</div></div><span class="icon-box tone-success"><i class="bi bi-cash-coin"></i></span></div>
            <div class="kpi-foot"><span>{{ number_format($margin30,1) }}% margin</span></div>
        </div>
    </div>
    <div class="col-6 col-xl-3">
        <div class="card kpi-card grad-cyan">
            <div class="kpi-top"><div class="min-w-0"><div class="kpi-label">Transaksi (30d)</div><div class="kpi-value">{{ number_format($cnt30,0,',','.') }}</div></div><span class="icon-box tone-cyan"><i class="bi bi-bag-check"></i></span></div>
            <div class="kpi-foot"><span class="kpi-delta {{ $pctCnt>=0?'is-up':'is-down' }}"><i class="bi bi-arrow-{{ $pctCnt>=0?'up':'down' }}-right"></i>{{ number_format(abs($pctCnt),1) }}%</span><span>vs 30 hari sebelumnya</span></div>
        </div>
    </div>
    <div class="col-6 col-xl-3">
        <div class="card kpi-card grad-rose">
            <div class="kpi-top"><div class="min-w-0"><div class="kpi-label">Tingkat retur</div><div class="kpi-value">{{ number_format($retRate,1) }}%</div></div><span class="icon-box tone-warning"><i class="bi bi-arrow-return-left"></i></span></div>
            <div class="kpi-foot"><span>{{ number_format($retCount,0,',','.') }} retur · 30 hari</span></div>
        </div>
    </div>
</div>

<div class="row g-app mb-4">
    @php
    $cards = [
        ['Sales reports','Ringkasan harian & produk terlaris','/app/sales-reports','bi-receipt','tone-primary'],
        ['Product performance','Produk paling menguntungkan','/app/product-performance','bi-trophy','tone-success'],
        ['Inventory reports','Stok, mutasi & nilai persediaan','/app/inventory-reports','bi-boxes','tone-cyan'],
        ['Profit margins','Margin per produk & kategori','/app/profit-margins','bi-pie-chart','tone-warning'],
        ['Sales overview','Tren revenue & keranjang','/app/sales-overview','bi-graph-up','tone-primary'],
        ['Store performance','Perbandingan antar outlet','/app/store-performance','bi-shop','tone-success'],
        ['Business overview','Ringkasan bisnis menyeluruh','/app/business-overview','bi-speedometer2','tone-cyan'],
    ];
    @endphp
    @foreach($cards as $c)
    <div class="col-md-6 col-xl-4">
        <a href="{{ $c[2] }}" class="card report-link h-100 text-decoration-none">
            <div class="card-body d-flex align-items-center gap-3">
                <span class="icon-box {{ $c[4] }}"><i class="bi {{ $c[3] }}"></i></span>
                <div class="min-w-0"><div class="fw-semibold text-heading">{{ $c[0] }}</div><div class="small text-muted">{{ $c[1] }}</div></div>
                <i class="bi bi-chevron-right ms-auto text-muted"></i>
            </div>
        </a>
    </div>
    @endforeach
</div>

<div class="modal fade" id="scheduleModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog"><div class="modal-content">
        <div class="modal-header"><h5 class="modal-title">Schedule report</h5><button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button></div>
        <div class="modal-body">
            <div class="alert-strip is-info"><i class="bi bi-info-circle"></i><span class="small">Penjadwalan laporan lewat email belum tersedia — tombol di bawah nonaktif. Unduh laporan langsung dari masing-masing halaman laporan.</span></div>
            <div class="mb-3"><label class="form-label">Laporan</label><select class="form-select" disabled><option>Laporan penjualan harian</option></select></div>
            <div class="mb-3"><label class="form-label">Frekuensi</label><select class="form-select" disabled><option>Harian</option></select></div>
        </div>
        <div class="modal-footer"><button type="button" class="btn btn-light" data-bs-dismiss="modal">Tutup</button><button type="button" class="btn btn-primary" disabled><i class="bi bi-calendar-plus"></i>Simpan jadwal</button></div>
    </div></div>
</div>
@endsection

@push('scripts')
<script>
(function(){
  if (!window.Chart) return;
  new Chart(document.getElementById('roSpark'), {type:'line',
    data:{labels:@json($sparkLabels),datasets:[{data:@json($sparkData),borderColor:'#fff',backgroundColor:'rgba(255,255,255,.25)',fill:true,tension:0,pointRadius:0}]},
    options:{plugins:{legend:{display:false},tooltip:{enabled:false}},scales:{x:{display:false},y:{display:false}},elements:{line:{borderWidth:2}}}});
})();
</script>
@endpush
