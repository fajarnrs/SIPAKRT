# 📊 Data Warga - Sistem Pendataan Warga## Pendataan Warga



Aplikasi berbasis web untuk mengelola data penduduk, Kartu Keluarga (KK), dan RT menggunakan Laravel 10 dan Filament Admin Panel.Aplikasi Laravel + Filament untuk pendataan warga per RT/KK. Panel admin tersedia di `/admin` dan hanya dapat diakses oleh pengguna dengan atribut `is_admin = true`.



## ✨ Fitur Utama## Menjalankan di Nginx



- 📋 **Manajemen Kartu Keluarga (KK)**1. **Instal dependensi produksi**

  - CRUD data KK dengan detail lengkap   ```bash

  - Auto-update status KK (aktif/non-aktif) berdasarkan status kepala keluarga   composer install --no-dev --optimize-autoloader

  - View page (read-only) untuk melihat detail KK   php artisan key:generate    # jika belum

  - Auto-create resident kepala keluarga saat KK dibuat   php artisan migrate --seed  # membuat admin & data demo

   ```

- 👥 **Manajemen Warga/Resident**

  - Data lengkap penduduk dengan relasi ke KK2. **Siapkan konfigurasi Nginx**

  - Auto-sync kepala keluarga ke tabel residents   - Salin berkas contoh `deploy/nginx.conf` ke `/etc/nginx/sites-available/pendataan-warga`.

  - Status warga: Aktif, Meninggal, Pindah, dll   - Sesuaikan `server_name`, path `root`, dan `fastcgi_pass` dengan lingkungan server Anda.

   - Aktifkan situs: `sudo ln -s /etc/nginx/sites-available/pendataan-warga /etc/nginx/sites-enabled/`.

- 🏘️ **Manajemen RT & Pengurus**   - Uji konfigurasi lalu muat ulang:

  - Data RT dengan ketua RT     ```bash

  - Data pengurus RT dengan periode jabatan     sudo nginx -t

     sudo systemctl reload nginx

- 📤 **Export Excel**     ```

  - Export semua data KK

  - Filter berdasarkan RT3. **Pastikan PHP-FPM dan permission benar**

  - Filter berdasarkan status   ```bash

  - Format terstruktur dengan styling   sudo systemctl enable --now php8.1-fpm

   sudo chown -R www-data:www-data storage bootstrap/cache

- 🔐 **Multi-user dengan Role Management**   sudo chmod -R ug+rwx storage bootstrap/cache

  - Admin: Full access   ```

  - RT: Access terbatas per RT

4. **Konfigurasi domain/HTTPS**

## 🛠️ Tech Stack   - Gunakan Let’s Encrypt (mis. `certbot --nginx`) untuk menambahkan blok `listen 443 ssl`.

   - Perbarui `.env` (`APP_URL`, detail database, mail) kemudian jalankan `php artisan config:cache`.

- **Framework**: Laravel 10.49.1

- **PHP**: 8.1+5. **Login ke panel**

- **Admin Panel**: Filament 2.17.58   - Buka `http(s)://domain-anda/admin`

- **Database**: MySQL 8.0   - Gunakan akun default `admin@warga.test / admin123` dan segera ganti kata sandi.

- **Export**: Maatwebsite/Excel 3.1.59

- **Frontend**: Livewire, Alpine.js, Tailwind CSSReferensi konfigurasi tambahan tersedia di [dokumentasi Laravel deploy](https://laravel.com/docs/deployment).


## 📦 Instalasi

### Quick Start (Regular)

```bash
# Clone & setup
git clone https://github.com/fajarnrs/SIPAKRT.git
cd SIPAKRT
composer install
cp .env.example .env
php artisan key:generate

# Database (edit .env dulu!)
php artisan migrate
php artisan make:filament-user

# Run
php artisan serve
# http://localhost:8000/admin
```

### Quick Start (Docker) 🐳

```bash
# Clone & setup
git clone https://github.com/fajarnrs/SIPAKRT.git
cd SIPAKRT
cp .env.example .env  # Edit DB_HOST=db

# Start
docker-compose up -d --build
docker-compose exec app composer install
docker-compose exec app php artisan key:generate
docker-compose exec app php artisan migrate
docker-compose exec app php artisan make:filament-user

# Access: http://localhost:8000/admin
```

## 🔧 Post-Installation

```bash
# Repair data lama (jika ada KK tanpa kepala keluarga)
php scripts/repair-missing-head-residents.php

# Clear cache
php artisan optimize:clear
```

## 🚀 Deployment

Lihat [DEPLOYMENT.md](DEPLOYMENT.md) untuk panduan lengkap deployment ke cPanel, VPS, atau Docker Production.

## � Dokumentasi

- **README.md** - Quick start guide
- **DEPLOYMENT.md** - Production deployment
- **CONTRIBUTING.md** - Contributing guidelines  
- **GITHUB_DEPLOYMENT.md** - GitHub setup guide

## 📝 License

MIT License

## � Credits

Built with Laravel • Filament • Maatwebsite/Excel
