# Deployment Guide — POS Retail

Panduan deployment production untuk POS Retail (Laravel backend + Flutter kasir app).

---

## Prasyarat Server

- PHP 8.2+
- MySQL 8.0+ / MariaDB 10.6+
- Composer 2.x
- Node.js 18+ (untuk build asset)
- Nginx atau Apache
- Supervisor (untuk queue worker + scheduler)
- Flutter SDK 3.x (untuk build APK kasir)
- Git

---

## 1. Clone & Setup Project

```bash
cd /var/www
git clone <repo-url> pos-retail
cd pos-retail

composer install --no-dev --optimize-autoloader
npm install
npm run build
```

---

## 2. Environment Configuration

```bash
cp .env.example .env
php artisan key:generate
```

Edit `.env` dan sesuaikan:

| Variable | Deskripsi | Contoh |
|---|---|---|
| `APP_NAME` | Nama aplikasi | `POS Retail` |
| `APP_ENV` | Environment | `production` |
| `APP_DEBUG` | Debug mode | `false` |
| `APP_URL` | Domain production | `https://posretail.test` |
| `DB_CONNECTION` | Database driver | `mysql` |
| `DB_HOST` | Host database | `127.0.0.1` |
| `DB_PORT` | Port database | `3306` |
| `DB_DATABASE` | Nama database | `pos_retail` |
| `DB_USERNAME` | User database | `root` |
| `DB_PASSWORD` | Password database | `***` |
| `SANCTUM_STATEFUL_DOMAINS` | Domain untuk SPA auth | `posretail.test` |
| `CORS_ALLOWED_ORIGINS` | Origins yang diizinkan | `*` |
| `QUEUE_CONNECTION` | Queue driver | `database` |
| `SESSION_DRIVER` | Session driver | `database` |
| `MAIL_MAILER` | Mail driver | `smtp` |
| `LICENSE_SERVER_URL` | License server | `https://whitelabel.co.id` |
| `LICENSE_DEV_BYPASS` | Bypass license dev | `false` |

---

## 3. Database Setup

```bash
mysql -u root -p -e "CREATE DATABASE pos_retail CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"

php artisan migrate --force
php artisan db:seed --class=DatabaseSeeder
```

Seeder akan membuat:
- 1 outlet (default)
- Payment methods (Cash, Debit, QRIS, Transfer)
- Admin user: `admin@pos-retail.test` / `password`
- Kategori & unit default

---

## 4. Storage & Permissions

```bash
php artisan storage:link
chmod -R 775 storage bootstrap/cache
chown -R bang1436:bang1436 storage bootstrap/cache
```

---

## 5. Nginx Configuration

Buat file `/etc/nginx/sites-available/posretail`:

```nginx
server {
    listen 80;
    server_name posretail.test;
    root /var/www/pos-retail/public;

    add_header X-Frame-Options "SAMEORIGIN";
    add_header X-Content-Type-Options "nosniff";

    index index.php;
    charset utf-8;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location = /favicon.ico { access_log off; log_not_found off; }
    location = /robots.txt  { access_log off; log_not_found off; }

    error_page 404 /index.php;

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
        fastcgi_buffers 16 16k;
        fastcgi_buffer_size 32k;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }

    # Security — block sensitive paths
    location ~ ^/(\.env|composer\.(json|lock)|package\.json|artisan)$ {
        deny all;
        return 404;
    }

    # API rate limiting
    location /api/ {
        try_files $uri $uri/ /index.php?$query_string;
        limit_req zone=api burst=30 nodelay;
    }
}

# Rate limit zone (in http block):
# limit_req_zone $binary_remote_addr zone=api:10m rate=10r/s;
```

Aktifkan site:

```bash
ln -s /etc/nginx/sites-available/posretail /etc/nginx/sites-enabled/
nginx -t
systemctl reload nginx
```

---

## 6. SSL (Let's Encrypt)

```bash
apt install certbot python3-certbot-nginx
certbot --nginx -d posretail.test
```

---

## 7. Supervisor — Queue Worker + Scheduler

Install Supervisor:

```bash
apt install supervisor
```

Buat file `/etc/supervisor/conf.d/posretail-worker.conf`:

```ini
[program:posretail-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /var/www/pos-retail/artisan queue:work database --sleep=3 --tries=3 --max-time=3600
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=bang1436
numprocs=2
redirect_stderr=true
stdout_logfile=/var/www/pos-retail/storage/logs/worker.log
stopwaitsecs=3600
```

Buat file `/etc/supervisor/conf.d/posretail-scheduler.conf`:

