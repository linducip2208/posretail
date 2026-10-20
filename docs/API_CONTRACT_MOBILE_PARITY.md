# API Contract — Mobile Parity (Flutter ↔ Laravel)

Tujuan: aplikasi kasir Android menampilkan status **Terhubung** per modul web secara otomatis
(`WebModuleScreen`), tanpa update aplikasi, segera setelah endpoint backend live.
Prinsip probe: `GET /api/v1/<modul>` **tanpa token** → `401` = modul SUDAH ada (tinggal pakai),
`404` = modul BELUM ada (perlu backend).

Hasil probe: 2026-10-21, `http://127.0.0.1:8765` (tanpa token).

| Modul | Probe | Status |
|---|---|---|
| products, customers, outlets, categories, payment-methods | 401 | SUDAH ADA |
| purchase-orders, suppliers, stock-opnames, stock-transfers, stock-movements, returns, held-carts, discounts, installments, loyalty, raw-materials, users, roles, attendances, reports, brands, units, expenses, shifts | 404 | BELUM ADA |

## Konvensi lama (wajib ditiru)

**ProductProvider (list):** query `search` (maks 100 char, cocokkan nama/SKU/barcode),
`page`, `per_page` (default 50, maks 100), `outlet_id` untuk scoping outlet user.
Envelope respons: `{ "data": [...], "meta": { "current_page", "per_page", "total" } }`.
Contoh nyata: `ProductController@index` (`paginate(min(100, max(1, per_page ?? 50)))`).

**ReportProvider (laporan):** query `start_date`, `end_date` (format `Y-m-d`, default 30 hari
terakhir), `outlet_id` opsional, **rentang maks 93 hari** (tolak 422 bila lebih).

Semua endpoint baru: `auth:sanctum` + scoping outlet via `getAccessibleOutletIds()`
(kasir hanya outletnya; owner semua). Error validasi → `422`. Rate limit API 120/min.

## 14 grup endpoint

### 1. Purchase Orders — prioritas TINGGI
- `GET /api/v1/purchase-orders` — list (konvensi ProductProvider + `status`, `supplier_id`)
- `POST /api/v1/purchase-orders` — body: `supplier_id`, `outlet_id`, `items[{product_id, quantity, unit_price}]`
- `GET /api/v1/purchase-orders/{id}` — detail + items
- `POST /api/v1/purchase-orders/{id}/receive` — terima barang (stok + payable otomatis via event model)
- Workflow status: `draft → dipesan → dikirim → diterima`
- Role: owner, manager, admin, gudang (kasir: tidak)

### 2. Suppliers — prioritas TINGGI
- `GET /api/v1/suppliers` — list (search nama/kontak)
- `POST /api/v1/suppliers` — body: `name`, `contact_person`, `phone`, `address`
- `GET /api/v1/suppliers/{id}` — detail + riwayat PO + rating rata-rata
- Role: owner, manager, admin, gudang

### 3. Stock Opnames — prioritas TINGGI
- `GET /api/v1/stock-opnames` — list (`status`, `outlet_id`)
- `POST /api/v1/stock-opnames` — body: `outlet_id`, `notes`
- `POST /api/v1/stock-opnames/{id}/items` — body: `items[{product_id, physical_stock}]`
- `POST /api/v1/stock-opnames/{id}/approve` — terapkan koreksi (butuh `edit-inventori`)
- Workflow status: `draft → dihitung → disetujui → selesai`
- Role: owner, manager, admin, gudang

### 4. Stock Transfers — prioritas TINGGI
- `GET /api/v1/stock-transfers` — list (`status`, `from_outlet_id`, `to_outlet_id`)
- `POST /api/v1/stock-transfers` — body: `from_outlet_id`, `to_outlet_id`, `items[{product_id, quantity}]`
- `POST /api/v1/stock-transfers/{id}/send`, `POST /api/v1/stock-transfers/{id}/receive`
- Workflow status: `draft → dikirim → diterima`
- Role: owner, manager, admin, gudang

### 5. Stock Movements — prioritas SEDANG
- `GET /api/v1/stock-movements` — read-only (konvensi ProductProvider + `product_id`, `type[in/out]`)
- Berisi `quantity_before → quantity_after`, `reference_type`, `reference_id`
- Role: owner, manager, admin, gudang (kasir: lihat saja stok outletnya)

### 6. Returns — prioritas SEDANG
- `GET /api/v1/returns` — list (`status`, `outlet_id`)
- `POST /api/v1/returns` — body: `order_id`, `items[{order_item_id, quantity}]`, `reason` (wajib)
- `POST /api/v1/returns/{id}/approve` — stok kembali otomatis
- Workflow status: `diajukan → disetujui/ditolak → selesai`
- Role: owner, manager, admin, kasir (ajukan); approve: owner, manager

### 7. Held Carts — prioritas SEDANG
- `GET /api/v1/held-carts` — list keranjang tertahan user login
- `POST /api/v1/held-carts` — body: `label`, `items` (JSON keranjang), auto-expire 24 jam
- `POST /api/v1/held-carts/{id}/resume` — kembalikan ke keranjang aktif
- `DELETE /api/v1/held-carts/{id}`
- Role: owner, manager, admin, kasir

