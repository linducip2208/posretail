# POS Retail — API v1 Contract

Base URL: `{APP_URL}/api/v1`. Semua endpoint kasir memakai guard
`auth:api` (Sanctum Bearer) + throttle `120/menit`.
Setiap response membawa header `X-Request-ID` untuk pelacakan log.

## Konvensi

- Format sukses list: `{ "data": [...] }`, products/customers: `{ "data": [...], "meta": {...} }`
- Format error validasi: `422 { "message": "...", "errors": { "field": [...] } }`
- Kode umum: `400` validasi umum · `401` token salah/kedaluwarsa ·
  `403` tanpa akses outlet/ability · `404` tidak ditemukan ·
  `422` validasi · `429` throttle
- Token ability: login menerbitkan `['pos-access', <role>]`.
  `POST /orders` & `POST /orders/sync-batch` menolak token tanpa
  `pos-access`/role (`403`).
- **Server adalah source of truth**: harga, stok, diskon, promo, voucher,
  pajak, dan total selalu dihitung ulang dari database. `unit_price`,
  `discount_amount`, `tax_amount` dari client **diabaikan**.

## Auth

| Method | URL | Auth | Body | Response |
|---|---|---|---|---|
| POST | `/login` | tidak | `{email, password, device_name?}` | `200 {token, user:{id,name,email,role,outlets[]}}` |
| GET | `/user` | ya | — | `200 {id,name,email,role,outlets[]}` |
| GET | `/outlets` | ya | — | `200 {data:[{id,name,code}]}` outlet aktif milik user |
| POST | `/logout` | ya | — | `200` + token saat ini dicabut |

Login salah → `422 {errors:{email:[...]}}`. Rate limit login: 10/menit.

## Produk

| Method | URL | Query/Body | Response |
|---|---|---|---|
| GET | `/products` | `search?` (nama/SKU/barcode), `category_id?`, `per_page?` (default 50, **di-cap 100**, tanpa `max` 422) | `{data:[product], meta}` |
| GET | `/products/{product}` | — | `{data:product}` |
| POST | `/products/barcode` | `{barcode}` (+ cocok varian) | `{data:product}` / `404` |

`product`: `{id,name,sku,barcode,selling_price,member_price?,wholesale_price?,current_stock,image,category{name},unit{name},variants[]}`.
Catatan: tidak ada field `has_variants` — Flutter menurunkannya dari `variants[]`.

## Customer

| Method | URL | Body | Response |
|---|---|---|---|
| GET | `/customers` | `search?`, `per_page?` (cap 100) | `{data:[...], meta}` |
| GET | `/customers/{customer}` | — | `{data}` (non-admin: 403 bila tanpa order di outlet-nya) |
| POST | `/customers` | `{name*, phone?, email?, address?, customer_group_id?}` | `201 {data:customer}` |

`customer`: `{id,name,phone,email,address,total_points,customer_group{name}}`.

## Order

| Method | URL | Body | Response |
|---|---|---|---|
| POST | `/orders` | lihat di bawah | `201 {data:order}` |
| GET | `/orders/today` | `outlet_id?` | `200 {data:[order]}` |
| GET | `/orders/history` | `start_date?`, `end_date?` (maks 93 hari), `outlet_id?` | `200 {data:[order]}` (limit 500) |
| GET | `/orders/{order}` | — | `200 {data:order}` (403 bila beda outlet) |
| POST | `/orders/sync-batch` | `{orders:[...≤50]}` | `200 {data:[{client_uuid,status,order_number?,id?,message?}]}` |
| POST | `/orders/{order}/split` | `{moves:[{order_item_id,quantity}], payments?}` | `200` |
| POST | `/orders/{order}/refund` | `{items:[{order_item_id,quantity}], reason?}` | `201` (butuh izin hapus-transaksi) |

### POST /orders

```json
{
  "outlet_id": 1,
  "client_uuid": "uuid-v4-opsional",
  "items": [{"product_id": 5, "product_variant_id": null, "quantity": 2, "discount_percent": 0}],
  "payments": [{"payment_method_id": 2, "amount": 11100}],
  "customer_id": null, "employee_id": null,
  "order_type": "walk_in",
  "voucher_code": null, "order_notes": null, "notes": null,
  "deposit_amount": 0,
  "is_installment": false, "installment_period": "monthly", "installment_count": 1
}
```

- Produk harus aktif dan milik outlet tersebut (atau produk pusat).
  Stok dicek + dikunci (`lockForUpdate`) dalam transaksi DB.
- `payments` boleh kosong → order `pending`; lunas/parsial dihitung server.
- `order`: `{id,order_number,queue_number,customer_id,outlet_id,user_id,
  subtotal,discount_amount,tax_amount,total_amount,payment_status,
  order_status,created_at,items[]:{...,product{name}},payments[]:{...,payment_method{name}},
  customer_name,outlet_name,cashier_name}`.

### POST /orders/sync-batch (idempoten)

Tiap entri: `{client_uuid* (≤100 char), outlet_id*, items*, payments*,
customer_id?, order_type?, notes?}`.
- `created` = order baru (kolom `orders.client_uuid` unique).
- `duplicate` = uuid sudah ada (termasuk retry/race) → kembalikan order lama.
- `failed` = `{message}` manusiawi; outlet tak-berhak → `failed` per-entri.

## Shift / Deposit (POS)

| Method | URL | Body |
|---|---|---|
| POST | `/shifts/open` | `{outlet_id*, starting_cash*, notes?}` (422 bila masih ada shift terbuka) |
| POST | `/shifts/{shift}/close` | `{ending_cash*}` → hitung `expected_cash` + selisih |
| POST | `/customers/{customer}/deposits` | `{outlet_id*, type: topup\|use\|refund, amount*, notes?}` |

Semua validasi akses outlet user (403 bila tak berhak).

## Payment Gateway & Webhook

- `POST /payment/create {payment_method_id, order_number, amount}` → data transaksi gateway.
- `POST /payment/status`, `GET /payment/presets`.
- `POST /v1/webhooks/{providerCode}` — verifikasi HMAC-SHA256
  (`X-Signature`, secret per-provider terenkripsi). Throttle 30/menit.
- `POST /v1/webhooks/marketplace/{platform}` — bila env
  `MARKETPLACE_WEBHOOK_TOKEN` di-set, wajib header `X-Webhook-Token`
  yang cocok (401 bila salah); bila env kosong, tidak ditegakkan.

## Misc

- `GET /categories`, `GET /inventory/forecast?weeks?&outlet_id?`,
  `GET /payment/presets`.
- Endpoint `GET /tables` **tidak ada** (fitur restoran dihapus):
  aplikasi kasir tidak boleh memanggilnya.
