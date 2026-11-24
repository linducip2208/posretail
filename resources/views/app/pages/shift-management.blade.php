@extends('app.layout')
@section('title','Shift Management')


@section('content')
                <div class="page-header">
                    <div>
                        <nav aria-label="Breadcrumb">
                            <ol class="breadcrumb">
                                <li class="breadcrumb-item"><a href="/app"><i class="bi bi-house-door"></i> Home</a></li>
                                <li class="breadcrumb-item"><a href="/app/sales-list">Sales</a></li>
                                <li class="breadcrumb-item active" aria-current="page">Shifts</li>
                            </ol>
                        </nav>
                        <h1 class="page-title">Shift management</h1>
                        <p class="page-subtitle">Shift kasir dari database — yang sedang open dan 20 shift terakhir.</p>
                    </div>
                    <div class="page-actions">
                        <a class="btn btn-primary" href="/pos"><i class="bi bi-upc-scan"></i>Buka Kasir</a>
                    </div>
                </div>

                <div class="row g-app mb-4">
                    <div class="col-6 col-xl-4">
                        <div class="card kpi-card grad-mint">
                            <div class="kpi-top"><div class="min-w-0"><div class="kpi-label">Shift open</div><div class="kpi-value">{{ $openShifts->count() }}</div></div><span class="icon-box tone-success"><i class="bi bi-clock"></i></span></div>
                            <div class="kpi-foot"><span>shift sedang berjalan</span></div>
                        </div>
                    </div>
                    <div class="col-6 col-xl-4">
                        <div class="card kpi-card grad-indigo">
                            <div class="kpi-top"><div class="min-w-0"><div class="kpi-label">Total shift</div><div class="kpi-value">{{ $shifts->count() }}</div></div><span class="icon-box tone-primary"><i class="bi bi-clock-history"></i></span></div>
                            <div class="kpi-foot"><span>20 shift terakhir ditampilkan</span></div>
                        </div>
                    </div>
                    <div class="col-6 col-xl-4">
                        <div class="card kpi-card grad-amber">
                            <div class="kpi-top"><div class="min-w-0"><div class="kpi-label">Kas awal open</div><div class="kpi-value">{{ tillora_rp($openShifts->sum('starting_cash')) }}</div></div><span class="icon-box tone-warning"><i class="bi bi-safe"></i></span></div>
                            <div class="kpi-foot"><span>modal awal shift berjalan</span></div>
                        </div>
                    </div>
                </div>

                @if($openShifts->isNotEmpty())
                <div class="card mb-4">
                    <div class="card-body">
                        <h6 class="card-title mb-3">Shift sedang open</h6>
                        <div class="alert-strip">
                            @foreach($openShifts as $s)
                            <div class="alert-strip-item"><i class="bi bi-clock"></i><div><strong>Shift #{{ $s->id }} · {{ optional($s->outlet)->name ?? '—' }}</strong><small class="d-block text-muted">{{ optional($s->user)->name ?? '—' }} · mulai {{ optional($s->started_at)->format('d M Y H:i') }} · kas awal {{ tillora_rp($s->starting_cash) }}</small></div></div>
                            @endforeach
                        </div>
                    </div>
                </div>
                @endif

                <div class="card table-card" data-table data-page-size="10">
                    <div class="table-toolbar"><h6 class="card-title mb-0">Riwayat shift</h6></div>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead><tr><th>Shift</th><th>Outlet</th><th>Kasir</th><th>Mulai</th><th>Selesai</th><th class="table-num">Kas awal</th><th class="table-num">Kas akhir</th><th class="table-num">Selisih</th><th>Status</th></tr></thead>
                            <tbody>
                                @forelse($shifts as $s)
                                <tr>
                                    <td><span class="table-id">#{{ $s->id }}</span></td>
                                    <td>{{ optional($s->outlet)->name ?? '—' }}</td>
                                    <td>{{ optional($s->user)->name ?? '—' }}</td>
                                    <td>{{ optional($s->started_at)->format('d M Y H:i') ?? '—' }}</td>
                                    <td>{{ optional($s->ended_at)->format('d M Y H:i') ?? '—' }}</td>
                                    <td class="table-num">{{ tillora_rp($s->starting_cash) }}</td>
                                    <td class="table-num">{{ tillora_rp($s->ending_cash) }}</td>
                                    <td class="table-num fw-semibold">{{ tillora_rp($s->difference) }}</td>
                                    <td><span class="status-badge {{ $s->status==='open' ? 'status-success' : 'status-info' }}">{{ ucfirst($s->status) }}</span></td>
                                </tr>
                                @empty
                                <tr><td colspan="9" class="text-center text-muted py-4">Belum ada data shift.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
@endsection
