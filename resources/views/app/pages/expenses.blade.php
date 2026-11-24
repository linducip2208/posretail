@extends('app.layout')
@section('title','Biaya Operasional')

@section('content')
<div class="page-header">
    <div>
        <nav aria-label="Breadcrumb"><ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/app"><i class="bi bi-house-door"></i> Home</a></li>
            <li class="breadcrumb-item"><a href="/app/reports-overview">Keuangan</a></li>
            <li class="breadcrumb-item active" aria-current="page">Expenses</li>
        </ol></nav>
        <h1 class="page-title">Biaya Operasional</h1>
        <p class="page-subtitle">60 pengeluaran terbaru · tren 30 hari · data asli.</p>
    </div>
    <div class="page-actions">
        <a class="btn btn-light" href="/app/budgets"><i class="bi bi-piggy-bank"></i> Anggaran</a>
        <a class="btn btn-primary" href="/admin/expenses/create"><i class="bi bi-plus-lg"></i> Tambah biaya</a>
    </div>
</div>

<div class="row g-app mb-4">
    <div class="col-6 col-xl-4">
        <div class="card kpi-card grad-indigo">
            <div class="kpi-top"><div class="min-w-0"><div class="kpi-label">Total bulan ini</div><div class="kpi-value">{{ tillora_rp($totalMonth) }}</div></div><span class="icon-box tone-primary"><i class="bi bi-wallet2"></i></span></div>
            <div class="kpi-foot"><span>{{ number_format($countMonth,0,',','.') }} transaksi bulan ini</span></div>
        </div>
    </div>
    <div class="col-6 col-xl-4">
        <div class="card kpi-card grad-cyan">
            <div class="kpi-top"><div class="min-w-0"><div class="kpi-label">Total keseluruhan</div><div class="kpi-value">{{ tillora_rp($totalAll) }}</div></div><span class="icon-box tone-cyan"><i class="bi bi-cash-stack"></i></span></div>
            <div class="kpi-foot"><span>{{ number_format($totalCount,0,',','.') }} catatan biaya</span></div>
        </div>
    </div>
    <div class="col-6 col-xl-4">
        <div class="card kpi-card grad-mint">
            <div class="kpi-top"><div class="min-w-0"><div class="kpi-label">Kategori terpakai</div><div class="kpi-value">{{ number_format(count($catRows),0,',','.') }}</div></div><span class="icon-box tone-success"><i class="bi bi-tags"></i></span></div>
            <div class="kpi-foot"><span>berdasarkan kolom kategori</span></div>
        </div>
    </div>
</div>

<div class="row g-app">
    <div class="col-xl-5">
        <div class="card mb-4">
            <div class="card-header"><div><h2 class="card-title">Biaya per kategori</h2><p class="card-subtitle">Donat dari database</p></div></div>
            <div class="card-body">
                <div class="chart-box"><canvas id="expCatChart"></canvas></div>
                <ul class="legend-list mt-3">
                    @forelse($catRows as $c)
                    <li><span class="legend-dot" style="background:#6366f1"></span><span>{{ $c['category'] }} ({{ number_format($c['count'],0,',','.') }})</span><strong class="ms-auto">{{ tillora_rp($c['total']) }}</strong></li>
                    @empty
                    <li class="text-muted">Belum ada data kategori.</li>
                    @endforelse
                </ul>
            </div>
        </div>
    </div>
    <div class="col-xl-7">
        <div class="card mb-4">
            <div class="card-header"><div><h2 class="card-title">Tren 30 hari</h2><p class="card-subtitle">Total biaya harian · data asli</p></div></div>
            <div class="card-body"><div class="chart-box"><canvas id="expTrend"></canvas></div></div>
        </div>
    </div>
</div>

<div class="card table-card">
    <div class="card-header"><div><h2 class="card-title">Pengeluaran terbaru</h2><p class="card-subtitle">60 terakhir · terbaru dulu</p></div></div>
    <div class="table-responsive" style="max-height:560px;overflow:auto;">
        <table class="table table-hover align-middle">
            <thead><tr><th>Tanggal</th><th>Kategori</th><th>Keterangan</th><th>Outlet</th><th>Oleh</th><th class="table-num">Jumlah</th></tr></thead>
            <tbody>
                @forelse($rows as $e)
                <tr>
                    <td>{{ $e->expense_date ? $e->expense_date->format('d M Y') : '-' }}</td>
                    <td><span class="badge bg-light text-dark">{{ $e->category ?: 'Lainnya' }}</span></td>
                    <td class="small">{{ $e->description ?: ($e->notes ?: '-') }}</td>
                    <td class="small text-muted">{{ $e->outlet ? $e->outlet->name : '-' }}</td>
                    <td class="small text-muted">{{ $e->user ? $e->user->name : '-' }}</td>
                    <td class="table-num fw-semibold">{{ tillora_rp($e->amount) }}</td>
                </tr>
                @empty
                <tr><td colspan="6" class="text-center text-muted py-4">Belum ada data pengeluaran.</td></tr>
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
  new Chart(document.getElementById('expCatChart'), {type:'doughnut',
    data:{labels:@json($catLabels), datasets:[{data:@json($catValues), backgroundColor:['#6366f1','#06b6d4','#10b981','#f59e0b','#ef4444','#8b5cf6','#ec4899','#14b8a6']}]},
    options:{plugins:{legend:{display:false}}}});
  new Chart(document.getElementById('expTrend'), {type:'line',
    data:{labels:@json($trendLabels), datasets:[{label:'Biaya',data:@json($trendValues),borderColor:'#4F46E5',backgroundColor:'rgba(79,70,229,.12)',fill:true,tension:.4,cubicInterpolationMode:'monotone',pointRadius:0}]},
    options:{plugins:{legend:{display:false}}}});
})();
</script>
@endpush
