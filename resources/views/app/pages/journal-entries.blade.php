@extends('app.layout')
@section('title','Jurnal Umum')

@section('content')
<div class="page-header">
    <div>
        <nav aria-label="Breadcrumb"><ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/app"><i class="bi bi-house-door"></i> Home</a></li>
            <li class="breadcrumb-item"><a href="/app/accounts">Keuangan</a></li>
            <li class="breadcrumb-item active" aria-current="page">Journal entries</li>
        </ol></nav>
        <h1 class="page-title">Jurnal Umum</h1>
        <p class="page-subtitle">100 jurnal terbaru beserta rincian debit &amp; kredit · data asli.</p>
    </div>
    <div class="page-actions">
        <a class="btn btn-light" href="/app/accounts"><i class="bi bi-diagram-3"></i> Akun</a>
        <a class="btn btn-primary" href="/admin/journal-entries/create"><i class="bi bi-plus-lg"></i> Tambah jurnal</a>
    </div>
</div>

<div class="row g-app mb-4">
    <div class="col-6 col-xl-3">
        <div class="card kpi-card grad-indigo">
            <div class="kpi-top"><div class="min-w-0"><div class="kpi-label">Total debit periode</div><div class="kpi-value">{{ tillora_rp($totalDebit) }}</div></div><span class="icon-box tone-primary"><i class="bi bi-arrow-down-circle"></i></span></div>
            <div class="kpi-foot"><span>filter tanggal berjalan</span></div>
        </div>
    </div>
    <div class="col-6 col-xl-3">
        <div class="card kpi-card grad-cyan">
            <div class="kpi-top"><div class="min-w-0"><div class="kpi-label">Total kredit periode</div><div class="kpi-value">{{ tillora_rp($totalCredit) }}</div></div><span class="icon-box tone-cyan"><i class="bi bi-arrow-up-circle"></i></span></div>
            <div class="kpi-foot"><span>filter tanggal berjalan</span></div>
        </div>
    </div>
    <div class="col-6 col-xl-3">
        <div class="card kpi-card grad-mint">
            <div class="kpi-top"><div class="min-w-0"><div class="kpi-label">Sudah posting</div><div class="kpi-value">{{ number_format($countPosted,0,',','.') }}</div></div><span class="icon-box tone-success"><i class="bi bi-check-circle"></i></span></div>
            <div class="kpi-foot"><span>{{ number_format($countDraft,0,',','.') }} masih draft</span></div>
        </div>
    </div>
    <div class="col-6 col-xl-3">
        <div class="card kpi-card grad-amber">
            <div class="kpi-top"><div class="min-w-0"><div class="kpi-label">Jurnal ditampilkan</div><div class="kpi-value">{{ number_format($totalCount,0,',','.') }}</div></div><span class="icon-box tone-warning"><i class="bi bi-journal-text"></i></span></div>
            <div class="kpi-foot"><span>maksimal 100 terbaru</span></div>
        </div>
    </div>
</div>

<div class="card mb-4">
    <div class="card-header"><div><h2 class="card-title">Arus debit &amp; kredit harian</h2><p class="card-subtitle">14 hari terakhir · data asli</p></div></div>
    <div class="card-body">
        <div class="chart-box chart-lg"><canvas id="jeTrend"></canvas></div>
        <div class="chart-legend"><span><i class="legend-dot" style="background: var(--app-primary);"></i>Debit</span><span><i class="legend-dot" style="background: #06B6D4;"></i>Kredit</span></div>
    </div>
</div>

<div class="card table-card">
    <div class="card-header">
        <div><h2 class="card-title">Daftar jurnal</h2><p class="card-subtitle">Terbaru dulu · klik akun untuk detail COA</p></div>
        <form method="GET" action="/app/journal-entries" class="d-flex gap-2 align-items-center">
            <input type="date" name="from" value="{{ $from }}" class="form-control form-control-sm">
            <span class="text-muted small">s/d</span>
            <input type="date" name="to" value="{{ $to }}" class="form-control form-control-sm">
            <button class="btn btn-light btn-sm" type="submit"><i class="bi bi-funnel"></i></button>
        </form>
    </div>
    <div class="table-responsive" style="max-height:560px;overflow:auto;">
        <table class="table table-hover align-middle">
            <thead><tr><th>Nomor</th><th>Tanggal</th><th>Keterangan</th><th>Status</th><th class="table-num">Debit</th><th class="table-num">Kredit</th><th>Rincian akun</th></tr></thead>
            <tbody>
                @forelse($rows as $j)
                <tr>
                    <td class="fw-semibold">{{ $j->journal_number }}</td>
                    <td>{{ $j->journal_date ? $j->journal_date->format('d M Y') : '-' }}</td>
                    <td class="small">{{ $j->description ?: '-' }}</td>
                    <td>
                        @if($j->status === 'posted')
                        <span class="badge bg-success">Posted</span>
                        @else
                        <span class="badge bg-secondary">{{ $j->status }}</span>
                        @endif
                    </td>
                    <td class="table-num">{{ tillora_rp($j->items->sum('debit')) }}</td>
                    <td class="table-num">{{ tillora_rp($j->items->sum('credit')) }}</td>
                    <td class="small text-muted">
                        @forelse($j->items as $it)
                        <div>{{ $it->account ? $it->account->code . ' · ' . $it->account->name : 'Akun #' . $it->account_id }}</div>
                        @empty
                        <div>-</div>
                        @endforelse
                    </td>
                </tr>
                @empty
                <tr><td colspan="7" class="text-center text-muted py-4">Belum ada jurnal pada periode ini.</td></tr>
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
  new Chart(document.getElementById('jeTrend'), {type:'bar',
    data:{labels:@json($trendLabels), datasets:[
      {label:'Debit',data:@json($trendDebit),backgroundColor:'#4F46E5'},
      {label:'Kredit',data:@json($trendCredit),backgroundColor:'#06B6D4'}]},
    options:{plugins:{legend:{display:false}}}});
})();
</script>
@endpush
