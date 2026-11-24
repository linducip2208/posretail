@extends('app.layout')
@section('title','Anggaran')

@section('content')
<div class="page-header">
    <div>
        <nav aria-label="Breadcrumb"><ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/app"><i class="bi bi-house-door"></i> Home</a></li>
            <li class="breadcrumb-item"><a href="/app/reports-overview">Keuangan</a></li>
            <li class="breadcrumb-item active" aria-current="page">Budgets</li>
        </ol></nav>
        <h1 class="page-title">Anggaran</h1>
        <p class="page-subtitle">Target vs realisasi per periode outlet · data asli.</p>
    </div>
    <div class="page-actions">
        <a class="btn btn-light" href="/app/expenses"><i class="bi bi-wallet2"></i> Biaya</a>
        <a class="btn btn-primary" href="/admin/budgets/create"><i class="bi bi-plus-lg"></i> Tambah anggaran</a>
    </div>
</div>

<div class="row g-app mb-4">
    <div class="col-6 col-xl-3">
        <div class="card kpi-card grad-indigo">
            <div class="kpi-top"><div class="min-w-0"><div class="kpi-label">Target pendapatan</div><div class="kpi-value">{{ tillora_rp($totalRevenueTarget) }}</div></div><span class="icon-box tone-primary"><i class="bi bi-graph-up-arrow"></i></span></div>
            <div class="kpi-foot"><span>aktual {{ tillora_rp($totalActualRevenue) }}</span></div>
        </div>
    </div>
    <div class="col-6 col-xl-3">
        <div class="card kpi-card grad-cyan">
            <div class="kpi-top"><div class="min-w-0"><div class="kpi-label">Batas belanja</div><div class="kpi-value">{{ tillora_rp($totalExpenseLimit) }}</div></div><span class="icon-box tone-cyan"><i class="bi bi-piggy-bank"></i></span></div>
            <div class="kpi-foot"><span>aktual {{ tillora_rp($totalActualExpense) }}</span></div>
        </div>
    </div>
    <div class="col-6 col-xl-3">
        <div class="card kpi-card grad-mint">
            <div class="kpi-top"><div class="min-w-0"><div class="kpi-label">Realisasi pendapatan</div><div class="kpi-value">{{ tillora_rp($totalActualRevenue) }}</div></div><span class="icon-box tone-success"><i class="bi bi-cash-coin"></i></span></div>
            <div class="kpi-foot"><span>dari seluruh periode</span></div>
        </div>
    </div>
    <div class="col-6 col-xl-3">
        <div class="card kpi-card grad-amber">
            <div class="kpi-top"><div class="min-w-0"><div class="kpi-label">Jumlah periode</div><div class="kpi-value">{{ number_format($totalCount,0,',','.') }}</div></div><span class="icon-box tone-warning"><i class="bi bi-calendar3"></i></span></div>
            <div class="kpi-foot"><span>anggaran tercatat</span></div>
        </div>
    </div>
</div>

<div class="card mb-4">
    <div class="card-header"><div><h2 class="card-title">Target vs aktual pendapatan</h2><p class="card-subtitle">12 periode · data asli</p></div></div>
    <div class="card-body"><div class="chart-box chart-lg"><canvas id="budChart"></canvas></div></div>
</div>

<div class="card table-card">
    <div class="card-header"><div><h2 class="card-title">Daftar anggaran</h2><p class="card-subtitle">Progress realisasi per periode</p></div></div>
    <div class="table-responsive" style="max-height:560px;overflow:auto;">
        <table class="table table-hover align-middle">
            <thead><tr><th>Periode</th><th>Outlet</th><th class="table-num">Target pendapatan</th><th class="table-num">Aktual</th><th>Progress</th><th class="table-num">Batas belanja</th><th class="table-num">Aktual belanja</th><th>Status</th></tr></thead>
            <tbody>
                @forelse($rows as $b)
                <tr>
                    <td class="fw-semibold">{{ $b->period_label }}</td>
                    <td class="small">{{ $b->outlet ? $b->outlet->name : 'Semua outlet' }}</td>
                    <td class="table-num">{{ tillora_rp($b->revenue_target) }}</td>
                    <td class="table-num">{{ tillora_rp($b->actual_revenue) }}</td>
                    <td style="min-width:140px;"><div class="rank-bar"><div class="rank-fill" style="width: {{ $b->rev_pct }}%;"></div></div><div class="small text-muted mt-1">{{ $b->rev_pct }}%</div></td>
                    <td class="table-num">{{ tillora_rp($b->expense_limit) }}</td>
                    <td class="table-num">{{ tillora_rp($b->actual_expense) }}</td>
                    <td>
                        @if($b->status_label === 'Tercapai')
                        <span class="badge bg-success">Tercapai</span>
                        @elseif($b->status_label === 'Over budget')
                        <span class="badge bg-danger">Over budget</span>
                        @else
                        <span class="badge bg-secondary">Berjalan</span>
                        @endif
                    </td>
                </tr>
                @empty
                <tr><td colspan="8" class="text-center text-muted py-4">Belum ada anggaran. Tambahkan periode anggaran melalui Filament.</td></tr>
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
  new Chart(document.getElementById('budChart'), {type:'bar',
    data:{labels:@json($periodLabels), datasets:[
      {label:'Target',data:@json($revTargets),backgroundColor:'#4F46E5'},
      {label:'Aktual',data:@json($revActuals),backgroundColor:'#06B6D4'}]},
    options:{plugins:{legend:{display:false}}}});
})();
</script>
@endpush
