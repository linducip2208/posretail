@extends('app.layout')
@section('title','Add Customer')

@section('content')
<div class="page-header">
    <div>
        <nav aria-label="Breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="/app"><i class="bi bi-house-door"></i> Home</a></li>
                <li class="breadcrumb-item"><a href="/app/customers-list">Customers</a></li>
                <li class="breadcrumb-item active" aria-current="page">Add customer</li>
            </ol>
        </nav>
        <h1 class="page-title">Add Customer</h1>
        <p class="page-subtitle">Register a new customer to the directory.</p>
    </div>
    <div class="page-actions">
        <a class="btn btn-light" href="/app/customers-list"><i class="bi bi-arrow-left"></i>Back</a>
    </div>
</div>

<div class="row g-app">
    <div class="col-12 col-xl-7">
        <div class="card">
            <div class="card-body">
                @if(session('success'))
                    <div class="alert alert-success">{{ session('success') }}</div>
                @endif
                @if($errors->any())
                    <div class="alert alert-danger">
                        <ul class="mb-0">
                            @foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach
                        </ul>
                    </div>
                @endif
                <form method="POST" action="{{ route('tillora.store.customer') }}">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label" for="name">Name</label>
                        <input id="name" name="name" type="text" class="form-control @error('name') is-invalid @enderror" value="{{ old('name') }}" required>
                        @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label" for="phone">Phone</label>
                            <input id="phone" name="phone" type="text" class="form-control @error('phone') is-invalid @enderror" value="{{ old('phone') }}">
                            @error('phone')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label" for="email">Email</label>
                            <input id="email" name="email" type="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email') }}">
                            @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label" for="address">Address</label>
                        <textarea id="address" name="address" rows="3" class="form-control @error('address') is-invalid @enderror">{{ old('address') }}</textarea>
                        @error('address')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="mb-4">
                        <label class="form-label" for="customer_group_id">Customer group</label>
                        <select id="customer_group_id" name="customer_group_id" class="form-select @error('customer_group_id') is-invalid @enderror">
                            <option value="">— No group —</option>
                            @foreach($groups as $g)
                                <option value="{{ $g->id }}" @selected(old('customer_group_id') == $g->id)>{{ $g->name }}</option>
                            @endforeach
                        </select>
                        @error('customer_group_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary"><i class="bi bi-person-plus"></i>Save customer</button>
                        <a href="/app/customers-list" class="btn btn-light">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <div class="col-12 col-xl-5">
        <div class="card">
            <div class="card-body">
                <h2 class="h6">Available groups</h2>
                <ul class="legend-list">
                    @forelse($groups as $g)
                        <li><span>{{ $g->name }}</span><span class="text-muted">{{ $g->discount_percent }}% off</span></li>
                    @empty
                        <li><span class="text-muted">Belum ada grup pelanggan.</span></li>
                    @endforelse
                </ul>
            </div>
        </div>
    </div>
</div>
@endsection
