@extends('app.layout')
@section('title','New Purchase Order')

@section('content')
<div class="page-header">
    <div>
        <nav aria-label="Breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="/app"><i class="bi bi-house-door"></i> Home</a></li>
                <li class="breadcrumb-item"><a href="/app/purchase-orders">Purchase Orders</a></li>
                <li class="breadcrumb-item active" aria-current="page">New order</li>
            </ol>
        </nav>
        <h1 class="page-title">New Purchase Order</h1>
        <p class="page-subtitle">Create a purchase order with line items.</p>
    </div>
    <div class="page-actions">
        <a class="btn btn-light" href="/app/purchase-orders"><i class="bi bi-arrow-left"></i>Back</a>
    </div>
</div>

<div class="card">
    <div class="card-body">
        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif
        @if($errors->any())
            <div class="alert alert-danger">
                <ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
            </div>
        @endif
        <form method="POST" action="{{ route('tillora.store.po') }}" id="poForm">
            @csrf
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label" for="supplier_id">Supplier</label>
                    <select id="supplier_id" name="supplier_id" class="form-select @error('supplier_id') is-invalid @enderror" required>
                        <option value="">— Select supplier —</option>
                        @foreach($suppliers as $s)
                            <option value="{{ $s->id }}" @selected(old('supplier_id') == $s->id)>{{ $s->name }}</option>
                        @endforeach
                    </select>
                    @error('supplier_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label" for="notes">Notes</label>
                    <input id="notes" name="notes" type="text" class="form-control" value="{{ old('notes') }}" placeholder="Optional note…">
                </div>
            </div>
            <h2 class="h6 mt-2">Items</h2>
            <div class="table-responsive">
                <table class="table align-middle" id="itemsTable">
                    <thead><tr><th>Product</th><th style="width:140px">Quantity</th><th style="width:180px">Unit price</th><th style="width:60px"></th></tr></thead>
                    <tbody>
                        <tr class="item-row">
                            <td>
                                <select name="items[0][product_id]" class="form-select" required>
                                    <option value="">— Select product —</option>
                                    @foreach($products as $p)
                                        <option value="{{ $p->id }}">{{ $p->name }} ({{ $p->sku }})</option>
                                    @endforeach
                                </select>
                            </td>
                            <td><input name="items[0][quantity]" type="number" min="1" value="1" class="form-control" required></td>
                            <td><input name="items[0][unit_price]" type="number" min="0" step="0.01" value="0" class="form-control" required></td>
                            <td><button type="button" class="btn btn-ghost btn-icon btn-sm remove-row" aria-label="Remove row"><i class="bi bi-trash3"></i></button></td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <div class="d-flex gap-2 mt-2">
                <button type="button" class="btn btn-light" id="addRow"><i class="bi bi-plus-lg"></i>Add item</button>
                <button type="submit" class="btn btn-primary"><i class="bi bi-check-lg"></i>Create PO</button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
(function(){
    var tbody = document.querySelector('#itemsTable tbody');
    var idx = 1;
    var productOptions = @json($products->map(fn($p) => ['id' => $p->id, 'label' => $p->name.' ('.$p->sku.')']));
    document.getElementById('addRow').addEventListener('click', function(){
        var tr = document.createElement('tr');
        tr.className = 'item-row';
        var opts = '<option value="">— Select product —</option>' + productOptions.map(function(p){
            return '<option value="'+p.id+'">'+p.label+'</option>';
        }).join('');
        tr.innerHTML = '<td><select name="items['+idx+'][product_id]" class="form-select" required>'+opts+'</select></td>'
            + '<td><input name="items['+idx+'][quantity]" type="number" min="1" value="1" class="form-control" required></td>'
            + '<td><input name="items['+idx+'][unit_price]" type="number" min="0" step="0.01" value="0" class="form-control" required></td>'
            + '<td><button type="button" class="btn btn-ghost btn-icon btn-sm remove-row" aria-label="Remove row"><i class="bi bi-trash3"></i></button></td>';
        tbody.appendChild(tr);
        idx++;
    });
    tbody.addEventListener('click', function(e){
        var btn = e.target.closest('.remove-row');
        if (!btn) return;
        if (tbody.querySelectorAll('.item-row').length > 1) btn.closest('.item-row').remove();
    });
})();
</script>
@endpush
