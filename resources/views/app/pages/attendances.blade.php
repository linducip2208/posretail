@extends('app.layout')
@section('title','Attendances')


@section('content')
<div class="page-header">
    <div>
        <nav aria-label="Breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="/app"><i class="bi bi-house-door"></i> Home</a></li>
                <li class="breadcrumb-item"><a href="/app/attendances">People</a></li>
                <li class="breadcrumb-item active" aria-current="page">Attendances</li>
            </ol>
        </nav>
        <h1 class="page-title">Kehadiran staf</h1>
        <p class="page-subtitle">Rekap kehadiran hari ini {{ $todayDate }} dan riwayat 60 catatan terakhir.</p>
    </div>
    <div class="page-actions">
        <a class="btn btn-primary" href="/admin/attendances/create"><i class="bi bi-plus-lg"></i>Tambah</a>
    </div>
</div>

<div class="row g-app mb-4">
    <div class="col-6 col-xl-3">
        <div class="card kpi-card grad-indigo">
            <div class="kpi-top">
                <div class="min-w-0">
                    <div class="kpi-label">Hadir hari ini</div>
                    <div class="kpi-value">{{ number_format($todayHadir) }}</div>
                </div>
                <span class="icon-box tone-primary"><i class="bi bi-person-check"></i></span>
            </div>
            <div class="kpi-foot"><span>staf tepat waktu</span></div>
        </div>
    </div>
    <div class="col-6 col-xl-3">
        <div class="card kpi-card grad-amber">
            <div class="kpi-top">
                <div class="min-w-0">
                    <div class="kpi-label">Terlambat</div>
                    <div class="kpi-value">{{ number_format($todayTerlambat) }}</div>
                </div>
                <span class="icon-box tone-warning"><i class="bi bi-alarm"></i></span>
            </div>
            <div class="kpi-foot"><span>staf datang terlambat</span></div>
        </div>
    </div>
    <div class="col-6 col-xl-3">
        <div class="card kpi-card grad-rose">
            <div class="kpi-top">
                <div class="min-w-0">
                    <div class="kpi-label">Alfa</div>
                    <div class="kpi-value">{{ number_format($todayAlfa) }}</div>
                </div>
                <span class="icon-box tone-danger"><i class="bi bi-person-x"></i></span>
            </div>
            <div class="kpi-foot"><span>tanpa keterangan hari ini</span></div>
        </div>
    </div>
    <div class="col-6 col-xl-3">
        <div class="card kpi-card grad-cyan">
            <div class="kpi-top">
                <div class="min-w-0">
                    <div class="kpi-label">Tercatat hari ini</div>
                    <div class="kpi-value">{{ number_format($todayTotal) }}</div>
                </div>
                <span class="icon-box tone-cyan"><i class="bi bi-clipboard-check"></i></span>
            </div>
            <div class="kpi-foot"><span>total catatan kehadiran</span></div>
        </div>
    </div>
</div>

<div class="card mb-4">
    <div class="card-body">
        <h6 class="card-title mb-3">Komposisi status · 30 hari terakhir</h6>
        <div style="height:220px;"><canvas id="attStatus"></canvas></div>
    </div>
</div>

<div class="card table-card" data-table data-page-size="10">
    <div class="table-toolbar">
        <div class="input-icon">
            <i class="bi bi-search"></i>
            <input type="search" class="form-control" placeholder="Cari staf, outlet, status…" data-table-search aria-label="Cari kehadiran">
        </div>
    </div>
    <div class="table-responsive">
        <table class="table table-hover align-middle">
            <thead>
                <tr>
                    <th class="col-main">Staf</th>
                    <th>Tanggal</th>
                    <th>Jam masuk</th>
                    <th>Jam keluar</th>
                    <th>Outlet</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                @forelse($attendances as $a)
                @php
                    $badge = 'status-info';
                    $st = strtolower($a->status ?: '');
                    if ($st === 'hadir' || $st === 'present' || $st === 'masuk') { $badge = 'status-success'; }
                    if ($st === 'terlambat' || $st === 'late') { $badge = 'status-warning'; }
                    if ($st === 'alfa' || $st === 'absent' || $st === 'izin' || $st === 'sakit' || $st === 'cuti') { $badge = 'status-danger'; }
                @endphp
                <tr>
                    <td>
                        <div class="cell-user"><span class="avatar tone-primary">{{ strtoupper(substr(optional($a->user)->name ?: '-', 0, 1)) }}</span>
                        <div><span class="cell-title">{{ optional($a->user)->name ?: '—' }}</span><span class="cell-sub">{{ $a->notes ?: '—' }}</span></div></div>
                    </td>
                    <td><span class="fw-medium text-heading">{{ $a->date ? $a->date->format('d M Y') : '—' }}</span></td>
                    <td>{{ $a->clock_in ?: '—' }}</td>
                    <td>{{ $a->clock_out ?: '—' }}</td>
                    <td>{{ optional($a->outlet)->name ?: '—' }}</td>
                    <td><span class="status-badge {{ $badge }}">{{ ucfirst($a->status ?: '—') }}</span></td>
                </tr>
                @empty
                <tr><td colspan="6" class="text-center text-muted py-4">Belum ada catatan kehadiran.</td></tr>
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
  Chart.defaults.font.family = 'Inter, system-ui, sans-serif';
  new Chart(document.getElementById('attStatus'), {type:'doughnut',
    data:{labels:@json($statusLabels),datasets:[{data:@json($statusCounts),backgroundColor:['#22C55E','#F59E0B','#EF4444','#6366F1','#06B6D4','#94A3B8']}]},
    options:{plugins:{legend:{position:'bottom'}}}});
})();
</script>
@endpush
