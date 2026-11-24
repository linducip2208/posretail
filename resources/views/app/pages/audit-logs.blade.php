@extends('app.layout')
@section('title','Audit Logs')


@section('content')
<div class="page-header">
    <div>
        <nav aria-label="Breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="/app"><i class="bi bi-house-door"></i> Home</a></li>
                <li class="breadcrumb-item"><a href="/app/audit-logs">Tools</a></li>
                <li class="breadcrumb-item active" aria-current="page">Audit Logs</li>
            </ol>
        </nav>
        <h1 class="page-title">Audit logs</h1>
        <p class="page-subtitle">100 aktivitas terakhir — siapa mengubah apa dan kapan.</p>
    </div>
    <div class="page-actions">
        <a class="btn btn-light" href="/app/notifications"><i class="bi bi-bell"></i>Notifikasi</a>
    </div>
</div>

<div class="row g-app mb-4">
    <div class="col-6 col-xl-3">
        <div class="card kpi-card grad-indigo">
            <div class="kpi-top">
                <div class="min-w-0">
                    <div class="kpi-label">Total log</div>
                    <div class="kpi-value">{{ number_format($totalCount) }}</div>
                </div>
                <span class="icon-box tone-primary"><i class="bi bi-clock-history"></i></span>
            </div>
            <div class="kpi-foot"><span>aktivitas terekam</span></div>
        </div>
    </div>
    <div class="col-12 col-xl-9">
        <div class="card mb-0">
            <div class="card-body">
                <h6 class="card-title mb-3">Aktivitas teratas</h6>
                <div style="height:120px;"><canvas id="auditAction"></canvas></div>
            </div>
        </div>
    </div>
</div>

<div class="card table-card" data-table data-page-size="10">
    <div class="table-toolbar">
        <form method="GET" action="/app/audit-logs" class="input-icon">
            <i class="bi bi-search"></i>
            <input type="search" name="q" value="{{ $q }}" class="form-control" placeholder="Cari aksi, model, IP…" aria-label="Cari audit log">
        </form>
        <div class="toolbar-end">
            @if($q !== '')<a class="btn btn-light" href="/app/audit-logs"><i class="bi bi-x-circle"></i>Reset</a>@endif
        </div>
    </div>
    <div class="table-responsive">
        <table class="table table-hover align-middle">
            <thead>
                <tr>
                    <th class="col-main">Pengguna</th>
                    <th>Aksi</th>
                    <th>Model</th>
                    <th>Detail</th>
                    <th>IP</th>
                    <th>Waktu</th>
                </tr>
            </thead>
            <tbody>
                @forelse($logs as $l)
                <tr>
                    <td>
                        <div class="cell-user"><span class="avatar tone-primary">{{ strtoupper(substr($l['user_name'], 0, 1)) }}</span>
                        <div><span class="cell-title">{{ $l['user_name'] }}</span></div></div>
                    </td>
                    <td><span class="status-badge status-info">{{ $l['action'] }}</span></td>
                    <td><span class="fw-medium text-heading">{{ $l['model'] }}</span><small class="d-block text-muted">ID {{ $l['model_id'] ?: '—' }}</small></td>
                    <td><span class="cell-sub">{{ $l['detail'] }}</span></td>
                    <td>{{ $l['ip'] }}</td>
                    <td>{{ $l['time_label'] }}</td>
                </tr>
                @empty
                <tr><td colspan="6" class="text-center text-muted py-4">Belum ada audit log.</td></tr>
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
  new Chart(document.getElementById('auditAction'), {type:'bar',
    data:{labels:@json($actionLabels),datasets:[{label:'Aktivitas',data:@json($actionCounts),backgroundColor:'#6366F1',borderRadius:5}]},
    options:{indexAxis:'y',plugins:{legend:{display:false}},scales:{x:{beginAtZero:true,ticks:{precision:0}}}}});
})();
</script>
@endpush
