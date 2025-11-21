#!/bin/bash

# SIPAKRT Deployment Script - Deploy from GitHub (cPanel Low Memory Version)
# Script ini untuk server dengan resource terbatas
# AMAN: .env tidak ditimpa, database hanya di-migrate (data tetap aman)

set -e  # Exit on error

echo "=== SIPAKRT GitHub Deployment (Low Memory) ==="
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
echo "=== Step 3: Download dari GitHub (Low Memory Mode) ==="

# Hapus clone lama jika ada
if [ -d "$APP_NAME" ]; then
    echo "🗑️  Hapus clone lama..."
    rm -rf "$APP_NAME"
fi

# Try method 1: Shallow clone (faster, less memory)
echo "📥 Try Method 1: Shallow clone (lebih hemat memory)..."
if git clone --depth 1 https://github.com/fajarnrs/SIPAKRT.git "$APP_NAME"; then
    echo "✅ Shallow clone berhasil!"
else
    echo "❌ Shallow clone gagal, try Method 2..."
    
    # Try method 2: Download ZIP (no git needed)
    echo "📥 Method 2: Download via ZIP archive..."
    
    # Download latest release as ZIP
    if command -v wget &> /dev/null; then
        wget -O sipakrt-latest.zip https://github.com/fajarnrs/SIPAKRT/archive/refs/heads/main.zip
    elif command -v curl &> /dev/null; then
        curl -L -o sipakrt-latest.zip https://github.com/fajarnrs/SIPAKRT/archive/refs/heads/main.zip
    else
        echo "❌ wget dan curl tidak tersedia!"
        echo ""
        echo "SOLUSI ALTERNATIF:"
        echo "1. Download manual dari: https://github.com/fajarnrs/SIPAKRT/archive/refs/heads/main.zip"
        echo "2. Upload ke cPanel via File Manager"
        echo "3. Extract manual: unzip main.zip"
        echo "4. Rename folder: mv SIPAKRT-main $APP_NAME"
        echo "5. Continue dari Step 4 (edit script, comment line 58-76)"
        exit 1
    fi
    
    # Extract ZIP
    if command -v unzip &> /dev/null; then
        unzip -q sipakrt-latest.zip
        mv SIPAKRT-main "$APP_NAME"
        rm sipakrt-latest.zip
        echo "✅ Download via ZIP berhasil!"
    else
        echo "❌ unzip tidak tersedia!"
        echo ""
        echo "SOLUSI ALTERNATIF:"
        echo "1. Extract manual via cPanel File Manager"
        echo "2. Rename folder SIPAKRT-main menjadi $APP_NAME"
        echo "3. Hapus file sipakrt-latest.zip"
        echo "4. Run script ini lagi"
        exit 1
    fi
fi

if [ ! -d "$APP_NAME" ]; then
    echo "❌ Download gagal! Coba manual download."
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
chmod -R 775 storage bootstrap/cache 2>/dev/null || chmod -R 755 storage bootstrap/cache

echo ""
echo "=== Step 6: Install Dependencies (Low Memory) ==="

# Check jika composer ada
if [ -f "composer.phar" ]; then
    echo "📦 Running composer install (low memory mode)..."
    # Use --no-scripts to reduce memory usage
    php composer.phar install --no-dev --optimize-autoloader --no-interaction --no-scripts
    
    # Run post-install scripts manually if needed
    php composer.phar run-script post-autoload-dump --no-interaction 2>/dev/null || true
    
elif command -v composer &> /dev/null; then
    echo "📦 Running composer install (low memory mode)..."
    composer install --no-dev --optimize-autoloader --no-interaction --no-scripts
    composer run-script post-autoload-dump --no-interaction 2>/dev/null || true
else
    echo "⚠️  Composer tidak ditemukan"
    echo ""
    echo "SOLUSI:"
    echo "1. Download composer:"
    echo "   wget https://getcomposer.org/composer.phar"
    echo ""
    echo "2. Install dependencies:"
    echo "   php composer.phar install --no-dev --optimize-autoloader --no-scripts"
    echo ""
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
