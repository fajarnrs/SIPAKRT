#!/bin/bash
# Quick Fix Script for Low Memory Server

echo "=== SIPAKRT Deployment Fix (Low Memory) ==="
echo ""

# Backup dulu
echo "Step 1: Backup .env dan storage..."
cp .env .env.backup-$(date +%Y%m%d-%H%M%S)
mkdir -p backup-storage
cp -r storage backup-storage/

# Hapus folder gagal
echo "Step 2: Hapus folder sipakrt-new yang gagal..."
rm -rf sipakrt-new

# Download ZIP
echo "Step 3: Download ZIP dari GitHub..."
wget -O sipakrt-main.zip https://github.com/fajarnrs/SIPAKRT/archive/refs/heads/main.zip

if [ ! -f "sipakrt-main.zip" ]; then
    echo "❌ Download gagal! Coba manual download."
    exit 1
fi

# Extract
echo "Step 4: Extract ZIP..."
unzip -q sipakrt-main.zip

if [ ! -d "SIPAKRT-main" ]; then
    echo "❌ Extract gagal!"
    exit 1
fi

# Rename
echo "Step 5: Rename folder..."
mv SIPAKRT-main sipakrt-new

# Cleanup
rm sipakrt-main.zip

# Restore files
echo "Step 6: Restore .env dan storage..."
cp .env sipakrt-new/.env
cp -r storage sipakrt-new/

# Permission
echo "Step 7: Set permission..."
cd sipakrt-new
chmod -R 775 storage bootstrap/cache

# Composer
echo "Step 8: Install composer dependencies..."
if [ ! -f "composer.phar" ]; then
    wget https://getcomposer.org/composer.phar
fi

php composer.phar install --no-dev --optimize-autoloader --no-scripts 2>&1 | head -20

# Migration
echo "Step 9: Run migration..."
php artisan migrate --force

# Cache
echo "Step 10: Clear & rebuild cache..."
php artisan config:clear
php artisan cache:clear
php artisan view:clear
php artisan optimize
php artisan config:cache
php artisan storage:link

echo ""
echo "✅ === DEPLOYMENT SELESAI === ✅"
echo ""
echo "LANGKAH TERAKHIR:"
echo "1. Update document root di cPanel:"
echo "   $(pwd)/public"
echo ""
echo "2. Test di browser"
echo ""
echo "Folder: $(pwd)"

