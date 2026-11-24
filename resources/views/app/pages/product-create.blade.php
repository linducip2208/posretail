@extends('app.layout')
@section('title','Add Product')

@section('content')

                <div class="page-header">
                    <div>
                        <nav aria-label="Breadcrumb">
                            <ol class="breadcrumb">
                                <li class="breadcrumb-item"><a href="/app"><i class="bi bi-house-door"></i> Home</a></li>
                                <li class="breadcrumb-item"><a href="/app/products-list">Catalog</a></li>
                                <li class="breadcrumb-item"><a href="/app/products-list">Products</a></li>
                                <li class="breadcrumb-item active" aria-current="page">Add product</li>
                            </ol>
                        </nav>
                        <h1 class="page-title">Add product</h1>
                        <p class="page-subtitle">Create a new catalog item. Required fields are marked and validated before saving.</p>
                    </div>
                    <div class="page-actions">
                        <a class="btn btn-light" href="/app/products-list">Cancel</a>
                        <button type="submit" form="productForm" class="btn btn-primary"><i class="bi bi-check2"></i>Save product</button>
                    </div>
                </div>

                @if(isset($errors) && $errors->any())
                <div class="alert-strip danger mb-3"><i class="bi bi-exclamation-triangle"></i><div><strong>Produk gagal disimpan.</strong><ul class="mb-0 mt-1">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div></div>
                @endif
                @if(session('success'))
                <div class="alert-strip success mb-3"><i class="bi bi-check-circle"></i>{{ session('success') }}</div>
                @endif

                <form id="productForm" method="POST" action="{{ route('tillora.store.product') }}">
                    @csrf
                    <div class="row g-app">
                        <div class="col-xl-8">
                            <div class="card">
                                <div class="card-body">
                                    <div class="form-section">
                                        <h2 class="form-section-title h6">Basic information<small>What customers and cashiers will see.</small></h2>
                                        <div class="row g-3">
                                            <div class="col-12">
                                                <label class="form-label" for="pcName">Product name *</label>
                                                <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" id="pcName" placeholder="e.g. Kopi Arabika 1kg" required maxlength="255" value="{{ old('name') }}">
                                                @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                            </div>
                                            <div class="col-md-6">
                                                <label class="form-label" for="pcCategory">Category</label>
                                                <select name="category_id" id="pcCategory" class="form-select @error('category_id') is-invalid @enderror">
                                                    <option value="">— Pilih kategori —</option>
                                                    @foreach($categories as $c)
                                                    <option value="{{ $c->id }}" @selected(old('category_id') == $c->id)>{{ $c->name }}</option>
                                                    @endforeach
                                                </select>
                                                @error('category_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                            </div>
                                            <div class="col-md-6">
                                                <label class="form-label" for="pcBrand">Brand</label>
                                                <select name="brand_id" id="pcBrand" class="form-select @error('brand_id') is-invalid @enderror">
                                                    <option value="">— Pilih merek —</option>
                                                    @foreach($brands as $b)
                                                    <option value="{{ $b->id }}" @selected(old('brand_id') == $b->id)>{{ $b->name }}</option>
                                                    @endforeach
                                                </select>
                                                @error('brand_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                            </div>
                                            <div class="col-md-6">
                                                <label class="form-label" for="pcUnit">Unit</label>
                                                <select name="unit_id" id="pcUnit" class="form-select @error('unit_id') is-invalid @enderror">
                                                    <option value="">— Pilih satuan —</option>
                                                    @foreach($units as $u)
                                                    <option value="{{ $u->id }}" @selected(old('unit_id') == $u->id)>{{ $u->name }}</option>
                                                    @endforeach
                                                </select>
                                                @error('unit_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                            </div>
                                        </div>
                                    </div>

                                    <div class="form-section">
                                        <h2 class="form-section-title h6">Pricing<small>Selling price is required.</small></h2>
                                        <div class="row g-3">
                                            <div class="col-md-6">
                                                <label class="form-label" for="pcSell">Selling price (Rp) *</label>
                                                <input type="number" name="selling_price" id="pcSell" class="form-control @error('selling_price') is-invalid @enderror" min="0" step="any" required value="{{ old('selling_price') }}">
                                                @error('selling_price')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                            </div>
                                            <div class="col-md-6">
                                                <label class="form-label" for="pcCost">Cost price (Rp)</label>
                                                <input type="number" name="cost_price" id="pcCost" class="form-control @error('cost_price') is-invalid @enderror" min="0" step="any" value="{{ old('cost_price') }}">
                                                @error('cost_price')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                            </div>
                                        </div>
                                    </div>

                                    <div class="form-section">
                                        <h2 class="form-section-title h6">Inventory<small>Opening stock and reorder point.</small></h2>
                                        <div class="row g-3">
                                            <div class="col-md-6">
                                                <label class="form-label" for="pcStock">Current stock</label>
                                                <input type="number" name="current_stock" id="pcStock" class="form-control @error('current_stock') is-invalid @enderror" min="0" step="1" value="{{ old('current_stock', 0) }}">
                                                @error('current_stock')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                            </div>
                                            <div class="col-md-6">
                                                <label class="form-label" for="pcMin">Minimum stock</label>
                                                <input type="number" name="min_stock" id="pcMin" class="form-control @error('min_stock') is-invalid @enderror" min="0" step="1" value="{{ old('min_stock', 0) }}">
                                                @error('min_stock')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-xl-4">
                            <div class="card">
                                <div class="card-body">
                                    <h2 class="form-section-title h6">Publish</h2>
                                    <p class="text-muted small">SKU dan barcode dibuat otomatis. Setelah disimpan Anda diarahkan ke halaman detail produk.</p>
                                    <button type="submit" class="btn btn-primary w-100"><i class="bi bi-check2"></i>Save product</button>
                                    <a class="btn btn-light w-100 mt-2" href="/app/products-list">Cancel</a>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
@endsection
