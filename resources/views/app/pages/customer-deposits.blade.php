@extends('app.layout')
@section('title','Customer Deposits')


@section('content')
<div class="page-header">
    <div>
        <nav aria-label="Breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="/app"><i class="bi bi-house-door"></i> Home</a></li>
                <li class="breadcrumb-item"><a href="/app/customer-deposits">People</a></li>
                <li class="breadcrumb-item active" aria-current="page">Customer Deposits</li>
            </ol>
        </nav>
        <h1 class="page-title">Deposit pelanggan</h1>
        <p class="page-subtitle">Mutasi dan saldo deposit pelanggan dari database.</p>
    </div>
    <div class="page-actions">
        <a class="btn btn-primary" href="/admin/customer-deposits/create"><i class="bi bi-plus-lg"></i>Tambah</a>
    </div>
</div>

<div class="row g-app mb-4">
    <div class="col-6 col-xl-3">
        <div class="card kpi-card grad-indigo">
            <div class="kpi-top">
                <div class="min-w-0">
                    <div class="kpi-label">Total saldo deposit</div>
                    <div class="kpi-value">{{ tillora_rp($totalDeposit) }}</div>
                </div>
                <span class="icon-box tone-primary"><i class="bi bi-wallet2"></i></span>
            </div>
            <div class="kpi-foot"><span>akumulasi semua pelanggan</span></div>
        </div>
    </div>
    <div class="col-6 col-xl-3">
        <div class="card kpi-card grad-cyan">
            <div class="kpi-top">
                <div class="min-w-0">
                    <div class="kpi-label">Pemegang deposit</div>
                    <div class="kpi-value">{{ number_format($holderCount) }}</div>
                </div>
                <span class="icon-box tone-cyan"><i class="bi bi-people"></i></span>
            </div>
            <div class="kpi-foot"><span>pelanggan bersaldo aktif</span></div>
        </div>
    </div>
    <div class="col-6 col-xl-3">
        <div class="card kpi-card grad-amber">
            <div class="kpi-top">
                <div class="min-w-0">
                    <div class="kpi-label">Transaksi deposit</div>
                    <div class="kpi-value">{{ number_format($txCount) }}</div>
                </div>
                <span class="icon-box tone-warning"><i class="bi bi-arrow-left-right"></i></span>
            </div>
            <div class="kpi-foot"><span>mutasi tercatat</span></div>
        </div>
    </div>
    <div class="col-6 col-xl-3">
        <div class="card kpi-card grad-rose">
            <div class="kpi-top">
                <div class="min-w-0">
                    <div class="kpi-label">Jenis mutasi</div>
                    <div class="kpi-value">{{ count($typeLabels) }}</div>
                </div>
                <span class="icon-box tone-danger"><i class="bi bi-tags"></i></span>
            </div>
            <div class="kpi-foot"><span>tipe transaksi deposit</span></div>
        </div>
    </div>
</div>

<div class="row g-app mb-4">
    <div class="col-12 col-xl-5">
        <div class="card mb-0">
            <div class="card-body">
                <h6 class="card-title mb-3">Saldo terbesar</h6>
                <div class="legend-list">
                    @forelse($topCustomers as $c)
                    <div class="legend-row">
                        <span class="avatar tone-primary">{{ strtoupper(substr($c->name ?: '-', 0, 1)) }}</span>
                        <span class="legend-label">{{ $c->name }}</span>
                        <span class="legend-value">{{ tillora_rp($c->deposit_balance) }}</span>
                    </div>
                    @empty
                    <p class="text-muted mb-0">Belum ada pelanggan berdeposit.</p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
    <div class="col-12 col-xl-7">
        <div class="card mb-0">
            <div class="card-body">
                <h6 class="card-title mb-3">Mutasi per jenis</h6>
                <div style="height:220px;"><canvas id="depType"></canvas></div>
            </div>
        </div>
    </div>
</div>

<div class="card table-card" data-table data-page-size="10">
    <div class="table-toolbar">
        <div class="input-icon">
            <i class="bi bi-search"></i>
            <input type="search" class="form-control" placeholder="Cari pelanggan, referensi…" data-table-search aria-label="Cari deposit">
        </div>
    </div>
    <div class="table-responsive">
        <table class="table table-hover align-middle">
            <thead>
                <tr>
                    <th class="col-main">Pelanggan</th>
                    <th>Jenis</th>
                    <th class="table-num">Nominal</th>
                    <th class="table-num">Saldo setelah</th>
                    <th>Outlet</th>
                    <th>Tanggal</th>
                </tr>
            </thead>
            <tbody>
                @forelse($deposits as $d)
                @php
                    $badge = 'status-info';
                    if ($d->type === 'topup' || $d->type === 'deposit') { $badge = 'status-success'; }
                    if ($d->type === 'withdraw' || $d->type === 'refund' || $d->type === 'use') { $badge = 'status-danger'; }
                @endphp
                <tr>
                    <td>
                        <div class="cell-user"><span class="avatar tone-primary">{{ strtoupper(substr(optional($d->customer)->name ?: '-', 0, 1)) }}</span>
                        <div><span class="cell-title">{{ optional($d->customer)->name ?: '—' }}</span><span class="cell-sub">{{ $d->reference ?: '—' }}</span></div></div>
                    </td>
                    <td><span class="status-badge {{ $badge }}">{{ ucfirst($d->type ?: 'mutasi') }}</span></td>
                    <td class="table-num fw-semibold">{{ tillora_rp($d->amount) }}</td>
                    <td class="table-num">{{ tillora_rp($d->balance_after) }}</td>
                    <td>{{ optional($d->outlet)->name ?: '—' }}</td>
                    <td>{{ $d->created_at ? $d->created_at->format('d M Y H:i') : '—' }}</td>
                </tr>
                @empty
                <tr><td colspan="6" class="text-center text-muted py-4">Belum ada mutasi deposit.</td></tr>
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
  const fmtRp = v => 'Rp ' + Number(v||0).toLocaleString('id-ID');
  new Chart(document.getElementById('depType'), {type:'bar',
    data:{labels:@json($typeLabels),datasets:[{label:'Transaksi',data:@json($typeCounts),backgroundColor:'#06B6D4',borderRadius:5}]},
    options:{plugins:{legend:{display:false},tooltip:{callbacks:{label:c=>' '+c.parsed.y+' transaksi'}}},scales:{y:{beginAtZero:true,ticks:{precision:0}}}}});
})();
</script>
@endpush
