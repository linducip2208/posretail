@extends('app.layout')
@section('title','Aset Perusahaan')

@section('content')
<div class="page-header">
    <div>
        <nav aria-label="Breadcrumb"><ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/app"><i class="bi bi-house-door"></i> Home</a></li>
            <li class="breadcrumb-item"><a href="/app/reports-overview">Keuangan</a></li>
            <li class="breadcrumb-item active" aria-current="page">Company assets</li>
        </ol></nav>
        <h1 class="page-title">Aset Perusahaan</h1>
        <p class="page-subtitle">Inventaris aset tetap · nilai perolehan &amp; nilai kini · data asli.</p>
    </div>
    <div class="page-actions">
        <a class="btn btn-light" href="/app/budgets"><i class="bi bi-piggy-bank"></i> Anggaran</a>
        <a class="btn btn-primary" href="/admin/company-assets/create"><i class="bi bi-plus-lg"></i> Tambah aset</a>
    </div>
</div>

<div class="row g-app mb-4">
    <div class="col-6 col-xl-4">
        <div class="card kpi-card grad-indigo">
            <div class="kpi-top"><div class="min-w-0"><div class="kpi-label">Nilai perolehan</div><div class="kpi-value">{{ tillora_rp($totalPurchase) }}</div></div><span class="icon-box tone-primary"><i class="bi bi-safe"></i></span></div>
            <div class="kpi-foot"><span>total harga beli aset</span></div>
        </div>
    </div>
    <div class="col-6 col-xl-4">
        <div class="card kpi-card grad-cyan">
            <div class="kpi-top"><div class="min-w-0"><div class="kpi-label">Nilai kini</div><div class="kpi-value">{{ tillora_rp($totalCurrent) }}</div></div><span class="icon-box tone-cyan"><i class="bi bi-bank"></i></span></div>
            <div class="kpi-foot"><span>setelah penyusutan</span></div>
        </div>
    </div>
    <div class="col-6 col-xl-4">
        <div class="card kpi-card grad-mint">
            <div class="kpi-top"><div class="min-w-0"><div class="kpi-label">Total aset</div><div class="kpi-value">{{ number_format($totalCount,0,',','.') }}</div></div><span class="icon-box tone-success"><i class="bi bi-box-seam"></i></span></div>
            <div class="kpi-foot"><span>{{ number_format($needMaintenance,0,',','.') }} perlu perbaikan</span></div>
        </div>
    </div>
</div>

<div class="row g-app">
    <div class="col-xl-4">
        <div class="card mb-4">
            <div class="card-header"><div><h2 class="card-title">Nilai per kategori</h2><p class="card-subtitle">Berdasarkan nilai perolehan</p></div></div>
            <div class="card-body">
                <div class="chart-box"><canvas id="assetCatChart"></canvas></div>
                <ul class="legend-list mt-3">
                    @forelse($catRows as $c)
                    <li><span class="legend-dot" style="background:#6366f1"></span><span>{{ $c['category'] }} ({{ number_format($c['count'],0,',','.') }})</span><strong class="ms-auto">{{ tillora_rp($c['total']) }}</strong></li>
                    @empty
                    <li class="text-muted">Belum ada data kategori aset.</li>
                    @endforelse
                </ul>
            </div>
        </div>
    </div>
    <div class="col-xl-8">
        <div class="card table-card">
            <div class="card-header"><div><h2 class="card-title">Daftar aset</h2><p class="card-subtitle">Kode · kategori · tanggal beli · kondisi</p></div></div>
            <div class="table-responsive" style="max-height:560px;overflow:auto;">
                <table class="table table-hover align-middle">
                    <thead><tr><th>Kode</th><th>Nama</th><th>Kategori</th><th>Tgl beli</th><th class="table-num">Nilai perolehan</th><th class="table-num">Nilai kini</th><th>Kondisi</th><th>Lokasi</th></tr></thead>
                    <tbody>
                        @forelse($rows as $a)
                        <tr>
                            <td class="fw-semibold">{{ $a->asset_code }}</td>
                            <td>{{ $a->name }}<div class="small text-muted">{{ $a->assignedUser ? 'Dipakai ' . $a->assignedUser->name : '' }}</div></td>
                            <td><span class="badge bg-light text-dark">{{ $a->category ?: 'Lainnya' }}</span></td>
                            <td>{{ $a->purchase_date ? $a->purchase_date->format('d M Y') : '-' }}</td>
                            <td class="table-num">{{ tillora_rp($a->purchase_value) }}</td>
                            <td class="table-num fw-semibold">{{ tillora_rp($a->current_value) }}</td>
                            <td>
                                @if($a->status === 'baik')
                                <span class="badge bg-success">Baik</span>
                                @elseif($a->status === 'perlu_perbaikan')
                                <span class="badge bg-warning text-dark">Perlu perbaikan</span>
                                @elseif($a->status === 'rusak')
                                <span class="badge bg-danger">Rusak</span>
                                @else
                                <span class="badge bg-secondary">{{ $a->status ?: 'Unknown' }}</span>
                                @endif
                            </td>
                            <td class="small text-muted">{{ $a->location ?: ($a->outlet ? $a->outlet->name : '-') }}</td>
                        </tr>
                        @empty
                        <tr><td colspan="8" class="text-center text-muted py-4">Belum ada aset tercatat.</td></tr>
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
  new Chart(document.getElementById('assetCatChart'), {type:'doughnut',
    data:{labels:@json($catLabels), datasets:[{data:@json($catValues), backgroundColor:['#6366f1','#06b6d4','#10b981','#f59e0b','#ef4444','#8b5cf6']}]},
    options:{plugins:{legend:{display:false}}}});
})();
</script>
@endpush