### 8. Discounts — prioritas SEDANG
- `GET /api/v1/discounts` — template aktif (`type`: percent/fixed/buy_x_get_y)
- Field: `name`, `type`, `value`, `min_purchase`, `start_date`, `end_date`
- Read-only di mobile (CRUD di web); validasi `POST /api/v1/orders` memakai template aktif
- Role: semua role login (baca)

### 9. Installments & DP — prioritas SEDANG
- `GET /api/v1/installments` — list (`status`, `outlet_id`)
- Dibuat via `POST /api/v1/orders` (flag `is_installment`, `installment_period`, `installment_count`, `deposit_amount`)
- `POST /api/v1/installments/{id}/pay` — body: `amount`, `payment_method_id`
- Workflow status: `aktif → lunas / macet`
- Role: owner, manager, admin, kasir

### 10. Loyalty — prioritas SEDANG
- `GET /api/v1/loyalty/summary?customer_id=` — poin, tier, grup
- `GET /api/v1/loyalty/rewards` — katalog reward aktif + `points_required`
- `POST /api/v1/loyalty/redeem` — body: `customer_id`, `reward_id`
- Poin earned otomatis per order completed (event model, bukan endpoint)
- Role: owner, manager, admin, kasir

### 11. Raw Materials — prioritas RENDAH
- `GET /api/v1/raw-materials` — list (search, `low_stock` flag)
- `POST /api/v1/raw-materials` — body: `name`, `unit_id`, `cost_per_unit`, `current_stock`, `min_stock`
- `GET /api/v1/raw-materials/{id}` — detail + recipe pemakaian
- Role: owner, manager, admin, gudang

### 12. Users & Roles — prioritas RENDAH
- `GET /api/v1/users` — list user se-outlet (tanpa password hash)
- `GET /api/v1/users/me` — alias `/api/v1/user` yang sudah ada
- `GET /api/v1/roles` — daftar role + permission (read-only, untuk badge UI)
- Role: owner, manager (kasir/gudang: hanya `/user`)

### 13. Attendances — prioritas RENDAH
- `POST /api/v1/attendances/clock-in` — body: `outlet_id` (+ GPS opsional)
- `POST /api/v1/attendances/clock-out`
- `GET /api/v1/attendances/today` — status kehadiran user hari ini
- Workflow status: `hadir / terlambat / izin / sakit`
- Role: semua role login (data sendiri); rekap: owner, manager

### 14. Reports — prioritas TINGGI
- `GET /api/v1/reports/sales?start_date&end_date&outlet_id` — omzet, transaksi, rata-rata, top produk
- `GET /api/v1/reports/stock?outlet_id` — nilai stok, menipis, overstock
- `GET /api/v1/reports/finance?start_date&end_date&outlet_id` — pemasukan vs pengeluaran
- Konvensi ReportProvider: rentang **maks 93 hari** (422 bila lebih)
- Role: owner, manager, admin (kasir/gudang: tidak)

## Role matrix ringkas

| Grup | owner | manager | admin | kasir | gudang |
|---|---|---|---|---|---|
| purchase-orders, suppliers, stock-* | ✓ | ✓ | ✓ | – | ✓ |
| returns (ajukan/approve) | ✓ | ✓ | ✓ | ajukan | – |
| held-carts, installments, loyalty | ✓ | ✓ | ✓ | ✓ | – |
| discounts, attendances (baca/pribadi) | ✓ | ✓ | ✓ | ✓ | ✓ |
| raw-materials | ✓ | ✓ | ✓ | – | ✓ |
| users/roles | ✓ | ✓ | – | – | – |
| reports | ✓ | ✓ | ✓ | – | – |

## Cara pakai (backend Laravel)

1. Daftarkan route per grup dengan `auth:sanctum` (+ throttle API) mengikuti pola `routes/api.php` yang ada.
2. Tiru envelope & validasi dari `ProductController@index` (list) dan `ReportExportController` (laporan).
3. Jangan buat permission baru — pakai `canAccess('<grup>', '<aksi>')` yang sudah ada.
4. Setelah live, probe ulang tanpa token: grup yang tadinya `404` harus menjadi `401` (atau `200` untuk webhook publik). Saat itulah `WebModuleScreen` otomatis berstatus Terhubung — tanpa update aplikasi.

## Checklist verifikasi

- [ ] Probe tanpa token: 0 grup mengembalikan 404 (target), sisanya 401/200
- [ ] Probe dengan token kasir: outlet lain → 403 (isolasi outlet)
- [ ] List: `search` + `page` + `per_page=1` mengembalikan envelope `{data, meta}`
- [ ] Laporan: rentang > 93 hari → 422
- [ ] Workflow: PO draft→diterima menambah stok; retur approve mengembalikan stok; approve order mengubah status
- [ ] Rate limit: > 120 req/menit → 429
- [ ] Tidak ada perubahan kontrak endpoint lama (Flutter lama tetap jalan)
