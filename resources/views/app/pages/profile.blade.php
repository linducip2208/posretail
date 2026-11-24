@extends('app.layout')
@section('title','Profile')



@section('content')
<div class="page-header">
    <div>
        <nav aria-label="Breadcrumb"><ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/app"><i class="bi bi-house-door"></i> Home</a></li>
            <li class="breadcrumb-item active" aria-current="page">Profile</li>
        </ol></nav>
        <h1 class="page-title">Profile</h1>
        <p class="page-subtitle">Akun yang sedang masuk &amp; aktivitas bulan berjalan.</p>
    </div>
</div>

@if(session('success'))
<div class="alert-strip is-success mb-4"><i class="bi bi-check-circle"></i><span class="small">{{ session('success') }}</span></div>
@endif
@if($errors->any())
<div class="alert-strip is-danger mb-4"><i class="bi bi-exclamation-triangle"></i><span class="small">{{ $errors->first() }}</span></div>
@endif

<div class="row g-app mb-4">
    <div class="col-6 col-xl-4">
        <div class="card kpi-card grad-indigo">
            <div class="kpi-top"><div class="min-w-0"><div class="kpi-label">Penjualan bulan ini</div><div class="kpi-value">{{ tillora_rp($mtdSales) }}</div></div><span class="icon-box tone-primary"><i class="bi bi-graph-up-arrow"></i></span></div>
            <div class="kpi-foot"><span>{{ number_format($mtdCount,0,',','.') }} transaksi MTD</span></div>
        </div>
    </div>
    <div class="col-6 col-xl-4">
        <div class="card kpi-card grad-cyan">
            <div class="kpi-top"><div class="min-w-0"><div class="kpi-label">Outlet</div><div class="kpi-value" style="font-size:1.1rem;">{{ $outletNames ? implode(', ', array_slice($outletNames, 0, 2)).(count($outletNames) > 2 ? ' +'.(count($outletNames)-2) : '') : '—' }}</div></div><span class="icon-box tone-cyan"><i class="bi bi-shop"></i></span></div>
            <div class="kpi-foot"><span>{{ number_format(count($outletNames),0,',','.') }} outlet terhubung</span></div>
        </div>
    </div>
    <div class="col-6 col-xl-4">
        <div class="card kpi-card grad-mint">
            <div class="kpi-top"><div class="min-w-0"><div class="kpi-label">Peran</div><div class="kpi-value" style="font-size:1.25rem;">{{ $roleLabel }}</div></div><span class="icon-box tone-success"><i class="bi bi-person-badge"></i></span></div>
            <div class="kpi-foot"><span>{{ $user->active ? 'Akun aktif' : 'Akun nonaktif' }}</span></div>
        </div>
    </div>
</div>

<div class="row g-app">
    <div class="col-xl-5">
        <div class="card h-100">
            <div class="card-header"><div><h2 class="card-title">Akun saya</h2><p class="card-subtitle">Terakhir masuk · {{ $user->updated_at?->format('d M Y H:i') ?? '—' }}</p></div></div>
            <div class="card-body">
                <div class="cell-user mb-4"><span class="avatar tone-primary" style="width:52px;height:52px;font-size:1.1rem;">{{ strtoupper(substr($user->name ?? '?',0,2)) }}</span><div><span class="cell-title" style="font-size:1.05rem;">{{ $user->name }}</span><span class="cell-sub">{{ $user->email }}</span></div></div>
                <ul class="legend-list">
                    <li><span><i class="legend-dot" style="background: var(--app-primary);"></i>Peran</span><strong>{{ $roleLabel }}</strong></li>
                    <li><span><i class="legend-dot" style="background: #06B6D4;"></i>Status</span><strong>{{ $user->active ? 'Aktif' : 'Nonaktif' }}</strong></li>
                    <li><span><i class="legend-dot" style="background: #10B981;"></i>Outlet</span><strong>{{ $outletNames ? implode(', ', $outletNames) : '—' }}</strong></li>
                    <li><span><i class="legend-dot" style="background: #F59E0B;"></i>Penjualan MTD</span><strong>{{ tillora_rp($mtdSales) }} ({{ number_format($mtdCount,0,',','.') }})</strong></li>
                </ul>
            </div>
        </div>
    </div>
    <div class="col-xl-7">
        <div class="card mb-4">
            <div class="card-header"><div><h2 class="card-title">Ubah profil</h2><p class="card-subtitle">Kosongkan kata sandi bila tidak ingin mengubah</p></div></div>
            <div class="card-body">
                <form method="POST" action="{{ route('tillora.profile.update') }}">
                    @csrf @method('PUT')
                    <div class="row g-3">
                        <div class="col-md-6"><label class="form-label" for="pfName">Nama</label><input id="pfName" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $user->name) }}" required>@error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
                        <div class="col-md-6"><label class="form-label" for="pfEmail">Email</label><input id="pfEmail" type="email" name="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email', $user->email) }}" required>@error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
                        <div class="col-md-6"><label class="form-label" for="pfPass">Kata sandi baru</label><input id="pfPass" type="password" name="password" class="form-control @error('password') is-invalid @enderror" placeholder="Min. 8 karakter" autocomplete="new-password">@error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
                    </div>
                    <div class="mt-3"><button type="submit" class="btn btn-primary"><i class="bi bi-check-lg"></i>Simpan perubahan</button></div>
                </form>
            </div>
        </div>
        <div class="card table-card">
            <div class="card-header"><div><h2 class="card-title">5 shift terakhir saya</h2><p class="card-subtitle">Riwayat jaga kasir · data asli</p></div><a class="btn btn-light btn-sm" href="/app/shift-management">Semua shift</a></div>
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead><tr><th>Mulai</th><th>Selesai</th><th class="table-num">Kas awal</th><th class="table-num">Kas akhir</th><th>Status</th></tr></thead>
                    <tbody>
                        @forelse($shifts as $s)
                        <tr>
                            <td>{{ $s->started_at?->format('d M Y H:i') ?? '—' }}</td>
                            <td>{{ $s->ended_at?->format('d M Y H:i') ?? '—' }}</td>
                            <td class="table-num">{{ tillora_rp($s->starting_cash) }}</td>
                            <td class="table-num">{{ $s->ending_cash !== null ? tillora_rp($s->ending_cash) : '—' }}</td>
                            <td><span class="status-badge {{ $s->status === 'open' ? 'status-success' : 'status-neutral' }}">{{ ucfirst($s->status) }}</span></td>
                        </tr>
                        @empty
                        <tr><td colspan="5" class="text-center text-muted py-4">Belum ada riwayat shift.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
