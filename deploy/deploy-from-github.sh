#!/bin/bash

# SIPAKRT Deployment Script - Deploy from GitHub
# Script ini akan mengganti semua file dengan versi terbaru dari GitHub
# AMAN: .env tidak ditimpa, database hanya di-migrate (data tetap aman)

set -e  # Exit on error

echo "=== SIPAKRT GitHub Deployment Script ==="
echo "Repository: https://github.com/fajarnrs/SIPAKRT"
echo ""

# Warna untuk output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# Deteksi direktori saat ini
CURRENT_DIR=$(pwd)
APP_NAME="sipakrt-app"
BACKUP_DIR="backup-$(date +%Y%m%d-%H%M%S)"

echo -e "${YELLOW}📍 Current Directory: $CURRENT_DIR${NC}"
echo ""

# Konfirmasi sebelum deploy
read -p "⚠️  Deploy dari GitHub ke direktori ini? Data database AMAN, .env tidak ditimpa. (yes/no): " confirm
if [ "$confirm" != "yes" ]; then
    echo "Deployment dibatalkan."
    exit 0
fi

echo ""
echo -e "${GREEN}=== Step 1: Backup File Penting ===${NC}"

# Cek apakah .env ada
if [ -f ".env" ]; then
    echo "✅ Backup .env..."
    cp .env .env.backup-$(date +%Y%m%d-%H%M%S)
    echo "   Saved to: .env.backup-$(date +%Y%m%d-%H%M%S)"
else
    echo -e "${YELLOW}⚠️  .env tidak ditemukan (mungkin fresh install)${NC}"
fi

# Backup storage folder (uploads, logs)
if [ -d "storage" ]; then
    echo "✅ Backup storage folder..."
    mkdir -p "../$BACKUP_DIR"
    cp -r storage "../$BACKUP_DIR/"
    echo "   Saved to: ../$BACKUP_DIR/storage"
fi

# Backup vendor (opsional, untuk speed up)
if [ -d "vendor" ]; then
    echo "✅ Backup vendor folder (untuk speed up composer install)..."
    mkdir -p "../$BACKUP_DIR"
    cp -r vendor "../$BACKUP_DIR/"
    echo "   Saved to: ../$BACKUP_DIR/vendor"
fi

echo ""
echo -e "${GREEN}=== Step 2: Backup Database ===${NC}"
echo "⚠️  PENTING: Backup database manual via phpMyAdmin atau gunakan command:"
echo "   mysqldump -u username -p database_name > backup-$(date +%Y%m%d-%H%M%S).sql"
echo ""
read -p "Sudah backup database? (yes/no): " db_confirm
if [ "$db_confirm" != "yes" ]; then
    echo -e "${RED}❌ Backup database dulu! Deployment dibatalkan.${NC}"
    exit 1
fi

echo ""
echo -e "${GREEN}=== Step 3: Clone dari GitHub ===${NC}"

# Pindah ke parent directory
cd ..

# Hapus clone lama jika ada
if [ -d "$APP_NAME" ]; then
    echo "🗑️  Hapus clone lama..."
    rm -rf "$APP_NAME"
fi

# Clone repository
echo "📥 Clone repository dari GitHub..."
git clone https://github.com/fajarnrs/SIPAKRT.git "$APP_NAME"

if [ ! -d "$APP_NAME" ]; then
    echo -e "${RED}❌ Clone gagal!${NC}"
    exit 1
fi

echo ""
echo -e "${GREEN}=== Step 4: Restore File Penting ===${NC}"

# Restore .env
if [ -f "$CURRENT_DIR/.env" ]; then
    echo "✅ Restore .env..."
    cp "$CURRENT_DIR/.env" "$APP_NAME/.env"
    echo "   .env restored (database config tetap sama)"
else
    echo -e "${YELLOW}⚠️  .env tidak ada, copy dari .env.example${NC}"
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