```ini
[program:posretail-scheduler]
process_name=%(program_name)s
command=php /var/www/pos-retail/artisan schedule:work
autostart=true
autorestart=true
user=bang1436
redirect_stderr=true
stdout_logfile=/var/www/pos-retail/storage/logs/scheduler.log
```

Reload Supervisor:

```bash
supervisorctl reread
supervisorctl update
supervisorctl start posretail-worker:*
supervisorctl start posretail-scheduler
```

---

## 8. Post-Deployment Check

```bash
# Clear all caches
php artisan optimize:clear

# Rebuild cache for production
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Verify
php artisan route:list --except-vendor
php artisan about
```

Verifikasi endpoint:
- `GET /` → Landing page (200)
- `GET /admin/login` → Login admin (200)
- `GET /docs` → Dokumentasi (200)
- `GET /sitemap.xml` → Sitemap XML (200)
- `GET /robots.txt` → Robots (200)
- `POST /api/v1/login` → API login endpoint
- `GET /pos` → POS Kasir (redirect ke login jika belum auth)

---

## 9. Flutter APK Build (Kasir App)

### Prasyarat build machine:
```bash
flutter doctor
flutter clean
flutter pub get
```

### Konfigurasi base URL API

Edit `D:\project flutter\pos-retail\lib\config\api_config.dart` (atau file config API) — pastikan `baseUrl` mengarah ke server production:

```dart
static const String baseUrl = 'https://posretail.test/api/v1';
```

### Build APK Release

```bash
flutter build apk --release
```

Output: `build/app/outputs/flutter-apk/app-release.apk`

### Build App Bundle (Google Play Store)

```bash
flutter build appbundle --release
```

Output: `build/app/outputs/bundle/release/app-release.aab`

### Build untuk iOS (opsional)

```bash
flutter build ios --release
```

Perlu Xcode + Apple Developer account untuk signing.

---

## 10. License v3 Activation

POS Retail menggunakan License v3 pairing (whitelabel.co.id) untuk proteksi source code.

### Aktivasi via browser wizard:
1. Buka `https://posretail.test/__pair`
2. Masukkan license key dari pembelian
3. Ikuti wizard pairing — sistem akan generate license payload
4. Setelah pairing sukses, `lock.json` akan tersimpan di `storage/app/`

### Environment variables license:

```env
LICENSE_SERVER_URL=https://whitelabel.co.id
LICENSE_DEV_BYPASS=false
LICENSE_HEARTBEAT_INTERVAL=86400
LICENSE_HEARTBEAT_GRACE=604800
```

### Cek status license:
```bash
php artisan license:status
```

---

## 11. Backup Strategy

### Database backup (daily):

Tambahkan ke `app/Console/Kernel.php` schedule atau supervisor cron:

```bash
0 2 * * * mysqldump -u root -p'password' pos_retail | gzip > /var/backups/posretail-$(date +\%Y\%m\%d).sql.gz
```

### File backup:
- `storage/app/` — uploads, logo, lock.json
- `.env` — environment config

---

## 12. Monitoring

- **Queue**: `php artisan queue:monitor`
- **Failed jobs**: `php artisan queue:failed`
- **Logs**: `tail -f storage/logs/laravel.log`
- **Supervisor**: `supervisorctl status`

---

## Environment Variables Reference

| Variable | Default | Deskripsi |
|---|---|---|
| `APP_NAME` | `POS Retail` | Nama aplikasi |
| `APP_ENV` | `production` | Environment |
| `APP_DEBUG` | `false` | Debug mode |
| `APP_URL` | — | URL production (wajib diisi) |
| `DB_CONNECTION` | `mysql` | Database driver |
| `DB_HOST` | `127.0.0.1` | Host database |
| `DB_PORT` | `3306` | Port database |
| `DB_DATABASE` | `pos_retail` | Nama database |
| `DB_USERNAME` | — | User database |
| `DB_PASSWORD` | — | Password database |
| `QUEUE_CONNECTION` | `database` | Queue driver |
| `SESSION_DRIVER` | `database` | Session storage |
| `SANCTUM_STATEFUL_DOMAINS` | — | Domain SPA auth |
| `CORS_ALLOWED_ORIGINS` | `*` | CORS origins |
| `MAIL_MAILER` | `log` | Mail driver |
| `MAIL_HOST` | — | SMTP host |
| `MAIL_PORT` | — | SMTP port |
| `MAIL_USERNAME` | — | SMTP username |
| `MAIL_PASSWORD` | — | SMTP password |
| `MAIL_FROM_ADDRESS` | — | From email |
| `LICENSE_SERVER_URL` | `https://whitelabel.co.id` | License server |
| `LICENSE_DEV_BYPASS` | `false` | Bypass license (prod: false) |
