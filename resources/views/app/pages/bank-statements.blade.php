@extends('app.layout')
@section('title','Mutasi Bank')

@section('content')
<div class="page-header">
    <div>
        <nav aria-label="Breadcrumb"><ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/app"><i class="bi bi-house-door"></i> Home</a></li>
            <li class="breadcrumb-item"><a href="/app/reports-overview">Keuangan</a></li>
            <li class="breadcrumb-item active" aria-current="page">Bank statements</li>
        </ol></nav>
        <h1 class="page-title">Mutasi Bank</h1>
        <p class="page-subtitle">60 mutasi terbaru · arus masuk &amp; keluar · data asli.</p>
    </div>
    <div class="page-actions">
        <a class="btn btn-light" href="/app/company-assets"><i class="bi bi-safe"></i> Aset</a>
    </div>
</div>

<div class="row g-app mb-4">
    <div class="col-6 col-xl-3">
        <div class="card kpi-card grad-indigo">
            <div class="kpi-top"><div class="min-w-0"><div class="kpi-label">Arus masuk</div><div class="kpi-value">{{ tillora_rp($totalIn) }}</div></div><span class="icon-box tone-primary"><i class="bi bi-arrow-down-circle"></i></span></div>
            <div class="kpi-foot"><span>total kredit hasil filter</span></div>
        </div>
    </div>
    <div class="col-6 col-xl-3">
        <div class="card kpi-card grad-cyan">
            <div class="kpi-top"><div class="min-w-0"><div class="kpi-label">Arus keluar</div><div class="kpi-value">{{ tillora_rp($totalOut) }}</div></div><span class="icon-box tone-cyan"><i class="bi bi-arrow-up-circle"></i></span></div>
            <div class="kpi-foot"><span>total debit hasil filter</span></div>
        </div>
    </div>
    <div class="col-6 col-xl-3">
        <div class="card kpi-card grad-mint">
            <div class="kpi-top"><div class="min-w-0"><div class="kpi-label">Arus bersih</div><div class="kpi-value">{{ tillora_rp($netFlow) }}</div></div><span class="icon-box tone-success"><i class="bi bi-activity"></i></span></div>
            <div class="kpi-foot"><span>masuk dikurangi keluar</span></div>
        </div>
    </div>
    <div class="col-6 col-xl-3">
        <div class="card kpi-card grad-amber">
            <div class="kpi-top"><div class="min-w-0"><div class="kpi-label">Sudah rekonsiliasi</div><div class="kpi-value">{{ number_format($matchedCount,0,',','.') }}</div></div><span class="icon-box tone-warning"><i class="bi bi-check2-circle"></i></span></div>
            <div class="kpi-foot"><span>dari {{ number_format($totalCount,0,',','.') }} mutasi</span></div>
        </div>
    </div>
</div>

<div class="card table-card">
    <div class="card-header">
        <div><h2 class="card-title">Daftar mutasi</h2><p class="card-subtitle">Terbaru dulu · maksimal 60 baris</p></div>
        <form method="GET" action="/app/bank-statements" class="d-flex gap-2">
            <input type="search" name="q" value="{{ $q }}" class="form-control form-control-sm" placeholder="Cari bank / no. rekening / keterangan...">
            <button class="btn btn-light btn-sm" type="submit"><i class="bi bi-search"></i></button>
        </form>
    </div>
    <div class="table-responsive" style="max-height:560px;overflow:auto;">
        <table class="table table-hover align-middle">
            <thead><tr><th>Tanggal</th><th>Bank</th><th>Keterangan</th><th>Referensi</th><th class="table-num">Keluar (debit)</th><th class="table-num">Masuk (kredit)</th><th class="table-num">Saldo</th><th>Status</th></tr></thead>
            <tbody>
                @forelse($rows as $s)
                <tr>
                    <td>{{ $s->transaction_date ? $s->transaction_date->format('d M Y') : '-' }}</td>
                    <td><div class="fw-semibold">{{ $s->bank_name }}</div><div class="small text-muted">{{ $s->account_number }}</div></td>
                    <td class="small">{{ $s->description ?: '-' }}</td>
                    <td class="small text-muted">{{ $s->reference ?: '-' }}</td>
                    <td class="table-num">{{ tillora_rp($s->debit) }}</td>
                    <td class="table-num fw-semibold">{{ tillora_rp($s->credit) }}</td>
                    <td class="table-num">{{ tillora_rp($s->balance) }}</td>
                    <td>
                        @if($s->is_matched)
                        <span class="badge bg-success">Matched</span>
                        @else
                        <span class="badge bg-secondary">Belum</span>
                        @endif
                    </td>
                </tr>
                @empty
                <tr><td colspan="8" class="text-center text-muted py-4">Belum ada mutasi bank yang cocok dengan pencarian.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
