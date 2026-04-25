# 📦 CargoMind

**Mind your store and warehouse** — Sistem Manajemen Toko & Gudang berbasis Laravel.

---

## ✨ Fitur Utama

- 🟢 **Dark Green Premium UI** — Tema Hijau Tua dengan desain modern
- 🔤 **Aksesibilitas** — Toggle font besar untuk pengguna lansia (70+)
- 👥 **Multi-Role** — Master, Manager, Karyawan
- 📦 **Inventori** — Sistem Rak Ganda (Primary/Secondary), Auto-compress foto
- 🛒 **POS/Kasir** — Transaksi cepat dengan diskon logging
- 🧾 **Nota Branded** — Invoice "CargoMind" dengan cetak otomatis
- 📊 **Laporan** — Dashboard grafik, Log Aktivitas, Ekspor CSV
- 🖨️ **Print** — Auto-print via `window.print()`, PDF via DomPDF

---

## 🚀 Setup

### Prerequisites
- PHP 8.2+
- Composer
- MySQL / MariaDB
- Node.js (opsional, untuk build assets)

### Installation

```bash
# 1. Install dependencies
composer install

# 2. Copy environment file
cp .env.example .env

# 3. Generate app key
php artisan key:generate

# 4. Setup database (edit .env first)
php artisan migrate --seed

# 5. Create storage symlink
php artisan storage:link

# 6. Run the development server
php artisan serve
```

### Default Login Accounts

| Role      | Email                   | Password   |
|-----------|-------------------------|------------|
| Master    | master@cargomind.com    | password   |
| Manager   | manager@cargomind.com   | password   |
| Karyawan  | karyawan@cargomind.com  | password   |

---

## 📄 Nota / Invoice

Nota mencetak otomatis setelah transaksi selesai. Pengaturan yang bisa di-customize:
- **Nama Toko** (default: CargoMind)
- **Alamat & Telepon**
- **Ukuran Font** (10-24pt, default 14pt)
- **Footer** (default: "Terima kasih telah berbelanja di CargoMind")

Semua pengaturan dapat diubah di menu **Pengaturan** (Master only).

---

## 📁 Struktur Project

```
CargoMind/
├── app/
│   ├── Helpers/helpers.php          # setting(), format_rupiah()
│   ├── Http/Controllers/           # Auth, Dashboard, Item, Transaction, Setting, Report
│   ├── Http/Middleware/CheckRole.php
│   ├── Models/                     # User, Setting, Item, Transaction, TransactionDetail, ActivityLog
│   └── Providers/
├── config/                         # app, auth, database, session, filesystems, view
├── database/
│   ├── migrations/                 # users, settings, items, transactions, activity_logs
│   └── seeders/DatabaseSeeder.php
├── public/
│   ├── css/app.css                 # Premium Dark Green theme
│   └── index.php
├── resources/views/
│   ├── layouts/app.blade.php       # Main layout with sidebar
│   ├── auth/login.blade.php
│   ├── dashboard.blade.php
│   ├── items/                      # index, form
│   ├── transactions/               # create (POS), index, receipt, invoice-pdf
│   ├── settings/index.blade.php
│   └── reports/index.blade.php
├── routes/web.php
├── .env.example
└── composer.json
```

---

## License

MIT
