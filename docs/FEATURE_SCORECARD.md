# Feature Scorecard — POS Retail

**Tanggal:** 19 Oktober 2026
**Metode:** setiap status harus punya bukti (test, perintah, atau file). ⛔ NOT TESTED diberi nilai 0 sampai diuji — bukan diasumsikan berhasil.

**Legenda:** ✅ Terverifikasi · 🟡 Terverifikasi sebagian · ⛔ NOT TESTED · ⚠️ Risiko/keputusan user

---

## 1. Backend Laravel

| # | Fitur | Status | Bukti |
|---|---|---|---|
| 1 | Login API + Sanctum token (`POST /api/v1/login`, throttle 10/menit) | ✅ | `routes/api.php`; test auth hijau di suite 126 |
| 2 | Otorisasi outlet per user (`getAccessibleOutletIds`) | ✅ | `OutletScope` + test `OrderIdempotencyTest` (akses lintas outlet ditolak 422) |
| 3 | Ability token POS (`pos-access`) | ✅ | `OrderController::ensurePosAbility`; suite hijau |
| 4 | **Idempotensi checkout (`POST /orders`, `client_uuid`)** | ✅ | `OrderIdempotencyTest` 3/3: retry → 200 `duplicate:true`, stok tidak dobel, uuid asing tidak bocor |
| 5 | Sync batch offline (`POST /orders/sync-batch`, max 50, hasil per-order) | ✅ | `syncBatch` + test offline Flutter (status `created`/`duplicate`) |
| 6 | Harga/stok diambil server (anti price-tampering) | ✅ | Validasi `store()` mengabaikan `unit_price` klien; stok dicek di `CheckoutService` |
| 7 | Endpoint AJAX POS (`products`, `barcode`, `display`) | ✅ | Wajib auth + `throttle:120,1`; test `SecurityHardeningTest` 8/8 |
| 8 | Customer display ter-scope outlet | ✅ | Test: outlet asing → `order_id` null |
| 9 | CSRF POS checkout | ✅ | Exemption dihapus; `pos-offline.js` + blade mengirim `X-CSRF-TOKEN` |
| 10 | Gate kredensial demo (production default aman) | ✅ | `DemoMode`, 6 lokasi digate, test 3 skenario demo |
| 11 | Seeder demo tidak jalan di production | ✅ | `DemoDataSeeder` baris 36 + test |
| 12 | Remediasi akun demo (`pos:disable-demo-accounts`) | ✅ | Command + test |
| 13 | Scheduler 13 job terdaftar & terdokumentasi akurat | ✅ | `routes/console.php`; README dikoreksi |
| 14 | Backup DB harian (`pos:backup-database`) + cloud (`pos:cloud-backup`) | 🟡 | Kode ada & realistis; **belum dijalankan di server produksi** |
| 15 | Migrasi DB kosong → skema lengkap | ✅ | `migrate --force` pada SQLite baru: semua DONE |
| 16 | Webhook payment/marketplace (throttle 30/menit) | 🟡 | Route & throttle terverifikasi; integrasi pihak ketiga NOT TESTED |
| 17 | Test suite menyeluruh | ✅ | **126/126 PASS, 309 assertions** |
| 18 | `composer validate` | 🟡 | Valid; 3 warning constraint pre-existing (tidak menghambat) |

**Skor backend: 92/100** — potongan: verifikasi MySQL produksi (⛔), webhook eksternal (⛔), constraint composer (🟡).

---

## 2. Client Flutter (Android)

