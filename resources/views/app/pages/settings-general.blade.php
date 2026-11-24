@extends('app.layout')
@section('title','General Settings')

@section('content')
<div class="page-header">
    <div>
        <nav aria-label="Breadcrumb"><ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/app"><i class="bi bi-house-door"></i> Home</a></li>
            <li class="breadcrumb-item"><a href="/app/stores-list">Admin</a></li>
            <li class="breadcrumb-item active" aria-current="page">General settings</li>
        </ol></nav>
        <h1 class="page-title">General settings</h1>
        <p class="page-subtitle">Nilai aktual dari system_settings · perubahan langsung tersimpan ke database.</p>
    </div>
</div>

@if(session('success'))
<div class="alert-strip is-success mb-4"><i class="bi bi-check-circle"></i><span class="small">{{ session('success') }}</span></div>
@endif
@if($errors->any())
<div class="alert-strip is-danger mb-4"><i class="bi bi-exclamation-triangle"></i><span class="small">{{ $errors->first() }}</span></div>
@endif

<div class="row g-app">
    <div class="col-xl-5">
        <div class="card h-100">
            <div class="card-header"><div><h2 class="card-title">Nilai saat ini</h2><p class="card-subtitle">Terbaca dari database</p></div></div>
            <div class="card-body">
                @if($logoUrl)
                <div class="mb-3"><img src="{{ $logoUrl }}" alt="Logo" style="max-height:56px;"></div>
                @endif
                <ul class="legend-list">
                    <li><span><i class="legend-dot" style="background: var(--app-primary);"></i>Nama aplikasi</span><strong>{{ $appName }}</strong></li>
                    <li><span><i class="legend-dot" style="background: #06B6D4;"></i>Tipe order</span><strong>{{ collect($orderTypes)->pluck('label')->join(', ') }}</strong></li>
                    <li><span><i class="legend-dot" style="background: #10B981;"></i>Serial tracking default</span><strong>{{ $serialDefault }}</strong></li>
                </ul>
            </div>
        </div>
    </div>
    <div class="col-xl-7">
        <div class="card">
            <div class="card-header"><div><h2 class="card-title">Ubah pengaturan</h2><p class="card-subtitle">Disimpan ke system_settings</p></div></div>
            <div class="card-body">
                <form method="POST" action="{{ route('tillora.settings.update') }}">
                    @csrf @method('PUT')
                    <div class="mb-3">
                        <label class="form-label" for="sgName">Nama aplikasi</label>
                        <input id="sgName" name="app_name" class="form-control @error('app_name') is-invalid @enderror" value="{{ old('app_name', $appName) }}" maxlength="100" required>
                        @error('app_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="mb-3">
                        <label class="form-label" for="sgSerial">Serial tracking default</label>
                        <select id="sgSerial" name="serial_tracking_default" class="form-select @error('serial_tracking_default') is-invalid @enderror">
                            @foreach(['none' => 'None — tanpa pelacakan serial', 'optional' => 'Optional — serial opsional', 'required' => 'Required — serial wajib'] as $val => $lbl)
                            <option value="{{ $val }}" {{ old('serial_tracking_default', $serialDefault) === $val ? 'selected' : '' }}>{{ $lbl }}</option>
                            @endforeach
                        </select>
                        @error('serial_tracking_default')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <button type="submit" class="btn btn-primary"><i class="bi bi-check-lg"></i>Simpan pengaturan</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
