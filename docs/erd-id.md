# Diagram Hubungan Entitas Database (ERD)

Dokumen ini menjelaskan struktur database untuk proyek CargoMind.

![Database ERD](erd.png)

## Diagram Mermaid

```mermaid
erDiagram
    USERS ||--o{ TRANSACTIONS : "diproses oleh kasir"
    USERS ||--o{ ACTIVITY_LOGS : "melakukan"
    USERS ||--o{ SESSIONS : "memiliki"
    
    TRANSACTIONS ||--|{ TRANSACTION_DETAILS : "berisi"
    ITEMS ||--o{ TRANSACTION_DETAILS : "dijual dalam"
    
    ACTIVITY_LOGS }o--o| ITEMS : "mencatat log (polimorfik)"
    ACTIVITY_LOGS }o--o| TRANSACTIONS : "mencatat log (polimorfik)"

    USERS {
        bigint id PK
        string name
        string email UK
        string password
        enum role "master, manager, karyawan"
        timestamp email_verified_at
        string remember_token
        timestamps timestamps
    }

    ITEMS {
        bigint id PK
        string name
        string sku UK
        text description
        decimal price
        decimal purchase_price
        integer stock
        string rack_primary
        string rack_secondary
        string image
        boolean is_active
        timestamps timestamps
    }

    TRANSACTIONS {
        bigint id PK
        string invoice_number UK
        bigint cashier_id FK
        decimal subtotal
        decimal discount_amount
        string discount_note
        decimal total
        decimal payment_amount
        decimal change_amount
        enum status "completed, voided"
        timestamps timestamps
    }

    TRANSACTION_DETAILS {
        bigint id PK
        bigint transaction_id FK
        bigint item_id FK
        string item_name "Snapshot"
        string rack_location "Snapshot"
        integer quantity
        decimal unit_price
        decimal purchase_price
        decimal discount
        decimal subtotal
        timestamps timestamps
    }

    ACTIVITY_LOGS {
        bigint id PK
        bigint user_id FK
        string action
        text description
        string ip_address
        string model_type
        bigint model_id
        timestamps timestamps
    }

    SETTINGS {
        bigint id PK
        string key UK
        text value
        string label
        string type
        timestamps timestamps
    }

    SESSIONS {
        string id PK
        bigint user_id FK
        string ip_address
        text user_agent
        longtext payload
        integer last_activity
    }

    PASSWORD_RESET_TOKENS {
        string email PK
        string token
        timestamp created_at
    }
```

## Deskripsi Entitas

### USERS
Menyimpan data pengguna aplikasi dan peran mereka. Peran meliputi `master` (pemilik), `manager`, dan `karyawan`.

### ITEMS
Berisi item inventaris, termasuk harga (`price` dan `purchase_price`), level stok, dan lokasi penyimpanan fisik (`rack_primary`, `rack_secondary`).

### TRANSACTIONS
Mencatat transaksi penjualan. Setiap transaksi terhubung ke `cashier_id` (User) dan mencakup total finansial serta status.

### TRANSACTION_DETAILS
Menyimpan item baris untuk setiap transaksi. Tabel ini melakukan denormalisasi data seperti `item_name` dan `purchase_price` pada saat penjualan untuk memastikan akurasi historis.

### ACTIVITY_LOGS
Jejak audit untuk tindakan sistem. Menggunakan `model_type` dan `model_id` untuk pelacakan polimorfik terhadap perubahan pada item, transaksi, dll.

### SETTINGS
Penyimpanan kunci-nilai (key-value) untuk konfigurasi aplikasi dan metadata.

### Tabel Sistem
*   **SESSIONS**: Mengelola status sesi pengguna.
*   **PASSWORD_RESET_TOKENS**: Menangani pemulihan kata sandi.
