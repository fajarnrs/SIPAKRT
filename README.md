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

### Opsi 1: Instalasi Regular (Manual)

#### Prerequisites

- PHP 8.1 atau lebih tinggi
- Composer
- MySQL 8.0 atau MariaDB 10.3+
- PHP Extensions: `pdo`, `mbstring`, `openssl`, `json`, `zip`, `gd`, `xml`

#### Langkah-langkah

1. **Clone Repository**

```bash
git clone https://github.com/yourusername/data-warga.git
cd data-warga
```

2. **Install Dependencies**

```bash
composer install
```

3. **Environment Configuration**

```bash
cp .env.example .env
php artisan key:generate
```

Edit `.env` sesuaikan database:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=pendataan_warga
DB_USERNAME=root
DB_PASSWORD=your_password
```

4. **Database Setup**

```bash
# Buat database
mysql -u root -p -e "CREATE DATABASE pendataan_warga CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"

# Run migrations
php artisan migrate

# (Optional) Seed data dummy
php artisan db:seed
```

5. **Storage Link**

```bash
php artisan storage:link
```

6. **Set Permissions**

```bash
chmod -R 775 storage bootstrap/cache
```

7. **Run Application**

```bash
# Development
php artisan serve

# Akses: http://localhost:8000
# Admin: http://localhost:8000/admin
```

8. **Create Admin User**

```bash
php artisan make:filament-user
```

---

### Opsi 2: Instalasi dengan Docker 🐳

#### Prerequisites

- Docker 20.10+
- Docker Compose 2.0+

#### Langkah-langkah

1. **Clone Repository**

```bash
git clone https://github.com/yourusername/data-warga.git
cd data-warga
```

2. **Environment Configuration**

```bash
cp .env.example .env
```

Edit `.env` untuk Docker:

```env
DB_CONNECTION=mysql
DB_HOST=db
DB_PORT=3306
DB_DATABASE=pendataan_warga
DB_USERNAME=datawarga
DB_PASSWORD=secret
```

3. **Build & Start Containers**

```bash
docker-compose up -d --build
```

4. **Install Dependencies (First time only)**

```bash
docker-compose exec app composer install
docker-compose exec app php artisan key:generate
docker-compose exec app php artisan storage:link
```

5. **Run Migrations**

```bash
docker-compose exec app php artisan migrate
```

6. **Create Admin User**

```bash
docker-compose exec app php artisan make:filament-user
```

7. **Access Application**

- **App**: http://localhost:8000
- **Admin Panel**: http://localhost:8000/admin
- **phpMyAdmin**: http://localhost:8080

#### Docker Commands

```bash
# Start containers
docker-compose up -d

# Stop containers
docker-compose down

# View logs
docker-compose logs -f

# Access container shell
docker-compose exec app bash

# Run artisan commands
docker-compose exec app php artisan [command]

# Rebuild containers
docker-compose up -d --build --force-recreate
```

---

## 🔧 Post-Installation

### 1. Repair Existing Data (Jika ada data lama)

Jika Anda mengimport data lama yang tidak memiliki resident kepala keluarga:

```bash
# Regular
php scripts/repair-missing-head-residents.php

# Docker
docker-compose exec app php scripts/repair-missing-head-residents.php
```

### 2. Clear Cache

```bash
php artisan config:clear
php artisan cache:clear
php artisan view:clear
```

---

## 👥 Default User (After Seeding)

```
Email: admin@example.com
Password: password
```

**⚠️ PENTING**: Ganti password default setelah login!

---

## 📂 Struktur Project

```
data-warga/
├── app/
│   ├── Filament/          # Filament resources & pages
│   ├── Models/            # Eloquent models
│   ├── Observers/         # Model observers (auto-sync logic)
│   ├── Exports/           # Excel export classes
│   └── ...
├── database/
│   ├── migrations/        # Database migrations
│   ├── seeders/           # Database seeders
│   └── factories/         # Model factories
├── deploy/                # Deployment configs
│   ├── nginx.conf
│   └── supervisord.conf
├── scripts/               # Utility scripts
│   └── repair-missing-head-residents.php
├── docker-compose.yml     # Docker compose config
├── Dockerfile             # Docker image config
└── README.md
```

---

## 🚀 Production Deployment

Lihat [DEPLOYMENT.md](DEPLOYMENT.md) untuk panduan deployment ke:
- ✅ cPanel
- ✅ VPS (Ubuntu/Debian)
- ✅ Docker Production

---

## 🐛 Bug Fixes & Updates

### v1.0.1 (2025-11-12)

**Fixed:**
- 🐛 Kepala keluarga tidak masuk ke tabel residents saat buat KK baru
- 🔧 Tambah `HouseholdObserver` untuk auto-sync kepala keluarga
- 📝 Tambah repair script untuk fix data lama

**Added:**
- ✨ Auto-update status KK ketika kepala keluarga meninggal/cerai
- 📄 View page (read-only) untuk detail KK
- 📊 Export Excel dengan filter RT dan Status

---

## 📚 Dokumentasi API Observer

### HouseholdObserver

Auto-sync kepala keluarga ke residents table:

```php
// Trigger saat household dibuat
Household::create([...]) 
// → Auto-create resident dengan relationship 'Kepala Keluarga'

// Trigger saat household diupdate
$household->update(['head_name' => 'Nama Baru'])
// → Auto-update resident kepala keluarga
```

### ResidentObserver

Auto-update status KK:

```php
// Jika kepala keluarga meninggal
$resident->update(['status' => 'meninggal'])
// → Household status = 'non-aktif'

// Jika kepala keluarga cerai
$resident->update(['marital_status' => 'cerai'])
// → Household status = 'non-aktif'
```

---

## 🤝 Contributing

1. Fork repository
2. Create feature branch: `git checkout -b feature/AmazingFeature`
3. Commit changes: `git commit -m 'Add some AmazingFeature'`
4. Push to branch: `git push origin feature/AmazingFeature`
5. Open Pull Request

---

## 📝 License

This project is licensed under the MIT License.

---

## 📧 Support

Jika ada pertanyaan atau issue:
- 🐛 **Bug reports**: [GitHub Issues](https://github.com/yourusername/data-warga/issues)
- 💬 **Discussions**: [GitHub Discussions](https://github.com/yourusername/data-warga/discussions)

---

## 🙏 Credits

- Laravel Framework
- Filament Admin Panel
- Maatwebsite/Excel
- All contributors

---

**Made with ❤️ for community**
