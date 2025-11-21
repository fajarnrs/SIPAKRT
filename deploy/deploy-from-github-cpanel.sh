#!/bin/bash

# SIPAKRT Deployment Script - Deploy from GitHub (cPanel Version)
# Script ini akan mengganti semua file dengan versi terbaru dari GitHub
# AMAN: .env tidak ditimpa, database hanya di-migrate (data tetap aman)
# Compatible dengan cPanel Terminal (no sudo required)

set -e  # Exit on error

echo "=== SIPAKRT GitHub Deployment Script (cPanel) ==="
echo "Repository: https://github.com/fajarnrs/SIPAKRT"
echo ""

# Deteksi direktori saat ini
CURRENT_DIR=$(pwd)
APP_NAME="sipakrt-new"
BACKUP_DIR="backup-$(date +%Y%m%d-%H%M%S)"

echo "📍 Current Directory: $CURRENT_DIR"
echo ""

# Konfirmasi sebelum deploy
read -p "⚠️  Deploy dari GitHub ke direktori ini? Data database AMAN, .env tidak ditimpa. (yes/no): " confirm
if [ "$confirm" != "yes" ]; then
    echo "Deployment dibatalkan."
    exit 0
fi

echo ""
echo "=== Step 1: Backup File Penting ==="

# Cek apakah .env ada
if [ -f ".env" ]; then
    echo "✅ Backup .env..."
    cp .env .env.backup-$(date +%Y%m%d-%H%M%S)
    echo "   Saved to: .env.backup-$(date +%Y%m%d-%H%M%S)"
else
    echo "⚠️  .env tidak ditemukan (mungkin fresh install)"
fi

# Backup storage folder (uploads, logs)
if [ -d "storage" ]; then
    echo "✅ Backup storage folder..."
    mkdir -p "$BACKUP_DIR"
    cp -r storage "$BACKUP_DIR/"
    echo "   Saved to: $BACKUP_DIR/storage"
fi

echo ""
echo "=== Step 2: Backup Database ==="
echo "⚠️  PENTING: Backup database manual via phpMyAdmin"
echo "   Menu: phpMyAdmin > Export > Go"
echo ""
read -p "Sudah backup database? (yes/no): " db_confirm
if [ "$db_confirm" != "yes" ]; then
    echo "❌ Backup database dulu! Deployment dibatalkan."
    exit 1
fi

echo ""
echo "=== Step 3: Clone dari GitHub ==="

# Hapus clone lama jika ada
if [ -d "$APP_NAME" ]; then
    echo "🗑️  Hapus clone lama..."
    rm -rf "$APP_NAME"
fi

# Clone repository
echo "📥 Clone repository dari GitHub..."
git clone https://github.com/fajarnrs/SIPAKRT.git "$APP_NAME"

if [ ! -d "$APP_NAME" ]; then
    echo "❌ Clone gagal!"
    exit 1
fi

echo ""
echo "=== Step 4: Restore File Penting ==="

# Restore .env
if [ -f ".env" ]; then
    echo "✅ Restore .env..."
    cp .env "$APP_NAME/.env"
    echo "   .env restored (database config tetap sama)"
else
    echo "⚠️  .env tidak ada, copy dari .env.example"
    cp "$APP_NAME/.env.example" "$APP_NAME/.env"
    echo "   ⚠️  EDIT .env untuk set database config!"
fi

# Restore storage
if [ -d "$BACKUP_DIR/storage" ]; then
    echo "✅ Restore storage folder..."
    rm -rf "$APP_NAME/storage"
    cp -r "$BACKUP_DIR/storage" "$APP_NAME/"
    echo "   Storage restored (uploads & logs tetap ada)"
fi

echo ""
echo "=== Step 5: Setup Permission ==="

cd "$APP_NAME"

# Set permission untuk storage & bootstrap/cache
echo "🔐 Set permission storage & cache..."
chmod -R 775 storage bootstrap/cache

echo ""
echo "=== Step 6: Install Dependencies ==="

# Check jika composer ada
if [ -f "composer.phar" ]; then
    echo "📦 Running composer install..."
    php composer.phar install --no-dev --optimize-autoloader --no-interaction
elif command -v composer &> /dev/null; then
    echo "📦 Running composer install..."
    composer install --no-dev --optimize-autoloader --no-interaction
else
    echo "⚠️  Composer tidak ditemukan"
    echo "   Download: wget https://getcomposer.org/composer.phar"
    echo "   Install: php composer.phar install --no-dev"
    read -p "Lanjut tanpa composer install? (yes/no): " composer_skip
    if [ "$composer_skip" != "yes" ]; then
        exit 1
    fi
fi

echo ""
echo "=== Step 7: Run Migration (Data AMAN) ==="

echo "🔄 Running migration..."
php artisan migrate --force

echo ""
echo "=== Step 8: Clear Cache ==="

echo "🧹 Clear cache..."
php artisan config:clear
php artisan cache:clear
php artisan view:clear
php artisan route:clear

echo ""
echo "=== Step 9: Generate Key (jika diperlukan) ==="

# Check jika APP_KEY kosong
if ! grep -q "APP_KEY=base64:" .env; then
    echo "🔑 Generate APP_KEY..."
    php artisan key:generate --force
else
    echo "✅ APP_KEY sudah ada, skip generate"
fi

echo ""
echo "=== Step 10: Optimize Application ==="

echo "⚡ Optimize..."
php artisan optimize
php artisan config:cache
php artisan route:cache
php artisan view:cache

echo ""
echo "=== Step 11: Symlink Storage ==="

echo "🔗 Create storage symlink..."
php artisan storage:link

echo ""
echo "✅ === DEPLOYMENT SELESAI === ✅"
echo ""
echo "📁 Aplikasi baru di: $(pwd)"
echo "📁 Aplikasi lama di: $CURRENT_DIR"
echo "📁 Backup di: $CURRENT_DIR/$BACKUP_DIR"
echo ""
echo "=== LANGKAH SELANJUTNYA ==="
echo ""
echo "1. TEST aplikasi baru:"
echo "   - Update document root di cPanel ke: $(pwd)/public"
echo "   - Atau buat symlink: ln -s $(pwd)/public ~/public_html/sipakrt"
echo "   - Buka di browser dan test"
echo ""
echo "2. JIKA BERHASIL:"
echo "   - Hapus aplikasi lama setelah yakin semua OK"
echo "   - rm -rf $CURRENT_DIR-old"
echo ""
echo "3. JIKA GAGAL:"
echo "   - Restore dari backup: $CURRENT_DIR/$BACKUP_DIR"
echo "   - Database tetap utuh (tidak ada data hilang)"
echo ""
echo "4. FITUR BARU:"
echo "   ✅ Login pakai No. KK (16 digit)"
echo "   ✅ Auto-create user untuk kepala KK"
echo "   ✅ Auto-update role jadi Ketua RT"
echo "   ✅ Validasi 16 digit untuk No. KK dan NIK"
echo "   ✅ Search by nama kepala KK"
echo "   ✅ Default password: password123"
echo ""
echo "5. OPTIONAL - Create users untuk KK existing:"
echo "   php artisan households:create-users"
echo ""
echo "🎉 Happy deploying!"
