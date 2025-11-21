#!/bin/bash

echo "=== SIPAKRT Deployment Package Creator ==="
echo ""

# Create deployment directory
DEPLOY_DIR="sipakrt-deploy-$(date +%Y%m%d-%H%M%S)"
mkdir -p $DEPLOY_DIR

echo "1. Copying application files..."
rsync -av --exclude='vendor' \
          --exclude='node_modules' \
          --exclude='.git' \
          --exclude='storage/logs/*' \
          --exclude='storage/framework/cache/*' \
          --exclude='storage/framework/sessions/*' \
          --exclude='storage/framework/views/*' \
          --exclude='.env' \
          --exclude='*.log' \
          . $DEPLOY_DIR/

echo "2. Creating migration-only package..."
mkdir -p $DEPLOY_DIR/migrations-only
cp database/migrations/*.php $DEPLOY_DIR/migrations-only/

echo "3. Creating .env.example for reference..."
cp .env.example $DEPLOY_DIR/

echo "4. Creating deployment instructions..."
cat > $DEPLOY_DIR/DEPLOYMENT-INSTRUCTIONS.txt << 'INST'
=== CARA DEPLOY KE CPANEL ===

OPSI 1: FULL DEPLOYMENT (Upload Semua File)
1. Backup dulu file .env yang ada di cPanel
2. Backup database (export via phpMyAdmin)
3. Upload semua file di folder ini ke public_html (atau subdomain folder)
4. Restore file .env yang sudah di-backup
5. Via SSH atau Terminal cPanel:
   cd /home/username/public_html
   php artisan migrate --force
   php artisan config:clear
   php artisan cache:clear
   php artisan view:clear

OPSI 2: UPDATE FILES ONLY (Rekomendasi)
1. Backup database dulu
2. Upload file-file yang berubah:
   - app/Filament/Auth/Login.php
   - app/Filament/Resources/HouseholdResource.php
   - app/Filament/Resources/HouseholdResource/Pages/ListHouseholds.php
   - app/Filament/Resources/ResidentResource.php
   - app/Filament/Resources/RtResource.php
   - app/Filament/Resources/RtResource/Pages/ListRts.php
   - app/Filament/Resources/RtOfficialResource.php
   - app/Filament/Resources/UserResource.php
   - app/Models/User.php
   - app/Observers/HouseholdObserver.php
   - app/Observers/RtOfficialObserver.php (NEW FILE)
   - app/Providers/AppServiceProvider.php
   - app/Console/Commands/CreateUsersForHouseholds.php (NEW FILE)
   - app/Console/Commands/DeleteAllData.php (NEW FILE)

3. Upload file migrations baru dari folder migrations-only/:
   - 2025_11_21_015124_add_family_card_number_to_users_table.php
   - 2025_11_21_015725_make_email_nullable_in_users_table.php

4. Via SSH atau Terminal cPanel:
   php artisan migrate --force
   php artisan config:clear
   php artisan cache:clear
   php artisan view:clear

OPSI 3: GIT PULL (Jika sudah setup Git di cPanel)
1. Via SSH:
   cd /home/username/public_html
   git pull origin main
   php artisan migrate --force
   php artisan config:clear
   php artisan cache:clear
   php artisan view:clear

FITUR BARU YANG DITAMBAHKAN:
✅ Login pakai No. KK (bukan email lagi)
✅ Auto-create user untuk kepala KK
✅ Auto-update role jadi Ketua RT saat diangkat
✅ Cascade delete: hapus KK otomatis hapus warga & user
✅ Validasi No. KK dan NIK harus 16 digit angka
✅ Search by nama kepala KK
✅ Kapitalisasi label RT dan KK
✅ Command: php artisan households:create-users
✅ Command: php artisan data:delete-all --force

DEFAULT PASSWORD:
Semua user kepala KK: password123
INST

echo "5. Creating SQL migration script..."
cat > $DEPLOY_DIR/manual-migration.sql << 'SQL'
-- Run this if you can't use php artisan migrate
-- Execute via phpMyAdmin or MySQL command line

-- Add family_card_number to users table
ALTER TABLE `users` 
ADD COLUMN `family_card_number` VARCHAR(20) NULL UNIQUE AFTER `email`;

-- Make email nullable
ALTER TABLE `users` 
MODIFY COLUMN `email` VARCHAR(255) NULL;

-- Verify changes
DESCRIBE `users`;
SQL

echo "6. Creating archive..."
tar -czf ${DEPLOY_DIR}.tar.gz $DEPLOY_DIR/
zip -r ${DEPLOY_DIR}.zip $DEPLOY_DIR/ -q

echo ""
echo "=== Deployment Package Created ==="
echo "Directory: $DEPLOY_DIR/"
echo "Archive (tar.gz): ${DEPLOY_DIR}.tar.gz"
echo "Archive (zip): ${DEPLOY_DIR}.zip"
echo ""
echo "Upload salah satu archive ke cPanel dan extract, lalu ikuti DEPLOYMENT-INSTRUCTIONS.txt"
