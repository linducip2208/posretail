@extends('app.layout')
@section('title','Chart of Accounts')

@section('content')
<div class="page-header">
    <div>
        <nav aria-label="Breadcrumb"><ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/app"><i class="bi bi-house-door"></i> Home</a></li>
            <li class="breadcrumb-item"><a href="/app/reports-overview">Keuangan</a></li>
            <li class="breadcrumb-item active" aria-current="page">Accounts</li>
        </ol></nav>
        <h1 class="page-title">Chart of Accounts</h1>
        <p class="page-subtitle">Daftar akun akuntansi beserta saldo dari jurnal · data asli.</p>
    </div>
    <div class="page-actions">
        <a class="btn btn-light" href="/app/journal-entries"><i class="bi bi-journal-text"></i> Jurnal</a>
        <a class="btn btn-primary" href="/admin/accounts/create"><i class="bi bi-plus-lg"></i> Tambah akun</a>
    </div>
</div>

<div class="row g-app mb-4">
    <div class="col-6 col-xl-4">
        <div class="card kpi-card grad-indigo">
            <div class="kpi-top"><div class="min-w-0"><div class="kpi-label">Total akun</div><div class="kpi-value">{{ number_format($totalAccounts,0,',','.') }}</div></div><span class="icon-box tone-primary"><i class="bi bi-diagram-3"></i></span></div>
            <div class="kpi-foot"><span>{{ number_format($activeCount,0,',','.') }} akun aktif</span></div>
        </div>
    </div>
    <div class="col-6 col-xl-4">
        <div class="card kpi-card grad-cyan">
            <div class="kpi-top"><div class="min-w-0"><div class="kpi-label">Kelompok tipe</div><div class="kpi-value">{{ number_format(count($typeRows),0,',','.') }}</div></div><span class="icon-box tone-cyan"><i class="bi bi-tags"></i></span></div>
            <div class="kpi-foot"><span>aset · kewajiban · modal · pendapatan · beban</span></div>
        </div>
    </div>
    <div class="col-6 col-xl-4">
        <div class="card kpi-card grad-mint">
            <div class="kpi-top"><div class="min-w-0"><div class="kpi-label">Akun ditampilkan</div><div class="kpi-value">{{ number_format(count($rows),0,',','.') }}</div></div><span class="icon-box tone-success"><i class="bi bi-list-check"></i></span></div>
            <div class="kpi-foot"><span>hasil filter saat ini</span></div>
        </div>
    </div>
</div>

<div class="row g-app">
    <div class="col-xl-4">
        <div class="card mb-4">
            <div class="card-header"><div><h2 class="card-title">Jumlah akun per tipe</h2><p class="card-subtitle">Ringkasan dari database</p></div></div>
            <div class="card-body">
                <div class="chart-box"><canvas id="accTypeChart"></canvas></div>
                <ul class="legend-list mt-3">
                    @forelse($typeRows as $t)
                    <li><span class="legend-dot" style="background:#6366f1"></span><span>{{ $t['type'] }}</span><strong class="ms-auto">{{ number_format($t['count'],0,',','.') }}</strong></li>
                    @empty
                    <li class="text-muted">Belum ada akun.</li>
                    @endforelse
                </ul>
            </div>
        </div>
    </div>
    <div class="col-xl-8">
        <div class="card table-card">
            <div class="card-header">
                <div><h2 class="card-title">Daftar akun</h2><p class="card-subtitle">Kode · nama · tipe · saldo jurnal</p></div>
                <form method="GET" action="/app/accounts" class="d-flex gap-2">
                    <input type="search" name="q" value="{{ $q }}" class="form-control form-control-sm" placeholder="Cari kode / nama / tipe...">
                    <button class="btn btn-light btn-sm" type="submit"><i class="bi bi-search"></i></button>
                </form>
            </div>
            <div class="table-responsive" style="max-height:560px;overflow:auto;">
                <table class="table table-hover align-middle">
                    <thead><tr><th>Kode</th><th>Nama</th><th>Tipe</th><th>Induk</th><th>Status</th><th class="table-num">Debit</th><th class="table-num">Kredit</th><th class="table-num">Saldo</th></tr></thead>
                    <tbody>
                        @forelse($rows as $a)
                        <tr>
                            <td class="fw-semibold">{{ $a->code }}</td>
                            <td>{{ $a->name }}</td>
                            <td><span class="badge bg-light text-dark">{{ $a->type }}</span></td>
                            <td class="text-muted small">{{ $a->parent ? $a->parent->name : '-' }}</td>
                            <td>
                                @if($a->active)
                                <span class="badge bg-success">Aktif</span>
                                @else
                                <span class="badge bg-secondary">Nonaktif</span>
                                @endif
                            </td>
                            <td class="table-num">{{ tillora_rp($a->total_debit) }}</td>
                            <td class="table-num">{{ tillora_rp($a->total_credit) }}</td>
                            <td class="table-num fw-semibold">{{ tillora_rp($a->saldo) }}</td>
                        </tr>
                        @empty
                        <tr><td colspan="8" class="text-center text-muted py-4">Belum ada akun. Tambahkan akun baru melalui Filament.</td></tr>
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
  new Chart(document.getElementById('accTypeChart'), {type:'doughnut',
    data:{labels:@json($typeLabels), datasets:[{data:@json($typeValues), backgroundColor:['#6366f1','#06b6d4','#10b981','#f59e0b','#ef4444','#8b5cf6']}]},
    options:{plugins:{legend:{display:false}}}});
})();
</script>
@endpush
