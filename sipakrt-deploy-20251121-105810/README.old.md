## Pendataan Warga

Aplikasi Laravel + Filament untuk pendataan warga per RT/KK. Panel admin tersedia di `/admin` dan hanya dapat diakses oleh pengguna dengan atribut `is_admin = true`.

## Menjalankan di Nginx

1. **Instal dependensi produksi**
   ```bash
   composer install --no-dev --optimize-autoloader
   php artisan key:generate    # jika belum
   php artisan migrate --seed  # membuat admin & data demo
   ```

2. **Siapkan konfigurasi Nginx**
   - Salin berkas contoh `deploy/nginx.conf` ke `/etc/nginx/sites-available/pendataan-warga`.
   - Sesuaikan `server_name`, path `root`, dan `fastcgi_pass` dengan lingkungan server Anda.
   - Aktifkan situs: `sudo ln -s /etc/nginx/sites-available/pendataan-warga /etc/nginx/sites-enabled/`.
   - Uji konfigurasi lalu muat ulang:
     ```bash
     sudo nginx -t
     sudo systemctl reload nginx
     ```

3. **Pastikan PHP-FPM dan permission benar**
   ```bash
   sudo systemctl enable --now php8.1-fpm
   sudo chown -R www-data:www-data storage bootstrap/cache
   sudo chmod -R ug+rwx storage bootstrap/cache
   ```

4. **Konfigurasi domain/HTTPS**
   - Gunakan Let’s Encrypt (mis. `certbot --nginx`) untuk menambahkan blok `listen 443 ssl`.
   - Perbarui `.env` (`APP_URL`, detail database, mail) kemudian jalankan `php artisan config:cache`.

5. **Login ke panel**
   - Buka `http(s)://domain-anda/admin`
   - Gunakan akun default `admin@warga.test / admin123` dan segera ganti kata sandi.

Referensi konfigurasi tambahan tersedia di [dokumentasi Laravel deploy](https://laravel.com/docs/deployment).