| # | Fitur | Status | Bukti |
|---|---|---|---|
| 1 | Login + auto-login token aman | ✅ | `login_test` + widget test; token di `flutter_secure_storage` |
| 2 | Pembersihan sesi 401 + navigasi login (debounce) | ✅ | Test baru: 3 request 401 → `onUnauthorized` 1 kali |
| 3 | Pembedaan error jaringan vs error server | ✅ | `ApiNetworkException` + test mapping; UI oranye vs merah |
| 4 | Timeout terkonsolidasi (`requestTimeout` 15s) | ✅ | `api_config.dart` dipakai di `_send()` |
| 5 | Keranjang & kalkulasi (subtotal, diskon, pajak) | ✅ | `cart_test` 4/4 |
| 6 | **Checkout retry-safe (UUID stabil per keranjang)** | ✅ | `_clientUuidFor` fingerprint + idempotensi server teruji |
| 7 | Auto-antre offline saat server tak terjangkau | ✅ | `lastErrorOffline` → `queueOrder` (kode + test komponen queue) |
| 8 | Antrean offline lokal (SQLite) | ✅ | `offline_sync_test` (DB FFI in-memory) |
| 9 | Sync offline: batch 50 + loop antrean >50 | ✅ | Test: 60 order → batch `[50,10]` → semua sinkron |
| 10 | Sync offline: status `duplicate` = sukses | ✅ | Test khusus |
| 11 | Sync offline: batch ditolak (422) → backoff + `last_error`, data aman | ✅ | Test khusus |
| 12 | Sync offline: jaringan putus → tanpa penalti attempts | ✅ | Test khusus |
| 13 | Scan barcode (mobile_scanner) | 🟡 | Kode & izin kamera ada; **uji kamera perangkat fisik NOT TESTED** |
| 14 | Cetak struk printer thermal BLE | 🟡 | `PrinterService` + permission legacy ditambahkan; **print fisik NOT TESTED** |
| 15 | Logo struk (`assets/logo.png`) | ✅ | Aset brand terpasang (sebelumnya file tidak ada) |
| 16 | Icon launcher & splash brand | ✅ | 5 mipmap diganti; splash pakai `assets/app_icon.png` |
| 17 | Tema sesuai palet brand (#2563EB) | ✅ | `main.dart` (light+dark) |
| 18 | Analisis statis | ✅ | `flutter analyze` → **No issues found!** |
| 19 | Test suite | ✅ | **18/18 PASS** (+6 test baru) |
| 20 | Build APK release | ✅ | **BERHASIL** — `app-release.apk` 69.4 MB, ditandatangani keystore rilis (`android/key.properties`, ter-gitignore) |
| 21 | Lisensi `flutter_blue_plus` (`nonprofit`) | ⚠️ | Keputusan user untuk distribusi komersial |
| 22 | `esc_pos_utils 1.1.0` (abandoned) | ⚠️ | Berfungsi; migrasi `esc_pos_utils_plus` opsional |

**Skor client: 86/100** — potongan utama: print/scan device fisik (⛔), risiko lisensi `flutter_blue_plus` (⚠️).

---

## 3. Aset & Branding

| Item | Status | Bukti |
|---|---|---|
| Inventarisasi 14 aset `posretail_assets_terpisah` | ✅ | Semua diperiksa; 6 dipakai |
| Icon launcher hasil ekstraksi presisi + transparansi sudut | ✅ | Verifikasi piksel (0 artefak), file mipmap baru |
| Palet warna resmi diterapkan (Flutter) | ✅ | Seed `#2563EB` |
| Aset siap pakai Laravel (`public/brand/`) | ✅ | 2 file |

---

## 4. Ringkasan Skor Keseluruhan

| Area | Skor | Catatan |
|---|---|---|
| Backend Laravel | 92/100 | Solid; sisa item produksi |
| Client Flutter | 86/100 | Sisa item hardware & legal |
| Keamanan | 95/100 | Semua temuan kritis/tinggi tertutup + tes |
| Dokumentasi | 95/100 | Drift utama dikoreksi (scheduler, rate limit, backup, demo account) |
| Testing | 92/100 | 144 test hijau total + APK rilis sukses; hardware/produksi belum |

**Kesiapan: STAGING ✅ · PRODUCTION 🟡 (memenuhi syarat bagian L di `FINAL_AUDIT_REPORT.md`)**

---

## 5. Jalur Menuju 100/100 (Urut Prioritas)

1. Uji printer thermal + scan di device fisik (Android 12+ dan ≤11) → naikkan #13/#14 Flutter.
2. Backup keystore rilis di luar repo (amanah operasional — kehilangan keystore menghambat update Play Store).
3. Keputusan lisensi `flutter_blue_plus` (komersial) & opsi migrasi `esc_pos_utils_plus`.
4. Verifikasi migrasi + backup di MySQL produksi & jalankan smoke test staging.
5. Uji webhook payment/marketplace dengan sandbox.
6. Rapikan constraint composer (`barryvdh/laravel-dompdf`, `picqer/php-barcode-generator`, versi Filament).
