@extends('app.layout')
@section('title','POS Terminal')

@section('content')
                <div class="page-header">
                    <div>
                        <nav aria-label="Breadcrumb">
                            <ol class="breadcrumb">
                                <li class="breadcrumb-item"><a href="/app"><i class="bi bi-house-door"></i> Home</a></li>
                                <li class="breadcrumb-item"><a href="/app/sales-list">Sales</a></li>
                                <li class="breadcrumb-item active" aria-current="page">POS</li>
                            </ol>
                        </nav>
                        <h1 class="page-title">POS terminal</h1>
                        <p class="page-subtitle">Katalog asli dari database — susun keranjang di sini, bayar &amp; cetak di kasir. <span class="text-muted">Shortcut: F2 cari &middot; F4 scan &middot; F9 bayar</span></p>
                    </div>
                    <div class="page-actions">
                        <a class="btn btn-primary" href="/pos"><i class="bi bi-upc-scan"></i>Buka Kasir</a>
                    </div>
                </div>

                <div class="row g-app mb-4">
                    <div class="col-6 col-xl-3">
                        <div class="card kpi-card grad-indigo">
                            <div class="kpi-top"><div class="min-w-0"><div class="kpi-label">Produk tersedia</div><div class="kpi-value" id="kpiProducts">{{ $products->count() }}</div></div><span class="icon-box tone-primary"><i class="bi bi-box-seam"></i></span></div>
                            <div class="kpi-foot"><span>stok &gt; 0 ditampilkan</span></div>
                        </div>
                    </div>
                    <div class="col-6 col-xl-3">
                        <div class="card kpi-card grad-cyan">
                            <div class="kpi-top"><div class="min-w-0"><div class="kpi-label">Kategori</div><div class="kpi-value">{{ $categories->count() }}</div></div><span class="icon-box tone-cyan"><i class="bi bi-tags"></i></span></div>
                            <div class="kpi-foot"><span>filter client-side di bawah</span></div>
                        </div>
                    </div>
                    <div class="col-6 col-xl-3">
                        <div class="card kpi-card grad-amber">
                            <div class="kpi-top"><div class="min-w-0"><div class="kpi-label">Held orders</div><div class="kpi-value" id="kpiHeld">{{ $heldCount }}</div></div><span class="icon-box tone-warning"><i class="bi bi-pause-circle"></i></span></div>
                            <div class="kpi-foot"><span><a href="/app/pos-hold-orders">lihat daftar</a></span></div>
                        </div>
                    </div>
                    <div class="col-6 col-xl-3">
                        <div class="card kpi-card grad-mint">
                            <div class="kpi-top"><div class="min-w-0"><div class="kpi-label">Keranjang aktif</div><div class="kpi-value" id="kpiCart">0</div></div><span class="icon-box tone-success"><i class="bi bi-cart3"></i></span></div>
                            <div class="kpi-foot"><span id="kpiCartTotal">Rp 0</span></div>
                        </div>
                    </div>
                </div>

                <div class="pos-workspace">
                    <section class="pos-catalog" aria-label="Product catalog">
                        <div class="pos-catalog-head">
                            <div class="pos-search-row">
                                <div class="input-icon">
                                    <i class="bi bi-search"></i>
                                    <input type="search" class="form-control" id="posSearch" placeholder="Filter 60 produk ini&hellip; (Enter = cari semua via API)" autocomplete="off" aria-label="Filter produk">
                                </div>
                                <div class="input-icon">
                                    <i class="bi bi-upc-scan"></i>
                                    <input type="search" class="form-control" id="posBarcode" placeholder="Scan barcode / SKU (F4)" autocomplete="off" aria-label="Scan barcode">
                                </div>
                            </div>
                            <div class="d-flex flex-wrap gap-2 align-items-center mt-2">
                                <div class="category-chips" id="posCategoryChips" role="toolbar" aria-label="Filter by category">
                                    <button type="button" class="btn btn-light btn-sm" data-cat="">Semua</button>
                                    @foreach($categories as $c)
                                    <button type="button" class="btn btn-light btn-sm" data-cat="{{ $c->id }}">{{ $c->name }}</button>
                                    @endforeach
                                </div>
                                <span class="text-muted small ms-auto" id="posResultInfo"></span>
                                <button type="button" class="btn btn-light btn-sm d-none" id="posReset"><i class="bi bi-arrow-counterclockwise"></i>Reset</button>
                            </div>
                        </div>
                        <div class="pos-catalog-body">
                            <div class="pos-grid" id="posGrid">
                                @forelse($products as $p)
                                <div class="card pos-item" data-id="{{ $p->id }}" data-cat="{{ $p->category_id }}" data-name="{{ strtolower($p->name.' '.$p->sku) }}">
                                    <div class="pos-item-media">
                                        <img src="{{ tillora_pimg($p->image) }}" alt="{{ $p->name }}" loading="lazy" onerror="this.onerror=null;this.src='/app-assets/images/products/mug.svg'">
                                    </div>
                                    <div class="pos-item-body">
                                        <span class="pos-item-name">{{ $p->name }}</span>
                                        <span class="pos-item-meta">{{ $p->sku }}{{ $p->category ? ' · '.$p->category->name : '' }}</span>
                                        <span class="pos-item-meta">Stok: {{ $p->current_stock }}</span>
                                    </div>
                                    <div class="pos-item-foot">
                                        <strong class="pos-item-price">{{ tillora_rp($p->selling_price) }}</strong>
                                        <button type="button" class="btn btn-primary btn-sm pos-add-btn" data-add="{{ $p->id }}"><i class="bi bi-cart-plus"></i>Tambah</button>
                                    </div>
                                </div>
                                @empty
                                <div class="card"><div class="card-body text-center text-muted py-4">Belum ada produk tersedia.</div></div>
                                @endforelse
                            </div>
                        </div>
                    </section>

                    <aside class="pos-cart" aria-label="Order panel">
                        <div class="pos-cart-panel card">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <h6 class="card-title mb-0">Current order</h6>
                                    <span class="status-badge status-info" id="cartCount">0 item</span>
                                </div>
                                <div class="mt-3">
                                    <label class="form-label small text-muted" for="cartCustomer">Customer (opsional)</label>
                                    <select id="cartCustomer" class="form-select">
                                        <option value="">Walk-in</option>
                                        @foreach($customers as $c)
                                        <option value="{{ $c->id }}">{{ $c->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <ul class="list-app mt-3" id="cartItems">
                                    <li class="text-muted" id="cartEmpty">Keranjang kosong — tambah dari katalog.</li>
                                </ul>
                                <div class="mt-2">
                                    <span class="small text-muted d-block mb-1">Diskon</span>
                                    <div class="d-flex gap-1 flex-wrap" id="discountBtns">
                                        <button type="button" class="btn btn-light btn-sm" data-disc="0">0%</button>
                                        <button type="button" class="btn btn-light btn-sm" data-disc="5">5%</button>
                                        <button type="button" class="btn btn-light btn-sm" data-disc="10">10%</button>
                                        <button type="button" class="btn btn-light btn-sm" data-disc="15">15%</button>
                                        <button type="button" class="btn btn-light btn-sm" data-disc="20">20%</button>
                                    </div>
                                </div>
                                <ul class="legend-list mt-3" id="cartTotals">
                                    <li><span>Subtotal</span><strong id="ctSub">Rp 0</strong></li>
                                    <li><span>Diskon (<span id="ctDiscPct">0</span>%)</span><strong id="ctDisc">Rp 0</strong></li>
                                    <li><span>Pajak ({{ rtrim(rtrim(number_format((float)$taxPercent, 2), '0'), '.') }}%)</span><strong id="ctTax">Rp 0</strong></li>
                                    <li><span>Total</span><strong id="ctTotal">Rp 0</strong></li>
                                </ul>
                                <div class="alert-strip d-none mt-2 py-2" id="cartMsg"><i class="bi bi-check-circle"></i><span class="small flex-grow-1"></span></div>
                                <div class="d-grid gap-2 mt-2">
                                    <button type="button" class="btn btn-light" id="btnHold"><i class="bi bi-pause-circle"></i>Hold (simpan)</button>
                                    <button type="button" class="btn btn-primary" id="btnPay"><i class="bi bi-credit-card"></i>Bayar di Kasir (F9)</button>
                                    <a class="btn btn-light btn-sm" href="/app/pos-hold-orders"><i class="bi bi-cart3"></i>Lihat held carts</a>
                                </div>
                                <h6 class="card-title mt-4 mb-2">Metode aktif</h6>
                                <ul class="legend-list">
                                    @forelse($methods as $m)
                                    <li><span>{{ $m->name }}</span><strong>{{ $m->code ?? '' }}</strong></li>
                                    @empty
                                    <li><span>Belum ada metode</span><strong>—</strong></li>
                                    @endforelse
                                </ul>
                            </div>
                        </div>
                    </aside>
                </div>
@endsection

@push('scripts')
<script>
(function(){
  const TAX = parseFloat(@json($taxPercent)) || 0;
  const KEY = 'posretail_app_cart';
  const CATALOG = @json($catalogJson);
  const byId = {};
  CATALOG.forEach(p => { byId[p.id] = p; });
  let cart = { items: [], discountPct: 0, customerId: '' };
  try { const raw = localStorage.getItem(KEY); if (raw) { const d = JSON.parse(raw); if (d && Array.isArray(d.items)) cart = d; } } catch(e){}
  const $ = id => document.getElementById(id);
  const fmt = v => 'Rp ' + Number(v||0).toLocaleString('id-ID');
  const esc = s => String(s ?? '').replace(/[&<>"']/g, c => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c]));
  function save(){ try { localStorage.setItem(KEY, JSON.stringify(cart)); } catch(e){} }
  function totals(){
    const sub = cart.items.reduce((s,i) => s + i.price*i.qty, 0);
    const disc = Math.round(sub * (cart.discountPct||0) / 100);
    const tax = Math.round((sub - disc) * TAX / 100);
    return { sub, disc, tax, total: sub - disc + tax, qty: cart.items.reduce((s,i)=>s+i.qty,0) };
  }
  function render(){
    const box = $('cartItems');
    if (!cart.items.length) {
      box.innerHTML = '<li class="text-muted" id="cartEmpty">Keranjang kosong — tambah dari katalog.</li>';
    } else {
      box.innerHTML = cart.items.map((it, idx) =>
        '<li><span class="avatar tone-neutral"><i class="bi bi-box-seam"></i></span>' +
        '<div class="list-main"><span class="cell-title">' + esc(it.name) + '</span>' +
        '<span class="list-sub">' + fmt(it.price) + ' &times; ' +
        '<button type="button" class="btn btn-ghost btn-sm" data-dec="'+idx+'">&minus;</button> ' + it.qty +
        ' <button type="button" class="btn btn-ghost btn-sm" data-inc="'+idx+'">+</button></span></div>' +
        '<div class="list-end"><strong>' + fmt(it.price*it.qty) + '</strong><br>' +
        '<button type="button" class="btn btn-ghost btn-sm text-danger" data-del="'+idx+'"><i class="bi bi-trash"></i></button></div></li>'
      ).join('');
    }
    const t = totals();
    $('ctSub').textContent = fmt(t.sub);
    $('ctDiscPct').textContent = cart.discountPct || 0;
    $('ctDisc').textContent = fmt(t.disc);
    $('ctTax').textContent = fmt(t.tax);
    $('ctTotal').textContent = fmt(t.total);
    $('cartCount').textContent = t.qty + ' item';
    $('kpiCart').textContent = t.qty;
    $('kpiCartTotal').textContent = fmt(t.total);
    document.querySelectorAll('#discountBtns [data-disc]').forEach(b => b.classList.toggle('btn-primary', Number(b.dataset.disc) === Number(cart.discountPct||0)));
    save();
  }
  function flash(msg, ok){
    const box = $('cartMsg');
    box.querySelector('span').textContent = msg;
    box.classList.remove('d-none', 'is-success', 'is-danger');
    box.classList.add(ok === false ? 'is-danger' : 'is-success');
    setTimeout(() => box.classList.add('d-none'), 4000);
  }
  function addItem(p, qty){
    qty = Math.max(1, qty|0);
    const ex = cart.items.find(i => i.id === p.id);
    const cur = ex ? ex.qty : 0;
    if (cur + qty > p.stock) { flash('Stok ' + p.name + ' tersisa ' + p.stock + '.', false); return; }
    if (ex) ex.qty += qty; else cart.items.push({ id:p.id, name:p.name, price:p.price, qty:qty, tracking:p.tracking||'none' });
    render();
  }
  document.getElementById('posGrid').addEventListener('click', e => {
    const b = e.target.closest('[data-add]');
    if (!b) return;
    const p = byId[b.dataset.add];
    if (p) { addItem(p, 1); flash(p.name + ' masuk keranjang.'); }
  });
  document.getElementById('cartItems').addEventListener('click', e => {
    const inc = e.target.closest('[data-inc]'), dec = e.target.closest('[data-dec]'), del = e.target.closest('[data-del]');
    if (inc) { const it = cart.items[+inc.dataset.inc]; const p = byId[it.id]; if (p && it.qty + 1 > p.stock) { flash('Stok ' + p.name + ' tersisa ' + p.stock + '.', false); return; } it.qty++; render(); }
    else if (dec) { const it = cart.items[+dec.dataset.dec]; it.qty--; if (it.qty <= 0) cart.items.splice(+dec.dataset.dec, 1); render(); }
    else if (del) { cart.items.splice(+del.dataset.del, 1); render(); }
  });
  document.querySelectorAll('#discountBtns [data-disc]').forEach(b => b.addEventListener('click', () => { cart.discountPct = Number(b.dataset.disc); render(); }));
  const custSel = $('cartCustomer');
  if (cart.customerId) custSel.value = cart.customerId;
  custSel.addEventListener('change', () => { cart.customerId = custSel.value; save(); });
  const input = $('posSearch'), grid = $('posGrid');
  let cat = '';
  function applyLocal(){
    const q = (input.value || '').toLowerCase();
    grid.querySelectorAll('.pos-item').forEach(el => {
      const okQ = !q || (el.dataset.name || '').includes(q);
      const okC = !cat || (el.dataset.cat || '') === cat;
      el.style.display = (okQ && okC) ? '' : 'none';
    });
    $('posResultInfo').textContent = '';
    $('posReset').classList.add('d-none');
  }
  if (input) input.addEventListener('input', applyLocal);
  document.querySelectorAll('#posCategoryChips [data-cat]').forEach(b => b.addEventListener('click', () => { cat = b.dataset.cat; applyLocal(); }));
  function imgUrl(v){
    v = String(v == null ? '' : v);
    if (!v) return '/app-assets/images/products/mug.svg';
    if (/^https?:\/\//.test(v)) return v;
    return v.charAt(0) === '/' ? v : (v.indexOf('storage/') === 0 ? '/' + v : '/storage/' + v);
  }
  function cardHtml(p){
    const inCart = (cart.items.find(i => i.id === p.id) || {}).qty || 0;
    return '<div class="card pos-item" data-id="'+p.id+'" data-cat="'+(p.cat||'')+'" data-name="'+esc(((p.name||'')+' '+(p.sku||'')).toLowerCase())+'">' +
      '<div class="pos-item-media"><img src="'+esc(imgUrl(p.img))+'" alt="'+esc(p.name||'Produk')+'" loading="lazy" onerror="this.onerror=null;this.src=\'/app-assets/images/products/mug.svg\'"></div>' +
      '<div class="pos-item-body"><span class="pos-item-name">'+esc(p.name)+'</span>' +
      '<span class="pos-item-meta">'+esc(p.sku||'')+(p.catName?' · '+esc(p.catName):'')+'</span>' +
      '<span class="pos-item-meta">Stok: '+p.stock+(inCart?' · di keranjang: '+inCart:'')+'</span></div>' +
      '<div class="pos-item-foot"><strong class="pos-item-price">'+fmt(p.price)+'</strong>' +
      '<button type="button" class="btn btn-primary btn-sm pos-add-btn" data-api-add="'+p.id+'"><i class="bi bi-cart-plus"></i>Tambah</button></div></div>';
  }
  if (input) input.addEventListener('keydown', e => {
    if (e.key !== 'Enter') return;
    e.preventDefault();
    const q = input.value.trim();
    if (!q) { applyLocal(); return; }
    fetch('/api/pos/products?search=' + encodeURIComponent(q), { headers: { 'Accept': 'application/json' } })
      .then(r => r.json()).then(j => {
        const list = (j.data || j).map(x => ({ id:x.id, name:x.name, sku:x.sku, price:Number(x.selling_price||0), stock:Number(x.current_stock||0), cat:x.category_id, catName:x.category&&x.category.name, tracking:x.serial_tracking||'none', img:(x.image_url || x.image || '') }));
        list.forEach(p => { byId[p.id] = p; });
        grid.innerHTML = list.length ? list.map(cardHtml).join('') : '<div class="card"><div class="card-body text-center text-muted py-4">Tidak ketemu — coba kata kunci lain.</div></div>';
        $('posResultInfo').textContent = list.length + ' hasil dari database untuk "' + q + '"';
        $('posReset').classList.remove('d-none');
        $('kpiProducts').textContent = list.length;
      }).catch(() => flash('Gagal mencari — periksa koneksi.', false));
  });
  $('posReset').addEventListener('click', () => location.reload());
  grid.addEventListener('click', e => {
    const b = e.target.closest('[data-api-add]');
    if (!b) return;
    const p = byId[b.dataset.apiAdd];
    if (p) { addItem(p, 1); flash(p.name + ' masuk keranjang.'); }
  });
  const bc = $('posBarcode');
  function scan(){
    const code = (bc.value || '').trim();
    if (!code) return;
    fetch('/api/pos/barcode/' + encodeURIComponent(code), { headers: { 'Accept': 'application/json' } })
      .then(r => { if (!r.ok) throw 0; return r.json(); })
      .then(x => {
        addItem({ id:x.id, name:x.name, price:Number(x.selling_price||0), stock:Number(x.current_stock||0), tracking:x.serial_tracking||'none' }, 1);
        flash(x.name + ' masuk keranjang.');
        bc.value = '';
      }).catch(() => flash('Barcode/SKU "' + code + '" tidak ditemukan.', false));
  }
  bc.addEventListener('keydown', e => { if (e.key === 'Enter') { e.preventDefault(); scan(); } });
  $('btnHold').addEventListener('click', () => {
    if (!cart.items.length) { flash('Keranjang masih kosong.', false); return; }
    const token = document.querySelector('meta[name="csrf-token"]').content;
    fetch('/app/hold-orders', { method:'POST', headers:{ 'Content-Type':'application/json','Accept':'application/json','X-CSRF-TOKEN':token },
      body: JSON.stringify({ customer_id: cart.customerId || null, items: cart.items.map(i => ({ product_id:i.id, qty:i.qty, price:i.price })) }) })
      .then(r => r.json()).then(j => {
        if (!j.ok) throw 0;
        flash('Ditahan sebagai #' + j.id + '. Lihat di Hold orders.');
        const k = $('kpiHeld'); if (k) k.textContent = Number(k.textContent||0) + 1;
      }).catch(() => flash('Gagal menahan — coba lagi.', false));
  });
  $('btnPay').addEventListener('click', () => {
    if (!cart.items.length) { flash('Keranjang masih kosong.', false); return; }
    save();
    location.href = '/pos';
  });
  document.addEventListener('keydown', e => {
    if (e.key === 'F2') { e.preventDefault(); input.focus(); }
    else if (e.key === 'F4') { e.preventDefault(); bc.focus(); }
    else if (e.key === 'F9') { e.preventDefault(); $('btnPay').click(); }
  });
  render();
})();
</script>
@endpush
