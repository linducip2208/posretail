@extends('app.layout')
@section('title','Stock Adjustments')

@section('content')


                <div class="page-header">
                    <div>
                        <nav aria-label="Breadcrumb">
                            <ol class="breadcrumb">
                                <li class="breadcrumb-item"><a href="/app"><i class="bi bi-house-door"></i> Home</a></li>
                                <li class="breadcrumb-item"><a href="/app/inventory-overview">Inventory</a></li>
                                <li class="breadcrumb-item active" aria-current="page">Adjustments</li>
                            </ol>
                        </nav>
                        <h1 class="page-title">Stock Adjustments</h1>
                        <p class="page-subtitle">Opnames and write-offs in one activity timeline.</p>
                    </div>
                    <div class="page-actions">
                        <a class="btn btn-light" href="/app/inventory-history"><i class="bi bi-clock-history"></i>History</a>
                    </div>
                </div>

                <div class="row g-app mb-4">
                    <div class="col-6 col-xl-6">
                        <div class="card kpi-card grad-indigo">
                            <div class="kpi-top"><div class="min-w-0"><div class="kpi-label">Stock opnames</div><div class="kpi-value">{{ number_format($totalOpname) }}</div></div><span class="icon-box tone-primary"><i class="bi bi-clipboard-check"></i></span></div>
                            <div class="kpi-foot"><span>latest 30 shown</span></div>
                        </div>
                    </div>
                    <div class="col-6 col-xl-6">
                        <div class="card kpi-card grad-rose">
                            <div class="kpi-top"><div class="min-w-0"><div class="kpi-label">Write-off loss</div><div class="kpi-value">{{ tillora_rp($totalLoss) }}</div></div><span class="icon-box tone-danger"><i class="bi bi-trash3"></i></span></div>
                            <div class="kpi-foot"><span>{{ number_format($writeoffs->count()) }} latest records</span></div>
                        </div>
                    </div>
                </div>

                <div class="card table-card">
                    <div class="card-header"><h2 class="card-title h6 mb-0">Adjustment activity</h2></div>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead><tr><th>Date</th><th>Type</th><th>Reference</th><th>Outlet</th><th>By</th><th>Status</th><th>Detail</th></tr></thead>
                            <tbody>
                                @forelse($activity as $a)
                                <tr>
                                    <td class="text-muted small">{{ $a['date']?->format('d M Y H:i') }}</td>
                                    <td><span class="status-badge {{ $a['type'] === 'Write-Off' ? 'status-danger' : 'status-info' }}">{{ $a['type'] }}</span></td>
                                    <td><span class="font-mono text-xs">{{ $a['ref'] }}</span></td>
                                    <td>{{ $a['outlet'] }}</td>
                                    <td>{{ $a['user'] }}</td>
                                    <td><span class="status-badge {{ in_array($a['status'],['completed','approved']) ? 'status-success' : 'status-warning' }}">{{ ucfirst($a['status'] ?? '-') }}</span></td>
                                    <td class="text-muted small">{{ $a['detail'] ?? '-' }}</td>
                                </tr>
                                @empty
                                <tr><td colspan="7" class="text-center text-muted py-4">Belum ada aktivitas penyesuaian stok.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
@endsection
