@extends('app.layout')
@section('title','Stock Take Mobile')

@section('content')
                <div class="page-header">
                    <div>
                        <nav aria-label="Breadcrumb">
                            <ol class="breadcrumb">
                                <li class="breadcrumb-item"><a href="/app"><i class="bi bi-house-door"></i> Home</a></li>
                                <li class="breadcrumb-item"><a href="/app/inventory-history">Inventory</a></li>
                                <li class="breadcrumb-item active" aria-current="page">Stock take</li>
                            </ol>
                        </nav>
                        <h1 class="page-title">Stock Take Mobile</h1>
                        <p class="page-subtitle">Hitung stok fisik langsung dari HP. Hasil tersimpan sebagai stock opname.</p>
                    </div>
                    <div class="page-actions">
                        <a class="btn btn-light" href="/app/inventory-history"><i class="bi bi-clock-history"></i>Riwayat opname</a>
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
                        <form method="POST" action="{{ route('tillora.stocktake.store') }}" id="stockTakeForm">
                            @csrf
                            <div class="mb-3">
                                <label class="form-label" for="stOutlet">Outlet *</label>
                                <select id="stOutlet" name="outlet_id" class="form-select form-select-lg @error('outlet_id') is-invalid @enderror" required>
                                    <option value="">— Pilih outlet —</option>
                                    @foreach($outlets as $o)
                                        <option value="{{ $o->id }}" @selected((string)old('outlet_id', $selectedOutletId) === (string)$o->id)>{{ $o->name }}</option>
                                    @endforeach
                                </select>
                                @error('outlet_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <h2 class="h6 mt-2">Item yang dihitung</h2>
                            <div id="stRows" class="d-grid gap-3">
                                <div class="card st-row border">
                                    <div class="card-body">
                                        <div class="mb-2">
                                            <label class="form-label">Produk *</label>
                                            <select name="items[0][product_id]" class="form-select form-select-lg st-product" required>
                                                <option value="">— Pilih produk —</option>
                                                @foreach($products as $p)
                                                    <option value="{{ $p->id }}">{{ $p->name }} ({{ $p->sku }})</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="row g-2">
                                            <div class="col-6">
                                                <label class="form-label">Stok sistem</label>
                                                <input name="items[0][system_qty]" type="number" min="0" value="0" class="form-control form-control-lg st-system" readonly>
                                                <div class="form-text st-info">Pilih produk dahulu.</div>
                                            </div>
                                            <div class="col-6">
                                                <label class="form-label">Stok fisik *</label>
                                                <input name="items[0][actual_qty]" type="number" min="0" value="0" class="form-control form-control-lg" required inputmode="numeric">
                                            </div>
                                        </div>
                                        <button type="button" class="btn btn-ghost btn-sm mt-2 st-remove"><i class="bi bi-trash3"></i>Hapus baris</button>
                                    </div>
                                </div>
                            </div>
                            <div class="d-grid gap-2 mt-3">
                                <button type="button" class="btn btn-light btn-lg" id="stAdd"><i class="bi bi-plus-lg"></i>Tambah produk</button>
                                <button type="submit" class="btn btn-primary btn-lg"><i class="bi bi-check-lg"></i>Simpan hasil opname</button>
                            </div>
                        </form>
                    </div>
                </div>

                <script>
                (function(){
                    var productOptions = @json($productOptions);
                    var stockMap = @json($stockMap);
                    var rowsWrap = document.getElementById('stRows');
                    var idx = 1;

                    function stockOf(id) {
                        for (var i = 0; i < stockMap.length; i++) {
                            if (String(stockMap[i].id) === String(id)) return stockMap[i].stock;
                        }
                        return 0;
                    }

                    function productOpts() {
                        var html = '<option value="">— Pilih produk —</option>';
                        for (var i = 0; i < productOptions.length; i++) {
                            html += '<option value="' + productOptions[i].id + '">' + productOptions[i].label + '</option>';
                        }
                        return html;
                    }

                    function refreshRow(card) {
                        var sel = card.querySelector('.st-product');
                        var sys = card.querySelector('.st-system');
                        var info = card.querySelector('.st-info');
                        var st = stockOf(sel.value);
                        sys.value = st;
                        info.textContent = sel.value ? 'Stok saat ini: ' + st + '.' : 'Pilih produk dahulu.';
                    }

                    document.getElementById('stAdd').addEventListener('click', function(){
                        var div = document.createElement('div');
                        div.className = 'card st-row border';
                        div.innerHTML = '<div class="card-body">'
                            + '<div class="mb-2"><label class="form-label">Produk *</label>'
                            + '<select name="items[' + idx + '][product_id]" class="form-select form-select-lg st-product" required>' + productOpts() + '</select></div>'
                            + '<div class="row g-2"><div class="col-6"><label class="form-label">Stok sistem</label>'
                            + '<input name="items[' + idx + '][system_qty]" type="number" min="0" value="0" class="form-control form-control-lg st-system" readonly>'
                            + '<div class="form-text st-info">Pilih produk dahulu.</div></div>'
                            + '<div class="col-6"><label class="form-label">Stok fisik *</label>'
                            + '<input name="items[' + idx + '][actual_qty]" type="number" min="0" value="0" class="form-control form-control-lg" required inputmode="numeric"></div></div>'
                            + '<button type="button" class="btn btn-ghost btn-sm mt-2 st-remove"><i class="bi bi-trash3"></i>Hapus baris</button>'
                            + '</div>';
                        rowsWrap.appendChild(div);
                        idx++;
                    });

                    rowsWrap.addEventListener('change', function(e){
                        var sel = e.target.closest('.st-product');
                        if (!sel) return;
                        refreshRow(sel.closest('.st-row'));
                    });

                    rowsWrap.addEventListener('click', function(e){
                        var btn = e.target.closest('.st-remove');
                        if (!btn) return;
                        if (rowsWrap.querySelectorAll('.st-row').length > 1) btn.closest('.st-row').remove();
                    });
                })();
                </script>
@endsection
