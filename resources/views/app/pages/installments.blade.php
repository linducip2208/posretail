@extends('app.layout')
@section('title','Installments')


@section('content')
<div class="page-header">
    <div>
        <nav aria-label="Breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="/app"><i class="bi bi-house-door"></i> Home</a></li>
                <li class="breadcrumb-item"><a href="/app/installments">Sales</a></li>
                <li class="breadcrumb-item active" aria-current="page">Installments</li>
            </ol>
        </nav>
        <h1 class="page-title">Cicilan pelanggan</h1>
        <p class="page-subtitle">Piutang cicilan dan jadwal pembayaran dari database.</p>
    </div>
    <div class="page-actions">
        <a class="btn btn-primary" href="/admin/installments/create"><i class="bi bi-plus-lg"></i>Tambah</a>
    </div>
</div>

<div class="row g-app mb-4">
    <div class="col-6 col-xl-3">
        <div class="card kpi-card grad-indigo">
            <div class="kpi-top">
                <div class="min-w-0">
                    <div class="kpi-label">Piutang cicilan</div>
                    <div class="kpi-value">{{ tillora_rp($totalReceivable) }}</div>
                </div>
                <span class="icon-box tone-primary"><i class="bi bi-credit-card"></i></span>
            </div>
            <div class="kpi-foot"><span>belum dibayar (induk)</span></div>
        </div>
    </div>
    <div class="col-6 col-xl-3">
        <div class="card kpi-card grad-cyan">
            <div class="kpi-top">
                <div class="min-w-0">
                    <div class="kpi-label">Sisa jadwal</div>
                    <div class="kpi-value">{{ tillora_rp($schedReceivable) }}</div>
                </div>
                <span class="icon-box tone-cyan"><i class="bi bi-calendar-check"></i></span>
            </div>
            <div class="kpi-foot"><span>jadwal belum lunas</span></div>
        </div>
    </div>
    <div class="col-6 col-xl-3">
        <div class="card kpi-card grad-amber">
            <div class="kpi-top">
                <div class="min-w-0">
                    <div class="kpi-label">Jatuh tempo ≤ 7 hari</div>
                    <div class="kpi-value">{{ number_format($due7) }}</div>
                </div>
                <span class="icon-box tone-warning"><i class="bi bi-alarm"></i></span>
            </div>
            <div class="kpi-foot"><span>cicilan perlu ditagih</span></div>
        </div>
    </div>
    <div class="col-6 col-xl-3">
        <div class="card kpi-card grad-rose">
            <div class="kpi-top">
                <div class="min-w-0">
                    <div class="kpi-label">Sudah lunas</div>
                    <div class="kpi-value">{{ tillora_rp($paidTotal) }}</div>
                </div>
                <span class="icon-box tone-danger"><i class="bi bi-check-circle"></i></span>
            </div>
            <div class="kpi-foot"><span>{{ number_format($overdueCount) }} cicilan menunggak</span></div>
        </div>
    </div>
</div>

<div class="card mb-4">
    <div class="card-body">
        <h6 class="card-title mb-3">Komposisi status cicilan</h6>
        <div style="height:220px;"><canvas id="instStatus"></canvas></div>
    </div>
</div>

<div class="card table-card" data-table data-page-size="10">
    <div class="table-toolbar">
        <div class="input-icon">
            <i class="bi bi-search"></i>
            <input type="search" class="form-control" placeholder="Cari order, pelanggan…" data-table-search aria-label="Cari cicilan">
        </div>
    </div>
    <div class="table-responsive">
        <table class="table table-hover align-middle">
            <thead>
                <tr>
                    <th class="col-main">Order</th>
                    <th>Pelanggan</th>
                    <th class="table-num">Tenor</th>
                    <th class="table-num">Terbayar</th>
                    <th>Jatuh tempo berikut</th>
                    <th class="table-num">Sisa</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                @forelse($installments as $i)
                @php
                    $badge = 'status-warning';
                    if ($i->status === 'paid') { $badge = 'status-success'; }
                    if ($i->status === 'overdue') { $badge = 'status-danger'; }
                    if ($i->status === 'pending') { $badge = 'status-info'; }
                @endphp
                <tr>
                    <td><span class="table-id">{{ $i->order_number }}</span><span class="cell-sub d-block text-muted">{{ $i->due_date ? $i->due_date->format('d M Y') : '—' }}</span></td>
                    <td>{{ $i->customer_name }}</td>
                    <td class="table-num">{{ $i->tenor }}x</td>
                    <td class="table-num">{{ $i->paid_count }}/{{ $i->tenor }}</td>
                    <td>{{ $i->next_due_label }}</td>
                    <td class="table-num fw-semibold">{{ tillora_rp($i->sisa) }}</td>
                    <td><span class="status-badge {{ $badge }}">{{ ucfirst($i->status) }}</span></td>
                </tr>
                @empty
                <tr><td colspan="7" class="text-center text-muted py-4">Belum ada data cicilan.</td></tr>
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
  new Chart(document.getElementById('instStatus'), {type:'doughnut',
    data:{labels:@json($statusLabels),datasets:[{data:@json($statusCounts),backgroundColor:['#22C55E','#F59E0B','#EF4444','#6366F1','#06B6D4']}]},
    options:{plugins:{legend:{position:'bottom'}}}});
})();
</script>
@endpush
