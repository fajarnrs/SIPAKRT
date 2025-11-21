# 🚀 DEPLOYMENT DARI GITHUB (Tukar Guling Method)

Script ini mengganti **SEMUA kode** dengan versi terbaru dari GitHub, tapi **DATA TETAP AMAN**.

## 🔒 Yang TIDAK akan berubah:
- ✅ File `.env` (database config tetap)
- ✅ Data database (tidak dihapus, hanya migration baru dijalankan)
- ✅ File uploads di `storage/app`
- ✅ Logs di `storage/logs`

## 📋 Yang akan berubah:
- 🔄 Semua kode PHP, Blade, JS, CSS
- 🔄 Migration baru akan dijalankan (struktur table update)
- 🔄 Composer dependencies update
- 🔄 Cache di-clear dan rebuild

---

## 📝 Cara Pakai di cPanel

### Step 1: Upload Script

1. Download file `deploy-from-github-cpanel.sh`
2. Upload ke cPanel via File Manager ke folder aplikasi (contoh: `/home/username/public_html`)
3. Set permission executable:
   ```bash
   chmod +x deploy-from-github-cpanel.sh
   ```

### Step 2: Backup Database

**WAJIB!** Backup database dulu via phpMyAdmin:
- Menu: **phpMyAdmin** → Pilih database → **Export** → **Go**
- Simpan file SQL di komputer

### Step 3: Run Script

Via **Terminal** di cPanel:

```bash
cd /home/username/public_html
./deploy-from-github-cpanel.sh
```

Script akan:
1. ✅ Backup `.env` dan `storage` folder
2. ✅ Clone dari GitHub ke folder baru
3. ✅ Restore `.env` dan `storage`
4. ✅ Install composer dependencies
5. ✅ Run migration (data aman)
6. ✅ Clear cache dan optimize

### Step 4: Update Document Root

Setelah deployment selesai, update document root di cPanel:

**Opsi A: Via cPanel → Domains**
1. Buka **Domains** menu
2. Edit domain/subdomain
3. Update Document Root ke: `/home/username/sipakrt-new/public`
4. Save

**Opsi B: Via Symlink**
```bash
# Backup public_html lama
mv public_html public_html-old

# Buat symlink ke aplikasi baru
ln -s ~/sipakrt-new/public ~/public_html
```

### Step 5: Test Aplikasi

1. Buka website di browser
2. Login dengan:
   - **No. KK** (16 digit) - untuk warga
   - **Email** - untuk admin
3. Test semua fitur:
   - ✅ Login No. KK berhasil
   - ✅ Create KK baru → user auto-created
   - ✅ Tunjuk warga jadi Ketua RT → role auto-update
   - ✅ Validasi 16 digit untuk No. KK dan NIK
   - ✅ Search by nama kepala KK

### Step 6: Cleanup (Jika Berhasil)

```bash
# Hapus folder lama
rm -rf public_html-old

# Hapus backup jika tidak perlu
rm -rf backup-*
```

---

## 🖥️ Cara Pakai di VPS/Dedicated Server

### Step 1: Upload Script

```bash
cd /var/www/html/sipakrt
wget https://raw.githubusercontent.com/fajarnrs/SIPAKRT/main/deploy-from-github.sh
chmod +x deploy-from-github.sh
```

### Step 2: Backup Database

```bash
mysqldump -u username -p database_name > backup-$(date +%Y%m%d-%H%M%S).sql
```

### Step 3: Run Script

```bash
./deploy-from-github.sh
```

### Step 4: Update Nginx/Apache Config

**Nginx:**
```nginx
server {
    root /var/www/html/sipakrt-new/public;
    # ... config lainnya
}
```

```bash
sudo nginx -t
sudo systemctl reload nginx
```

**Apache:**
```apache
DocumentRoot "/var/www/html/sipakrt-new/public"
```

```bash
sudo systemctl reload apache2
```

### Step 5: Set Permission (VPS only)

```bash
cd /var/www/html/sipakrt-new
sudo chown -R www-data:www-data storage bootstrap/cache
sudo chmod -R 775 storage bootstrap/cache
```

---

## 🔄 Rollback (Jika Ada Masalah)

Jika deployment gagal, rollback mudah karena data tidak hilang:

### Rollback Aplikasi:

```bash
# Restore folder lama
mv public_html-old public_html

# Atau update document root kembali ke folder lama
```

### Rollback Database (jika perlu):

Via phpMyAdmin:
1. Drop database (atau truncate semua table)
2. Import file backup SQL yang sudah di-backup

Via Terminal:
```bash
mysql -u username -p database_name < backup-20251121-103000.sql
```

**CATATAN:** Rollback database **HANYA jika ada masalah serius**, karena data tetap utuh sebenarnya.

