# 📊 SIPAKRT - Sistem Informasi Pendataan Anggota Kartu Keluarga RT# 📊 Data Warga - Sistem Pendataan Warga## Pendataan Warga



Aplikasi web untuk mengelola data Kartu Keluarga (KK), Warga, dan RT menggunakan Laravel 10 + Filament Admin Panel.



## ✨ FiturAplikasi berbasis web untuk mengelola data penduduk, Kartu Keluarga (KK), dan RT menggunakan Laravel 10 dan Filament Admin Panel.Aplikasi Laravel + Filament untuk pendataan warga per RT/KK. Panel admin tersedia di `/admin` dan hanya dapat diakses oleh pengguna dengan atribut `is_admin = true`.



- 📋 **Kartu Keluarga (KK)** - CRUD, auto-update status, view read-only, auto-create kepala keluarga

- 👥 **Warga/Resident** - Data lengkap dengan relasi KK, auto-sync kepala keluarga  

- 🏘️ **RT & Pengurus** - Manajemen RT dengan pengurus dan periode jabatan## ✨ Fitur Utama## Menjalankan di Nginx

- 📤 **Export Excel** - Export dengan filter RT dan status

- 🔐 **Role Management** - Admin (full), RT (terbatas per RT)



**Tech Stack:** Laravel 10.49 • PHP 8.1+ • Filament 2.17 • MySQL 8.0 • Maatwebsite/Excel- 📋 **Manajemen Kartu Keluarga (KK)**1. **Instal dependensi produksi**



## 🚀 Quick Start  - CRUD data KK dengan detail lengkap   ```bash



### Regular Installation  - Auto-update status KK (aktif/non-aktif) berdasarkan status kepala keluarga   composer install --no-dev --optimize-autoloader



```bash  - View page (read-only) untuk melihat detail KK   php artisan key:generate    # jika belum

git clone https://github.com/fajarnrs/SIPAKRT.git

cd SIPAKRT  - Auto-create resident kepala keluarga saat KK dibuat   php artisan migrate --seed  # membuat admin & data demo

composer install

cp .env.example .env   ```

php artisan key:generate

- 👥 **Manajemen Warga/Resident**

# Edit .env untuk database config

  - Data lengkap penduduk dengan relasi ke KK2. **Siapkan konfigurasi Nginx**

php artisan migrate

php artisan make:filament-user  - Auto-sync kepala keluarga ke tabel residents   - Salin berkas contoh `deploy/nginx.conf` ke `/etc/nginx/sites-available/pendataan-warga`.



php artisan serve  - Status warga: Aktif, Meninggal, Pindah, dll   - Sesuaikan `server_name`, path `root`, dan `fastcgi_pass` dengan lingkungan server Anda.

# http://localhost:8000/admin

```   - Aktifkan situs: `sudo ln -s /etc/nginx/sites-available/pendataan-warga /etc/nginx/sites-enabled/`.



### Docker Installation- 🏘️ **Manajemen RT & Pengurus**   - Uji konfigurasi lalu muat ulang:



```bash  - Data RT dengan ketua RT     ```bash

git clone https://github.com/fajarnrs/SIPAKRT.git

cd SIPAKRT  - Data pengurus RT dengan periode jabatan     sudo nginx -t

cp .env.example .env  # Set DB_HOST=db

     sudo systemctl reload nginx

docker-compose up -d --build

docker-compose exec app composer install- 📤 **Export Excel**     ```

docker-compose exec app php artisan key:generate

docker-compose exec app php artisan migrate  - Export semua data KK

docker-compose exec app php artisan make:filament-user

  - Filter berdasarkan RT3. **Pastikan PHP-FPM dan permission benar**

# http://localhost:8000/admin

# phpMyAdmin: http://localhost:8080  - Filter berdasarkan status   ```bash

```

  - Format terstruktur dengan styling   sudo systemctl enable --now php8.1-fpm

## 🔧 Post-Installation

   sudo chown -R www-data:www-data storage bootstrap/cache

```bash

# Repair KK lama tanpa kepala keluarga- 🔐 **Multi-user dengan Role Management**   sudo chmod -R ug+rwx storage bootstrap/cache

php scripts/repair-missing-head-residents.php

  - Admin: Full access   ```

# Clear cache

php artisan optimize:clear  - RT: Access terbatas per RT

```

4. **Konfigurasi domain/HTTPS**

## 📚 Documentation

## 🛠️ Tech Stack   - Gunakan Let’s Encrypt (mis. `certbot --nginx`) untuk menambahkan blok `listen 443 ssl`.

- **[DEPLOYMENT.md](DEPLOYMENT.md)** - Production deployment (cPanel/VPS/Docker)

- **[CONTRIBUTING.md](CONTRIBUTING.md)** - Contributing guidelines   - Perbarui `.env` (`APP_URL`, detail database, mail) kemudian jalankan `php artisan config:cache`.

- **[GITHUB_DEPLOYMENT.md](GITHUB_DEPLOYMENT.md)** - GitHub setup guide

- **Framework**: Laravel 10.49.1

## 🐛 What's New (v1.0.1)

- **PHP**: 8.1+5. **Login ke panel**

- ✅ Fix: Auto-create kepala keluarga saat KK dibuat (HouseholdObserver)

- ✅ Auto-update status KK jika kepala meninggal/cerai (ResidentObserver)  - **Admin Panel**: Filament 2.17.58   - Buka `http(s)://domain-anda/admin`

- ✅ View page read-only untuk detail KK

- ✅ Export Excel dengan filter RT & status- **Database**: MySQL 8.0   - Gunakan akun default `admin@warga.test / admin123` dan segera ganti kata sandi.

- ✅ Repair script untuk data lama

- ✅ Docker support dengan docker-compose- **Export**: Maatwebsite/Excel 3.1.59

- ✅ CI/CD dengan GitHub Actions

- **Frontend**: Livewire, Alpine.js, Tailwind CSSReferensi konfigurasi tambahan tersedia di [dokumentasi Laravel deploy](https://laravel.com/docs/deployment).

## 📝 License



MIT License## 📦 Instalasi



## 🙏 Credits### Quick Start (Regular)



Built with ❤️ using Laravel • Filament • Maatwebsite/Excel```bash

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
