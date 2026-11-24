@extends('app.layout')
@section('title','Employees')


@section('content')
<div class="page-header">
    <div>
        <nav aria-label="Breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="/app"><i class="bi bi-house-door"></i> Home</a></li>
                <li class="breadcrumb-item"><a href="/app/employees-list">People</a></li>
                <li class="breadcrumb-item active" aria-current="page">Employees</li>
            </ol>
        </nav>
        <h1 class="page-title">Employees</h1>
        <p class="page-subtitle">Staff directory with shift status and today's sales.</p>
    </div>
    <div class="page-actions">
        <a class="btn btn-light" href="/app/users-roles"><i class="bi bi-shield-lock"></i>Roles</a>
    </div>
</div>

<div class="card table-card" data-table data-page-size="10">
    <div class="table-toolbar">
        <div class="input-icon">
            <i class="bi bi-search"></i>
            <input type="search" class="form-control" placeholder="Search name, email…" data-table-search aria-label="Search employees">
        </div>
    </div>
    <div class="table-responsive">
        <table class="table table-hover align-middle">
            <thead><tr><th class="col-main">Employee</th><th>Role</th><th>Outlet</th><th>Shift today</th><th class="table-num">Sales today</th><th class="col-actions"></th></tr></thead>
            <tbody>
                @forelse($users as $u)
                @php $onShift = isset($openShiftUserIds[$u->id]); @endphp
                <tr>
                    <td><div class="cell-user"><span class="avatar">{{ strtoupper(substr($u->name,0,1)) }}</span><div><a class="cell-title" href="/app/employee-details?id={{ $u->id }}">{{ $u->name }}</a><span class="cell-sub">{{ $u->email }}</span></div></div></td>
                    <td>{{ $u->roles->pluck('name')->join(', ') ?: ($u->role ?? '—') }}</td>
                    <td>{{ $u->outlets->pluck('name')->join(', ') ?: '—' }}</td>
                    <td><span class="status-badge {{ $onShift ? 'status-success' : 'status-neutral' }}">{{ $onShift ? 'On shift' : 'Off' }}</span></td>
                    <td class="table-num fw-semibold">{{ tillora_rp($todaySales[$u->id] ?? 0) }}<small class="d-block text-muted">{{ $todayCount[$u->id] ?? 0 }} trx</small></td>
                    <td class="col-actions"><a class="btn btn-ghost btn-icon btn-sm" href="/app/employee-details?id={{ $u->id }}" aria-label="View details"><i class="bi bi-eye"></i></a></td>
                </tr>
                @empty
                <tr><td colspan="6" class="text-center text-muted py-4">Belum ada karyawan aktif.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="table-footer"><span>{{ $users->count() }} karyawan aktif</span></div>
</div>
@endsection
