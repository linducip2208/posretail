@extends('app.layout')
@section('title','Sales Invoices')


@section('content')
                <div class="page-header">
                    <div>
                        <nav aria-label="Breadcrumb">
                            <ol class="breadcrumb">
                                <li class="breadcrumb-item"><a href="/app"><i class="bi bi-house-door"></i> Home</a></li>
                                <li class="breadcrumb-item"><a href="/app/sales-list">Sales</a></li>
                                <li class="breadcrumb-item active" aria-current="page">Invoices</li>
                            </ol>
                        </nav>
                        <h1 class="page-title">Sales invoices</h1>
                        <p class="page-subtitle">Faktur pajak penjualan dari database.</p>
                    </div>
                </div>

                <div class="row g-app mb-4">
                    <div class="col-6 col-xl-4">
                        <div class="card kpi-card grad-indigo">
                            <div class="kpi-top"><div class="min-w-0"><div class="kpi-label">Jumlah faktur</div><div class="kpi-value">{{ $count }}</div></div><span class="icon-box tone-primary"><i class="bi bi-file-earmark-text"></i></span></div>
                            <div class="kpi-foot"><span>faktur pajak tercatat</span></div>
                        </div>
                    </div>
                    <div class="col-6 col-xl-4">
                        <div class="card kpi-card grad-cyan">
                            <div class="kpi-top"><div class="min-w-0"><div class="kpi-label">Total DPP</div><div class="kpi-value">{{ tillora_rp($totalDpp) }}</div></div><span class="icon-box tone-cyan"><i class="bi bi-calculator"></i></span></div>
                            <div class="kpi-foot"><span>dasar pengenaan pajak</span></div>
                        </div>
                    </div>
                    <div class="col-6 col-xl-4">
                        <div class="card kpi-card grad-amber">
                            <div class="kpi-top"><div class="min-w-0"><div class="kpi-label">Total PPN</div><div class="kpi-value">{{ tillora_rp($totalPpn) }}</div></div><span class="icon-box tone-warning"><i class="bi bi-percent"></i></span></div>
                            <div class="kpi-foot"><span>pajak pertambahan nilai</span></div>
                        </div>
                    </div>
                </div>

                <div class="card table-card" data-table data-page-size="10">
                    <div class="table-toolbar">
                        <form method="GET" action="/app/sales-invoices" class="input-icon">
                            <i class="bi bi-search"></i>
                            <input type="search" name="q" value="{{ $q }}" class="form-control" placeholder="Search nomor faktur…" aria-label="Search faktur">
                        </form>
                        <div class="toolbar-end">@if($q!=='')<a class="btn btn-light" href="/app/sales-invoices"><i class="bi bi-x-circle"></i>Reset</a>@endif</div>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead><tr><th>Nomor faktur</th><th>Tanggal</th><th>Order</th><th>Customer</th><th>Outlet</th><th class="table-num">DPP</th><th class="table-num">PPN</th><th class="table-num">Total</th><th>Status</th></tr></thead>
                            <tbody>
                                @forelse($invoices as $inv)
                                <tr>
                                    <td><span class="table-id">{{ $inv->invoice_number }}</span><span class="cell-sub d-block">{{ $inv->reference_number ?? '' }}</span></td>
                                    <td>{{ optional($inv->invoice_date)->format('d M Y') ?? $inv->created_at->format('d M Y') }}</td>
                                    <td>@if($inv->order)<a href="/app/sale-details?id={{ $inv->order->order_number }}">{{ $inv->order->order_number }}</a>@else — @endif</td>
                                    <td>{{ $inv->customer_name ?? optional(optional($inv->order)->customer)->name ?? '—' }}</td>
                                    <td>{{ optional($inv->outlet)->name ?? '—' }}</td>
                                    <td class="table-num">{{ tillora_rp($inv->dpp) }}</td>
                                    <td class="table-num">{{ tillora_rp($inv->ppn_amount) }}</td>
                                    <td class="table-num fw-semibold">{{ tillora_rp($inv->total_amount) }}</td>
                                    <td><span class="status-badge {{ $inv->status==='voided' ? 'status-danger' : 'status-success' }}">{{ ucfirst($inv->status ?? '—') }}</span></td>
                                </tr>
                                @empty
                                <tr><td colspan="9" class="text-center text-muted py-4">Belum ada faktur pajak. Faktur dibuat otomatis/dari kasir saat penjualan.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
@endsection