# Restore vendor jika ada (speed up)
if [ -d "$BACKUP_DIR/vendor" ]; then
    echo "✅ Restore vendor folder..."
    cp -r "$BACKUP_DIR/vendor" "$APP_NAME/"
    echo "   Vendor restored (skip composer install jika versi sama)"
fi

echo ""
echo -e "${GREEN}=== Step 5: Setup Permission ===${NC}"

cd "$APP_NAME"

# Set permission untuk storage & bootstrap/cache
echo "🔐 Set permission storage & cache..."
chmod -R 775 storage bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache 2>/dev/null || echo "   (Skip chown - run manual jika perlu)"

echo ""
echo -e "${GREEN}=== Step 6: Install Dependencies ===${NC}"

# Check jika composer ada
if command -v composer &> /dev/null; then
    echo "📦 Running composer install..."
    composer install --no-dev --optimize-autoloader --no-interaction
else
    echo -e "${YELLOW}⚠️  Composer tidak ditemukan, skip composer install${NC}"
    echo "   Run manual: php composer.phar install --no-dev"
fi

# Check jika npm ada (optional untuk production)
if command -v npm &> /dev/null; then
    echo "📦 Running npm install & build..."
    npm install
    npm run build
else
    echo -e "${YELLOW}⚠️  npm tidak ditemukan, skip npm install${NC}"
    echo "   Run manual jika perlu: npm install && npm run build"
fi

echo ""
echo -e "${GREEN}=== Step 7: Run Migration (Data AMAN) ===${NC}"

echo "🔄 Running migration..."
php artisan migrate --force

echo ""
echo -e "${GREEN}=== Step 8: Clear Cache ===${NC}"

echo "🧹 Clear cache..."
php artisan config:clear
php artisan cache:clear
php artisan view:clear
php artisan route:clear

echo ""
echo -e "${GREEN}=== Step 9: Generate Key (jika diperlukan) ===${NC}"

# Check jika APP_KEY kosong
if ! grep -q "APP_KEY=base64:" .env; then
    echo "🔑 Generate APP_KEY..."
    php artisan key:generate --force
else
    echo "✅ APP_KEY sudah ada, skip generate"
fi

echo ""
echo -e "${GREEN}=== Step 10: Optimize Application ===${NC}"

echo "⚡ Optimize..."
php artisan optimize
php artisan config:cache
php artisan route:cache
php artisan view:cache

echo ""
echo -e "${GREEN}=== Step 11: Symlink Storage ===${NC}"

echo "🔗 Create storage symlink..."
php artisan storage:link

echo ""
echo -e "${GREEN}✅ === DEPLOYMENT SELESAI === ✅${NC}"
echo ""
echo "📁 Aplikasi baru di: $(pwd)"
echo "📁 Aplikasi lama di: $CURRENT_DIR"
echo "📁 Backup di: ../$BACKUP_DIR"
echo ""
echo -e "${YELLOW}=== LANGKAH SELANJUTNYA ===${NC}"
echo ""
echo "1. TEST aplikasi baru:"
echo "   - Buka di browser"
echo "   - Login dengan No. KK atau email"
echo "   - Cek semua fitur berjalan normal"
echo ""
echo "2. JIKA BERHASIL:"
echo "   - Pindahkan aplikasi lama: mv $CURRENT_DIR ${CURRENT_DIR}-old"
echo "   - Rename aplikasi baru: mv $(pwd) $CURRENT_DIR"
echo "   - Update webserver config (jika perlu)"
echo ""
echo "3. JIKA GAGAL:"
echo "   - Restore dari backup: ../$BACKUP_DIR"
echo "   - Database tetap utuh (tidak ada data hilang)"
echo ""
echo "4. OPTIONAL - Run command untuk create users:"
echo "   php artisan households:create-users"
echo "   (Untuk create user bagi KK yang belum punya user)"
echo ""
echo -e "${GREEN}🎉 Happy deploying!${NC}"
