# Panduan Publish CargoMind ke cPanel

Ikuti langkah-langkah ini untuk mempublikasikan website **CargoMind** ke hosting cPanel abang.

## 1. Persiapan File
Zipp semua file project CargoMind abang, **KECUALI**:
- Folder `node_modules`
- Folder `.git`
- Folder `tests`
- File `.env` (Jangan bawa file .env lokal abang)

**PENTING**: Pastikan folder `vendor` ikut di-zip. Jika tidak ada, jalankan `composer install --optimize-autoloader --no-dev` terlebih dahulu.

## 2. Struktur Folder di cPanel
Untuk keamanan maksimal (agar file inti Laravel tidak bisa diakses publik), gunakan struktur ini:

1. Upload file zip tadi ke folder root cPanel abang (satu level di atas `public_html`).
2. Ekstrak zip tersebut ke folder baru, misalnya `/home/username/cargomind_core`.
3. Pindahkan **isi** dari folder `cargomind_core/public` ke dalam folder `public_html`.
4. Hapus folder `cargomind_core/public` yang sudah kosong (opsional).

## 3. Konfigurasi `index.php`
Edit file `public_html/index.php` agar mengarah ke folder core:

Cari baris ini:
```php
require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
```

Ubah menjadi:
```php
require __DIR__.'/../cargomind_core/vendor/autoload.php';
$app = require_once __DIR__.'/../cargomind_core/bootstrap/app.php';
```

## 4. Konfigurasi `.env` di Server
1. Masuk ke `/home/username/cargomind_core`.
2. Rename file `.env.production` menjadi `.env`.
3. Edit file `.env` tersebut:
   - `APP_URL`: Ubah ke domain asli (misal: `https://cargomind.com`).
   - `APP_DEBUG`: Pastikan tetap `false`.
   - **Database**: Jika pakai MySQL, masukkan detail database yang abang buat di cPanel (MySQL Databases).

## 5. Setting Folder Permissions
Pastikan folder berikut bisa ditulisi oleh server (Permission 775 atau 755):
- `/home/username/cargomind_core/storage`
- `/home/username/cargomind_core/bootstrap/cache`

## 6. Jalankan Migrasi (Opsional)
Jika abang punya akses **Terminal** di cPanel, jalankan:
```bash
cd ~/cargomind_core
php artisan migrate --force
```

Jika tidak ada akses terminal, abang bisa import database manual via **phpMyAdmin**.

## 7. Pointing Symlink Storage
Agar gambar/file yang diupload muncul, jalankan command ini di Terminal cPanel:
```bash
ln -s /home/username/cargomind_core/storage/app/public /home/username/public_html/storage
```
*(Ganti `username` dengan username cPanel abang)*

---
**Tips Tambahan**:
- Gunakan **Select PHP Version** di cPanel untuk memastikan abang pakai PHP 8.2+.
- Aktifkan ekstensi PHP yang dibutuhkan (biasanya `bcmath`, `ctype`, `fileinfo`, `json`, `mbstring`, `openssl`, `pdo`, `tokenizer`, `xml`).
