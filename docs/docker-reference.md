# Docker Reference Manual

## Overview
Server Debian menjalankan Docker Compose sebagai platform aplikasi production.

### Service
- nginx (reverse proxy)
- mysql (shared database)
- php74
- php81
- php82
- phpmyadmin
- aplikasi Node.js (mis. arka-fms)

## Struktur Folder

```text
/home/skyone/stack
├── docker-compose.yml
├── nginx/
├── mysql/
├── php74/
├── php81/
├── php82/
├── backup/
│   ├── backup-mysql.sh
│   ├── backup-web.sh
│   ├── mysql/
│   └── web/
└── apps/
    ├── app74/
    ├── app81/
    │   └── arka-fms/
    └── app82/
        └── arka-hero/
```

## Arsitektur

```text
Internet
    │
 Nginx
 ├── PHP74 -> Laravel/CI
 ├── PHP81 -> Laravel
 ├── PHP82 -> Laravel
 └── Node (arka-fms)
          │
       MySQL
```

## Standar Deployment

1. Clone source ke `apps/appXX/nama-app`.
2. Pilih runtime sesuai kebutuhan.
3. Laravel menggunakan container PHP yang sesuai.
4. Next.js menggunakan container Node sendiri.
5. Semua database menggunakan host `mysql`.
6. Semua container berada pada network `appnet`.

## Laravel

- Jalankan Composer di dalam container PHP.
- Artisan dijalankan di container.
- Jangan menggunakan PHP host.

Contoh:

```bash
docker exec -it stack-php82-1 sh
cd /var/www/app82/arka-hero
composer install
php artisan migrate
```

### ARKA HERO queue + scheduler (host crontab)

Stack PHP-FPM tidak menjalankan worker terus-menerus. Gunakan driver **`database`** (`QUEUE_CONNECTION=database`, tabel `jobs`) dan crontab di host Debian:

```bash
* * * * * docker exec -w /var/www/app82/arka-hero stack-php82-1 php artisan schedule:run >> /dev/null 2>&1
* * * * * docker exec -w /var/www/app82/arka-hero stack-php82-1 php artisan queue:work database --stop-when-empty --max-time=50 --tries=3 >> /dev/null 2>&1
```

Setelah deploy: `migrate --force`, pastikan `.env` production punya `QUEUE_CONNECTION=database` dan `DOCUMENT_NOTIFICATIONS_BASE_URL=http://192.168.32.146:8080`. Tidak perlu Redis/Horizon.

Local Laragon: set `QUEUE_CONNECTION=database` lalu jalankan `php artisan queue:work` saat menguji kirim email.
## Next.js

- Dockerfile berada di root project.
- Prisma:
  - `npx prisma generate`
  - `npx prisma migrate deploy`
  - `npx prisma db seed`

## Dockerfile PHP

Minimal extension yang direkomendasikan:

- pdo_mysql
- mysqli
- zip
- gd
- opcache
- bcmath
- intl
- exif
- pcntl (CLI)

Composer diinstall di image.

## Backup

### Database
- Backup SQL
- ZIP
- Upload FTP `/backup-146/database`
- Harian

### Source Code
- ZIP per aplikasi:
  - arka-hero.zip
  - arka-fms.zip
- Upload FTP `/backup-146`
- Bulanan

## Best Practice

- restart: unless-stopped
- healthcheck untuk semua service
- logging max-size/max-file
- build image bila Dockerfile berubah
- gunakan `mysql` sebagai hostname DB

## Troubleshooting

### Composer tidak ada
Tambahkan Composer ke Dockerfile PHP lalu rebuild.

### ext-gd
Install extension GD pada image PHP.

### Prisma
Selalu gunakan DATABASE_URL dengan host `mysql`.

### Git
Composer dalam container mungkin memerlukan:

```bash
git config --global --add safe.directory /var/www/app82/arka-hero
```

## Checklist Menambah Aplikasi Baru

- Analisa runtime
- Clone source
- Dockerfile
- docker-compose
- Environment
- Database
- Migration
- Reverse proxy
- Backup
- Monitoring
- Testing
