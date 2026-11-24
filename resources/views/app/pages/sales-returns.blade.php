@extends('app.layout')
@section('title','Sales Returns')


@section('content')
                <div class="page-header">
                    <div>
                        <nav aria-label="Breadcrumb">
                            <ol class="breadcrumb">
                                <li class="breadcrumb-item"><a href="/app"><i class="bi bi-house-door"></i> Home</a></li>
                                <li class="breadcrumb-item"><a href="/app/sales-list">Sales</a></li>
                                <li class="breadcrumb-item active" aria-current="page">Returns</li>
                            </ol>
                        </nav>
                        <h1 class="page-title">Sales returns</h1>
                        <p class="page-subtitle">Semua retur penjualan dari database.</p>
                    </div>
                    <div class="page-actions">
                        <a class="btn btn-primary" href="/pos"><i class="bi bi-arrow-return-left"></i>Proses di Kasir</a>
                    </div>
                </div>

                <div class="row g-app mb-4">
                    <div class="col-6 col-xl-4">
                        <div class="card kpi-card grad-rose">
                            <div class="kpi-top"><div class="min-w-0"><div class="kpi-label">Total retur completed</div><div class="kpi-value">{{ tillora_rp($totalRetur) }}</div></div><span class="icon-box tone-danger"><i class="bi bi-arrow-return-left"></i></span></div>
                            <div class="kpi-foot"><span>{{ $returns->count() }} data ditampilkan</span></div>
                        </div>
                    </div>
                    <div class="col-6 col-xl-4">
                        <div class="card kpi-card grad-amber">
                            <div class="kpi-top"><div class="min-w-0"><div class="kpi-label">Bulan ini</div><div class="kpi-value">{{ $countMonth }}</div></div><span class="icon-box tone-warning"><i class="bi bi-calendar-month"></i></span></div>
                            <div class="kpi-foot"><span>retur bulan berjalan</span></div>
                        </div>
                    </div>
                    <div class="col-6 col-xl-4">
                        <div class="card kpi-card grad-indigo">
                            <div class="kpi-top"><div class="min-w-0"><div class="kpi-label">Pending</div><div class="kpi-value">{{ $pendingCount }}</div></div><span class="icon-box tone-primary"><i class="bi bi-hourglass-split"></i></span></div>
                            <div class="kpi-foot"><span>menunggu diproses</span></div>
                        </div>
                    </div>
                </div>

                <div class="card mb-4">
                    <div class="card-body">
                        <h6 class="card-title mb-3">Retur per hari · 7 hari terakhir</h6>
                        <div style="height:200px;"><canvas id="retDaily"></canvas></div>
                    </div>
                </div>

                <div class="card table-card" data-table data-page-size="10">
                    <div class="table-toolbar">
                        <form method="GET" action="/app/sales-returns" class="input-icon">
                            <i class="bi bi-search"></i>
                            <input type="search" name="q" value="{{ $q }}" class="form-control" placeholder="Search nomor retur…" aria-label="Search retur">
                        </form>
                        <div class="toolbar-end">@if($q!=='')<a class="btn btn-light" href="/app/sales-returns"><i class="bi bi-x-circle"></i>Reset</a>@endif</div>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead><tr><th>Nomor</th><th>Tanggal</th><th>Order</th><th>Customer</th><th class="table-num">Total</th><th>Alasan</th><th>Status</th></tr></thead>
                            <tbody>
                                @forelse($returns as $rt)
                                @php $badge = match($rt->status){ 'completed'=>'status-success', 'pending'=>'status-warning', 'cancelled'=>'status-danger', default=>'status-info' }; @endphp
                                <tr>
                                    <td><span class="table-id">{{ $rt->return_number }}</span><span class="cell-sub d-block">{{ $rt->type ?? '' }}</span></td>
                                    <td>{{ $rt->created_at->format('d M Y') }}</td>
                                    <td>@if($rt->order)<a href="/app/sale-details?id={{ $rt->order->order_number }}">{{ $rt->order->order_number }}</a>@else — @endif</td>
                                    <td>{{ optional(optional($rt->order)->customer)->name ?? '—' }}</td>
                                    <td class="table-num fw-semibold">{{ tillora_rp($rt->total_amount) }}</td>
                                    <td class="text-muted">{{ \Illuminate\Support\Str::limit($rt->reason ?? '—', 40) }}</td>
                                    <td><span class="status-badge {{ $badge }}">{{ ucfirst($rt->status) }}</span></td>
                                </tr>
                                @empty
                                <tr><td colspan="7" class="text-center text-muted py-4">Belum ada data retur.</td></tr>
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
  new Chart(document.getElementById('retDaily'), {type:'line',
    data:{labels:@json($labels),datasets:[{label:'Retur',data:@json($daily),borderColor:'#F43F5E',backgroundColor:'rgba(244,63,94,.12)',fill:true,tension:.4,cubicInterpolationMode:'monotone'}]},
    options:{plugins:{legend:{display:false}}}});
})();
</script>
@endpush