---

## 📦 Fitur Baru yang Akan Aktif

Setelah deployment dari GitHub, fitur-fitur ini akan aktif:

### 1. Login dengan No. KK
- User bisa login pakai **No. KK (16 digit)** atau email
- Default password untuk kepala KK: `password123`

### 2. Auto-Create User
- Saat buat KK baru → user auto-created untuk kepala KK
- No perlu manual create user lagi

### 3. Auto-Update Role
- Warga ditunjuk jadi Ketua RT → role auto jadi `rt`
- Masa jabatan selesai → role balik jadi `warga`

### 4. Validasi Ketat
- No. KK harus 16 digit angka
- NIK harus 16 digit angka
- Tidak bisa input huruf atau karakter lain

### 5. Search Enhancement
- Bisa search KK by nama kepala KK
- Lebih mudah cari data

### 6. Cascade Delete
- Hapus KK → auto hapus semua warga dan user terkait
- Data konsisten, tidak ada orphan records

### 7. New Commands

**Create users untuk KK existing:**
```bash
php artisan households:create-users
```
Gunakan jika ada KK lama yang belum punya user.

**Delete all data (development only):**
```bash
php artisan data:delete-all --force
```
⚠️ JANGAN run di production!

---

## 🛡️ Keamanan & Best Practices

### ✅ DO:
- Backup database sebelum deployment
- Test di staging/local dulu jika memungkinkan
- Monitor logs setelah deployment: `tail -f storage/logs/laravel.log`
- Verify semua fitur berjalan normal

### ❌ DON'T:
- Deploy tanpa backup database
- Run `php artisan data:delete-all` di production
- Skip testing setelah deployment
- Hapus backup sebelum yakin deployment sukses

---

## 📞 Troubleshooting

### Issue: Composer install gagal

**Solusi:**
```bash
# Download composer
wget https://getcomposer.org/composer.phar

# Install dependencies
php composer.phar install --no-dev --optimize-autoloader
```

### Issue: Permission denied

**Solusi:**
```bash
chmod -R 775 storage bootstrap/cache
```

Di VPS dengan sudo:
```bash
sudo chown -R www-data:www-data storage bootstrap/cache
```

### Issue: Migration error

**Solusi:**
```bash
# Check migration status
php artisan migrate:status

# Rollback last migration
php artisan migrate:rollback

# Run again
php artisan migrate --force
```

### Issue: Cache tidak clear

**Solusi:**
```bash
# Clear semua cache
php artisan cache:clear
php artisan config:clear
php artisan view:clear
php artisan route:clear

# Rebuild cache
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

### Issue: Symlink storage error

**Solusi:**
```bash
# Hapus symlink lama
rm public/storage

# Buat baru
php artisan storage:link
```

### Issue: Login gagal setelah deployment

**Solusi:**
```bash
# Generate new APP_KEY
php artisan key:generate --force

# Clear auth cache
php artisan cache:clear
```

---

## 📊 Post-Deployment Checklist

Setelah deployment, verify ini semua:

- [ ] Website bisa diakses
- [ ] Login dengan email (admin) berhasil
- [ ] Login dengan No. KK (warga) berhasil
- [ ] Bisa create KK baru
- [ ] User auto-created saat create KK
- [ ] Bisa tunjuk warga jadi Ketua RT
- [ ] Role auto-update ke `rt`
- [ ] Validasi 16 digit berfungsi
- [ ] Search KK by nama kepala berfungsi
- [ ] Upload file masih berjalan
- [ ] Data lama masih tampil semua
- [ ] Logs tidak ada error: `tail -f storage/logs/laravel.log`

---

## 🔗 Links

- **GitHub Repository:** https://github.com/fajarnrs/SIPAKRT
- **Issues:** https://github.com/fajarnrs/SIPAKRT/issues
- **Latest Release:** https://github.com/fajarnrs/SIPAKRT/releases

---

## 📝 Changelog

### Version 2.0 (Latest)
- ✨ Login dengan No. KK (16 digit)
- ✨ Auto-create user untuk kepala KK
- ✨ Auto-update role saat diangkat jadi Ketua RT
- ✨ Validasi 16 digit untuk No. KK dan NIK
- ✨ Search by nama kepala KK
- ✨ Cascade delete untuk data konsistensi
- ✨ New commands: `households:create-users`, `data:delete-all`
- 🐛 Fix duplicate user creation
- 🐛 Fix dropdown filter RT leader
- 🐛 Fix email nullable support
- 🎨 Kapitalisasi label RT dan KK
- 🎨 Simplified RT edit form

---

**🎉 Selamat Deployment! Data Anda Aman! 🎉**
