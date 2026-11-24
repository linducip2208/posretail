@extends('app.layout')
@section('title','Kurs Mata Uang')

@section('content')
<div class="page-header">
    <div>
        <nav aria-label="Breadcrumb"><ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/app"><i class="bi bi-house-door"></i> Home</a></li>
            <li class="breadcrumb-item"><a href="/app/reports-overview">Keuangan</a></li>
            <li class="breadcrumb-item active" aria-current="page">Exchange rates</li>
        </ol></nav>
        <h1 class="page-title">Kurs Mata Uang</h1>
        <p class="page-subtitle">Nilai tukar terhadap rupiah per tanggal berlaku · data asli.</p>
    </div>
    <div class="page-actions">
        <a class="btn btn-light" href="/app/bank-statements"><i class="bi bi-bank"></i> Mutasi bank</a>
        <a class="btn btn-primary" href="/admin/exchange-rates/create"><i class="bi bi-plus-lg"></i> Tambah kurs</a>
    </div>
</div>

<div class="row g-app mb-4">
    <div class="col-6 col-xl-4">
        <div class="card kpi-card grad-indigo">
            <div class="kpi-top"><div class="min-w-0"><div class="kpi-label">Mata uang terpantau</div><div class="kpi-value">{{ number_format($currencyCount,0,',','.') }}</div></div><span class="icon-box tone-primary"><i class="bi bi-currency-exchange"></i></span></div>
            <div class="kpi-foot"><span>kurs terbaru per kode</span></div>
        </div>
    </div>
    <div class="col-6 col-xl-4">
        <div class="card kpi-card grad-cyan">
            <div class="kpi-top"><div class="min-w-0"><div class="kpi-label">Total catatan kurs</div><div class="kpi-value">{{ number_format($totalCount,0,',','.') }}</div></div><span class="icon-box tone-cyan"><i class="bi bi-database"></i></span></div>
            <div class="kpi-foot"><span>seluruh riwayat tanggal</span></div>
        </div>
    </div>
    <div class="col-6 col-xl-4">
        <div class="card kpi-card grad-mint">
            <div class="kpi-top"><div class="min-w-0"><div class="kpi-label">Kelola via Filament</div><div class="kpi-value">Admin</div></div><span class="icon-box tone-success"><i class="bi bi-gear"></i></span></div>
            <div class="kpi-foot"><span>tambah / ubah kurs di panel admin</span></div>
        </div>
    </div>
</div>

<div class="row g-app">
    <div class="col-xl-5">
        <div class="card mb-4">
            <div class="card-header"><div><h2 class="card-title">Kurs terbaru per mata uang</h2><p class="card-subtitle">Terhadap rupiah · data asli</p></div></div>
            <div class="card-body">
                <div class="chart-box"><canvas id="fxChart"></canvas></div>
                <ul class="legend-list mt-3">
                    @forelse($latestList as $l)
                    <li><span class="legend-dot" style="background:#6366f1"></span><span>{{ $l['currency'] }} · {{ $l['date'] }}</span><strong class="ms-auto">{{ tillora_rp($l['rate']) }}</strong></li>
                    @empty
                    <li class="text-muted">Belum ada data kurs.</li>
                    @endforelse
                </ul>
            </div>
        </div>
    </div>
    <div class="col-xl-7">
        <div class="card table-card">
            <div class="card-header"><div><h2 class="card-title">Riwayat kurs</h2><p class="card-subtitle">Tanggal berlaku terbaru dulu</p></div></div>
            <div class="table-responsive" style="max-height:560px;overflow:auto;">
                <table class="table table-hover align-middle">
                    <thead><tr><th>Mata uang</th><th class="table-num">Kurs (Rp)</th><th>Tanggal berlaku</th></tr></thead>
                    <tbody>
                        @forelse($rows as $x)
                        <tr>
                            <td><span class="badge bg-light text-dark">{{ $x->currency }}</span></td>
                            <td class="table-num fw-semibold">{{ tillora_rp($x->rate) }}</td>
                            <td>{{ $x->effective_date ? $x->effective_date->format('d M Y') : '-' }}</td>
                        </tr>
                        @empty
                        <tr><td colspan="3" class="text-center text-muted py-4">Belum ada data kurs. Tambahkan melalui panel admin.</td></tr>
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
  new Chart(document.getElementById('fxChart'), {type:'bar',
    data:{labels:@json($curLabels), datasets:[{data:@json($curValues),backgroundColor:'#6366f1'}]},
    options:{plugins:{legend:{display:false}}}});
})();
</script>
@endpush
