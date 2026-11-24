@extends('app.layout')
@section('title','Sales Transactions')


@section('content')
                <div class="page-header">
                    <div>
                        <nav aria-label="Breadcrumb">
                            <ol class="breadcrumb">
                                <li class="breadcrumb-item"><a href="/app"><i class="bi bi-house-door"></i> Home</a></li>
                                <li class="breadcrumb-item"><a href="/app/sales-list">Sales</a></li>
                                <li class="breadcrumb-item active" aria-current="page">Transactions</li>
                            </ol>
                        </nav>
                        <h1 class="page-title">Sales transactions</h1>
                        <p class="page-subtitle">Setiap penjualan dari database — cari, filter, dan telusuri detailnya.</p>
                    </div>
                    <div class="page-actions">
                        <a class="btn btn-primary" href="/pos"><i class="bi bi-upc-scan"></i>New sale</a>
                    </div>
                </div>

                <div class="row g-app mb-4">
                    <div class="col-6 col-xl-3">
                        <div class="card kpi-card grad-indigo">
                            <div class="kpi-top">
                                <div class="min-w-0">
                                    <div class="kpi-label">Sales today</div>
                                    <div class="kpi-value">{{ tillora_rp($salesToday) }}</div>
                                </div>
                                <span class="icon-box tone-primary"><i class="bi bi-currency-dollar"></i></span>
                            </div>
                            <div class="kpi-foot"><span>{{ $countToday }} transaksi hari ini</span></div>
                        </div>
                    </div>
                    <div class="col-6 col-xl-3">
                        <div class="card kpi-card grad-cyan">
                            <div class="kpi-top">
                                <div class="min-w-0">
                                    <div class="kpi-label">Last 7 days</div>
                                    <div class="kpi-value">{{ tillora_rp($salesWeek) }}</div>
                                </div>
                                <span class="icon-box tone-cyan"><i class="bi bi-calendar-week"></i></span>
                            </div>
                            <div class="kpi-foot"><span>{{ $countWeek }} transaksi 7 hari terakhir</span></div>
                        </div>
                    </div>
                    <div class="col-6 col-xl-3">
                        <div class="card kpi-card grad-rose">
                            <div class="kpi-top">
                                <div class="min-w-0">
                                    <div class="kpi-label">Refunded</div>
                                    <div class="kpi-value">{{ tillora_rp($refundedTotal) }}</div>
                                </div>
                                <span class="icon-box tone-danger"><i class="bi bi-arrow-return-left"></i></span>
                            </div>
                            <div class="kpi-foot"><span>total retur completed</span></div>
                        </div>
                    </div>
                    <div class="col-6 col-xl-3">
                        <div class="card kpi-card grad-amber">
                            <div class="kpi-top">
                                <div class="min-w-0">
                                    <div class="kpi-label">Pending</div>
                                    <div class="kpi-value">{{ $pendingCount }}</div>
                                </div>
                                <span class="icon-box tone-warning"><i class="bi bi-hourglass-split"></i></span>
                            </div>
                            <div class="kpi-foot"><span>order menunggu penyelesaian</span></div>
                        </div>
                    </div>
                </div>

                <div class="card mb-4">
                    <div class="card-body">
                        <h6 class="card-title mb-3">Daily revenue · 7 hari terakhir</h6>
                        <div style="height:220px;"><canvas id="salesDaily"></canvas></div>
                    </div>
                </div>

                <div class="card table-card" data-table data-page-size="10">
                    <div class="table-toolbar">
                        <form method="GET" action="/app/sales-list" class="input-icon">
                            <i class="bi bi-search"></i>
                            <input type="search" name="q" value="{{ $q }}" class="form-control" placeholder="Search receipt, customer…" aria-label="Search receipt, customer…">
                        </form>
                        <div class="toolbar-end">
                            @if($q !== '')<a class="btn btn-light" href="/app/sales-list"><i class="bi bi-x-circle"></i>Reset</a>@endif
                        </div>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle" id="salesTable">
                            <thead>
                                <tr>
                                    <th class="col-main">Receipt</th>
                                    <th>Tanggal</th>
                                    <th>Customer</th>
                                    <th>Outlet</th>
                                    <th>Payment</th>
                                    <th class="table-num">Items</th>
                                    <th class="table-num">Total</th>
                                    <th>Status</th>
                                    <th class="col-actions"></th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($orders as $o)
                                @php
                                    $payName = $o->payments->count() > 1 ? 'Split' : (optional(optional($o->payments->first())->paymentMethod)->name ?? '—');
                                    $badge = match($o->order_status){ 'completed'=>'status-success', 'cancelled'=>'status-danger', 'pending'=>'status-info', default=>'status-warning' };
                                @endphp
                                <tr>
                                    <td><div class="cell-receipt"><span class="receipt-icon"><i class="bi bi-receipt"></i></span><div><a class="table-id" href="/app/sale-details?id={{ $o->order_number }}">{{ $o->order_number }}</a><span class="cell-sub">{{ $o->order_type ?? 'In-store' }}</span></div></div></td>
                                    <td><span class="fw-medium text-heading">{{ $o->created_at->format('d M Y') }}</span><small class="d-block text-muted">{{ $o->created_at->format('H:i') }}</small></td>
                                    <td><div class="cell-user"><span class="avatar tone-primary">{{ strtoupper(substr(optional($o->customer)->name ?? 'W',0,1)) }}</span><div><span class="cell-title">{{ optional($o->customer)->name ?? 'Walk-in customer' }}</span><span class="cell-sub">{{ optional($o->user)->name ?? '—' }}</span></div></div></td>
                                    <td>{{ optional($o->outlet)->name ?? '—' }}</td>
                                    <td><span class="pay-chip"><i class="bi bi-credit-card-2-front"></i>{{ $payName }}</span></td>
                                    <td class="table-num">{{ $o->orderItems->sum('quantity') }}</td>
                                    <td class="table-num fw-semibold">{{ tillora_rp($o->total_amount) }}</td>
                                    <td><span class="status-badge {{ $badge }}">{{ ucfirst($o->order_status) }}</span></td>
                                    <td class="col-actions"><div class="dropdown">
                                        <button class="btn btn-ghost btn-icon btn-sm" type="button" data-bs-toggle="dropdown" aria-expanded="false" aria-label="Row actions"><i class="bi bi-three-dots"></i></button>
                                        <ul class="dropdown-menu dropdown-menu-end">
                                            <li><a class="dropdown-item" href="/app/sale-details?id={{ $o->order_number }}"><i class="bi bi-eye"></i>View details</a></li>
                                            <li><a class="dropdown-item" href="/app/pos-receipt?id={{ $o->order_number }}"><i class="bi bi-receipt"></i>View receipt</a></li>
                                        </ul>
                                    </div></td>
                                </tr>
                                @empty
                                <tr><td colspan="9" class="text-center text-muted py-4">Belum ada transaksi penjualan.</td></tr>
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
  new Chart(document.getElementById('salesDaily'), {type:'bar',
    data:{labels:@json($labels),datasets:[{label:'Revenue',data:@json($daily),backgroundColor:'#4F46E5',borderRadius:5}]},
    options:{plugins:{legend:{display:false},tooltip:{callbacks:{label:c=>' '+fmtRp(c.parsed.y)}}},scales:{y:{ticks:{callback:v=>'Rp '+(v>=1e6?(v/1e6)+'jt':v>=1e3?(v/1e3)+'rb':v)}}}}});
})();
</script>
@endpush
