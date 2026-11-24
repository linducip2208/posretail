@extends('app.layout')
@section('title','Sales Payments')


@section('content')
                <div class="page-header">
                    <div>
                        <nav aria-label="Breadcrumb">
                            <ol class="breadcrumb">
                                <li class="breadcrumb-item"><a href="/app"><i class="bi bi-house-door"></i> Home</a></li>
                                <li class="breadcrumb-item"><a href="/app/sales-list">Sales</a></li>
                                <li class="breadcrumb-item active" aria-current="page">Payments</li>
                            </ol>
                        </nav>
                        <h1 class="page-title">Sales payments</h1>
                        <p class="page-subtitle">Semua pembayaran penjualan dari database, lengkap dengan ringkasan per metode.</p>
                    </div>
                </div>

                <div class="row g-app mb-4">
                    <div class="col-6 col-xl-3">
                        <div class="card kpi-card grad-indigo">
                            <div class="kpi-top"><div class="min-w-0"><div class="kpi-label">Diterima hari ini</div><div class="kpi-value">{{ tillora_rp($totalToday) }}</div></div><span class="icon-box tone-primary"><i class="bi bi-cash-stack"></i></span></div>
                            <div class="kpi-foot"><span>pembayaran hari ini</span></div>
                        </div>
                    </div>
                    @foreach(array_slice($totalsPerMethod, 0, 3) as $i => $tm)
                    <div class="col-6 col-xl-3">
                        <div class="card kpi-card {{ ['grad-cyan','grad-amber','grad-rose'][$i % 3] }}">
                            <div class="kpi-top"><div class="min-w-0"><div class="kpi-label">{{ $tm['name'] }}</div><div class="kpi-value">{{ tillora_rp($tm['total']) }}</div></div><span class="icon-box tone-cyan"><i class="bi bi-credit-card"></i></span></div>
                            <div class="kpi-foot"><span>akumulasi semua waktu</span></div>
                        </div>
                    </div>
                    @endforeach
                </div>

                <div class="row g-app">
                    <div class="col-lg-8">
                        <div class="card table-card" data-table data-page-size="10">
                            <div class="table-toolbar">
                                <form method="GET" action="/app/sales-payments" class="input-icon">
                                    <i class="bi bi-search"></i>
                                    <input type="search" name="q" value="{{ $q }}" class="form-control" placeholder="Search referensi / order…" aria-label="Search pembayaran">
                                </form>
                                <div class="toolbar-end">@if($q!=='')<a class="btn btn-light" href="/app/sales-payments"><i class="bi bi-x-circle"></i>Reset</a>@endif</div>
                            </div>
                            <div class="table-responsive">
                                <table class="table table-hover align-middle">
                                    <thead><tr><th>Referensi</th><th>Tanggal</th><th>Order</th><th>Metode</th><th class="table-num">Jumlah</th><th>Status</th></tr></thead>
                                    <tbody>
                                        @forelse($payments as $p)
                                        <tr>
                                            <td><span class="table-id">{{ $p->reference_number ?? 'PAY-'.$p->id }}</span></td>
                                            <td>{{ optional($p->paid_at)->format('d M Y H:i') ?? $p->created_at->format('d M Y H:i') }}</td>
                                            <td>@if($p->order)<a href="/app/sale-details?id={{ $p->order->order_number }}">{{ $p->order->order_number }}</a>@else — @endif</td>
                                            <td><span class="pay-chip"><i class="bi bi-credit-card-2-front"></i>{{ optional($p->paymentMethod)->name ?? '—' }}</span></td>
                                            <td class="table-num fw-semibold">{{ tillora_rp($p->amount) }}</td>
                                            <td><span class="status-badge {{ in_array($p->status,['success','confirmed','completed']) ? 'status-success' : ($p->status==='refunded' ? 'status-danger' : 'status-warning') }}">{{ ucfirst($p->status) }}</span></td>
                                        </tr>
                                        @empty
                                        <tr><td colspan="6" class="text-center text-muted py-4">Belum ada data pembayaran.</td></tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-4">
                        <div class="card mb-4">
                            <div class="card-body">
                                <h6 class="card-title mb-3">Ringkasan per metode</h6>
                                <ul class="legend-list">
                                    @forelse($totalsPerMethod as $tm)
                                    <li><span>{{ $tm['name'] }}</span><strong>{{ tillora_rp($tm['total']) }}</strong></li>
                                    @empty
                                    <li><span>Belum ada data</span><strong>—</strong></li>
                                    @endforelse
                                </ul>
                            </div>
                        </div>
                        <div class="card">
                            <div class="card-body">
                                <h6 class="card-title mb-3">Distribusi pembayaran</h6>
                                <div style="height:220px;"><canvas id="payDist"></canvas></div>
                            </div>
                        </div>
                    </div>
                </div>
@endsection

@push('scripts')
<script>
(function(){
  if (!window.Chart) return;
  const fmtRp = v => 'Rp ' + Number(v||0).toLocaleString('id-ID');
  new Chart(document.getElementById('payDist'), {type:'doughnut',
    data:{labels:@json($methodLabels),datasets:[{data:@json($methodTotals),backgroundColor:['#4F46E5','#06B6D4','#F59E0B','#F43F5E','#10B981','#8B5CF6']}]},
    options:{plugins:{legend:{position:'bottom'},tooltip:{callbacks:{label:c=>' '+c.label+': '+fmtRp(c.parsed)}}}}});
})();
</script>
@endpush
