# 📊 SIPAKRT - Sistem Pendataan Warga RT/KK

> Aplikasi web untuk mengelola data Kartu Keluarga (KK), Warga, dan RT menggunakan Laravel 10 + Filament Admin Panel.

[![Laravel](https://img.shields.io/badge/Laravel-10.49-red.svg)](https://laravel.com)
[![Filament](https://img.shields.io/badge/Filament-2.17-orange.svg)](https://filamentphp.com)
[![PHP](https://img.shields.io/badge/PHP-8.1+-blue.svg)](https://php.net)
[![License](https://img.shields.io/badge/License-MIT-green.svg)](LICENSE)

---

## ✨ Fitur Utama

### 📋 Manajemen Kartu Keluarga (KK)
- ✅ CRUD data KK dengan detail lengkap
- ✅ Auto-update status (aktif/non-aktif) berdasarkan kepala keluarga
- ✅ View read-only untuk detail KK
- ✅ Auto-create kepala keluarga saat KK dibuat
- ✅ Login menggunakan **No. KK (16 digit)** atau email

### 👥 Manajemen Warga
- ✅ Data lengkap dengan relasi KK
- ✅ Auto-sync kepala keluarga
- ✅ Status: Aktif, Meninggal, Pindah, dll
- ✅ Auto-create user akun untuk kepala KK

### 🏘️ Manajemen RT & Pengurus
- ✅ Data RT dengan ketua RT
- ✅ Pengurus dengan periode jabatan
- ✅ Auto-update role user saat diangkat jadi Ketua RT

### 📤 Export & Reporting
- ✅ Export Excel dengan filter RT dan status
- ✅ Format terstruktur dengan styling

### 🔐 Multi-User & Security
- ✅ **Admin**: Full access
- ✅ **RT**: Akses terbatas per RT
- ✅ **Warga**: Login dengan No. KK
- ✅ Default password: password123

---

## 🛠️ Tech Stack

| Technology | Version |
|------------|---------|
| **Laravel** | 10.49.1 |
| **PHP** | 8.1+ |
| **Filament** | 2.17.58 |
| **MySQL** | 8.0 |
| **Excel Export** | Maatwebsite/Excel 3.1.59 |

---

## 🚀 Quick Start

### 📦 Installation (Regular)

```bash
# Clone repository
git clone https://github.com/fajarnrs/SIPAKRT.git
cd SIPAKRT

# Install dependencies
composer install
cp .env.example .env
php artisan key:generate

# Database setup (edit .env first!)
php artisan migrate
php artisan make:filament-user

# Run development server
php artisan serve
# Access: http://localhost:8000/admin
```

### 🐳 Installation (Docker)

```bash
# Clone & setup
git clone https://github.com/fajarnrs/SIPAKRT.git
cd SIPAKRT
cp .env.example .env  # Edit: DB_HOST=db

# Start containers
docker-compose up -d --build
docker-compose exec app composer install
docker-compose exec app php artisan key:generate
docker-compose exec app php artisan migrate
docker-compose exec app php artisan make:filament-user

# Access:
# App: http://localhost:8000/admin
# phpMyAdmin: http://localhost:8080
```

---

## 🔧 Post-Installation

```bash
# Repair data lama (jika ada KK tanpa kepala keluarga)
php scripts/repair-missing-head-residents.php

# Clear all cache
php artisan optimize:clear
php artisan config:cache
```

---

## 🚀 Deployment

Panduan lengkap deployment tersedia di dokumentasi:

| Platform | Guide | Description |
|----------|-------|-------------|
| **cPanel** | [DEPLOYMENT-FROM-GITHUB.md](DEPLOYMENT-FROM-GITHUB.md) | Deploy via GitHub (tukar guling method) |
| **VPS/Server** | [DEPLOYMENT.md](DEPLOYMENT.md) | Nginx/Apache production setup |
| **Docker** | [docker-compose.yml](docker-compose.yml) | Container production |

### Quick Deploy Commands

**From GitHub (Production):**
```bash
# Download deployment script
wget https://raw.githubusercontent.com/fajarnrs/SIPAKRT/main/deploy-from-github-cpanel.sh
chmod +x deploy-from-github-cpanel.sh

# Run deployment (data aman, .env tidak ditimpa)
./deploy-from-github-cpanel.sh
```

**Low Memory Server:**
```bash
wget https://raw.githubusercontent.com/fajarnrs/SIPAKRT/main/deploy-from-github-cpanel-lowmem.sh
chmod +x deploy-from-github-cpanel-lowmem.sh
./deploy-from-github-cpanel-lowmem.sh
```

---

## 📚 Dokumentasi

| File | Description |
|------|-------------|
| [README.md](README.md) | Quick start guide (this file) |
| [DEPLOYMENT-FROM-GITHUB.md](DEPLOYMENT-FROM-GITHUB.md) | GitHub deployment (recommended) |
| [DEPLOYMENT.md](DEPLOYMENT.md) | Production deployment guide |
| [CONTRIBUTING.md](CONTRIBUTING.md) | Contributing guidelines |
| [QUICK-DEPLOY-GUIDE.txt](QUICK-DEPLOY-GUIDE.txt) | Quick reference card |

---

## 🎯 Default Credentials

| Role | Email/Username | Password |
|------|----------------|----------|
| **Admin** | admin@warga.test | admin123 |
| **Warga** | No. KK (16 digit) | password123 |
| **Ketua RT** | No. KK (16 digit) | password123 |

⚠️ **PENTING:** Ganti password default setelah login pertama!

---

## 🆕 What's New (v2.0)

### New Features
- ✨ **Login dengan No. KK** (16 digit) atau email
- ✨ **Auto-create user** untuk kepala KK (password: password123)
- ✨ **Auto-update role** saat diangkat jadi Ketua RT
- ✨ **Validasi ketat** 16 digit untuk No. KK dan NIK
- ✨ **Search by nama** kepala keluarga di daftar KK
- ✨ **Cascade delete** (hapus KK → auto hapus warga & user)

### New Commands
```bash
# Create users untuk KK existing
php artisan households:create-users

# Delete all data (development only!)
php artisan data:delete-all --force
```

### Improvements
- 🐛 Fix duplicate user creation
- 🐛 Fix dropdown filter RT leader selection
- 🐛 Fix email nullable support
- 🎨 Kapitalisasi label RT dan KK
- 🎨 Simplified RT edit form

---

## 🤝 Contributing

Kontribusi sangat diterima! Lihat [CONTRIBUTING.md](CONTRIBUTING.md) untuk panduan.

1. Fork repository
2. Create feature branch (git checkout -b feature/AmazingFeature)
3. Commit changes (git commit -m 'Add some AmazingFeature')
4. Push to branch (git push origin feature/AmazingFeature)
5. Open Pull Request

---

## 📝 License

MIT License - lihat [LICENSE](LICENSE) file untuk detail.

---

## 🙏 Credits

Built with ❤️ by **[fajarnrs](https://github.com/fajarnrs)**

**Tech Stack:**
- [Laravel](https://laravel.com) - The PHP Framework
- [Filament](https://filamentphp.com) - Admin Panel
- [Maatwebsite/Excel](https://laravel-excel.com) - Excel Export
- [Livewire](https://laravel-livewire.com) - Dynamic UI
- [Tailwind CSS](https://tailwindcss.com) - Styling

---

## 📞 Support

- **Issues:** [GitHub Issues](https://github.com/fajarnrs/SIPAKRT/issues)
- **Discussions:** [GitHub Discussions](https://github.com/fajarnrs/SIPAKRT/discussions)
- **Documentation:** [Wiki](https://github.com/fajarnrs/SIPAKRT/wiki)

---

<div align="center">

**⭐ Star this repo if you find it useful!**

[Report Bug](https://github.com/fajarnrs/SIPAKRT/issues) • [Request Feature](https://github.com/fajarnrs/SIPAKRT/issues) • [Documentation](https://github.com/fajarnrs/SIPAKRT/wiki)

</div>
