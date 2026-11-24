@extends('app.layout')
@section('title','Notifications')


@section('content')
<div class="page-header">
    <div>
        <nav aria-label="Breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="/app"><i class="bi bi-house-door"></i> Home</a></li>
                <li class="breadcrumb-item"><a href="/app/notifications">Tools</a></li>
                <li class="breadcrumb-item active" aria-current="page">Notifications</li>
            </ol>
        </nav>
        <h1 class="page-title">Notifikasi</h1>
        <p class="page-subtitle">50 notifikasi terbaru dari database sistem.</p>
    </div>
    <div class="page-actions">
        <a class="btn btn-light" href="/app/audit-logs"><i class="bi bi-clock-history"></i>Audit logs</a>
    </div>
</div>

<div class="row g-app mb-4">
    <div class="col-6 col-xl-3">
        <div class="card kpi-card grad-indigo">
            <div class="kpi-top">
                <div class="min-w-0">
                    <div class="kpi-label">Total notifikasi</div>
                    <div class="kpi-value">{{ number_format($totalCount) }}</div>
                </div>
                <span class="icon-box tone-primary"><i class="bi bi-bell"></i></span>
            </div>
            <div class="kpi-foot"><span>tersimpan di database</span></div>
        </div>
    </div>
    <div class="col-6 col-xl-3">
        <div class="card kpi-card grad-amber">
            <div class="kpi-top">
                <div class="min-w-0">
                    <div class="kpi-label">Belum dibaca</div>
                    <div class="kpi-value">{{ number_format($unreadCount) }}</div>
                </div>
                <span class="icon-box tone-warning"><i class="bi bi-bell-fill"></i></span>
            </div>
            <div class="kpi-foot"><span>perlu perhatian</span></div>
        </div>
    </div>
    <div class="col-12 col-xl-6">
        <div class="card mb-0">
            <div class="card-body">
                <h6 class="card-title mb-3">Notifikasi per tipe</h6>
                <div style="height:120px;"><canvas id="notifType"></canvas></div>
            </div>
        </div>
    </div>
</div>

<div class="card table-card" data-table data-page-size="10">
    <div class="table-toolbar">
        <div class="input-icon">
            <i class="bi bi-search"></i>
            <input type="search" class="form-control" placeholder="Cari judul, tipe notifikasi…" data-table-search aria-label="Cari notifikasi">
        </div>
    </div>
    <div class="table-responsive">
        <table class="table table-hover align-middle">
            <thead>
                <tr>
                    <th class="col-main">Notifikasi</th>
                    <th>Tipe</th>
                    <th>Waktu</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                @forelse($notes as $n)
                <tr>
                    <td>
                        <div class="cell-receipt">
                            @if($n['is_read'])
                            <span class="receipt-icon"><i class="bi bi-envelope-open"></i></span>
                            @else
                            <span class="receipt-icon"><i class="bi bi-envelope-fill"></i></span>
                            @endif
                            <div><span class="cell-title">{{ $n['title'] }}</span><span class="cell-sub">{{ $n['message'] }}</span></div>
                        </div>
                    </td>
                    <td><span class="pay-chip"><i class="bi bi-tag"></i>{{ $n['type'] }}</span></td>
                    <td><span class="fw-medium text-heading">{{ $n['created_label'] }}</span><small class="d-block text-muted">{{ $n['created_ago'] }}</small></td>
                    <td>
                        @if($n['is_read'])
                        <span class="status-badge status-success">Dibaca</span>
                        @else
                        <span class="status-badge status-warning">Belum dibaca</span>
                        @endif
                    </td>
                </tr>
                @empty
                <tr><td colspan="4" class="text-center text-muted py-4">Belum ada notifikasi.</td></tr>
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
  new Chart(document.getElementById('notifType'), {type:'bar',
    data:{labels:@json($typeLabels),datasets:[{label:'Notifikasi',data:@json($typeCounts),backgroundColor:'#4F46E5',borderRadius:5}]},
    options:{indexAxis:'y',plugins:{legend:{display:false}},scales:{x:{beginAtZero:true,ticks:{precision:0}}}}});
})();
</script>
@endpush
