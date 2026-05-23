# Diagram Aktivitas (Activity Diagrams)

Dokumen ini merepresentasikan alur proses untuk kasus penggunaan (use case) paling signifikan dalam sistem CargoMind.

## 1. Proses Transaksi Penjualan (POS)

![Sales Transaction Flow](pos_flow.png)

Transaksi penjualan adalah proses yang paling kompleks, melibatkan validasi stok dan operasi database atomik.

```mermaid
stateDiagram-v2
    [*] --> BukaPOS: Kasir membuka halaman POS
    BukaPOS --> InputItem: Kasir memilih item & jumlah
    InputItem --> ValidasiStok: Sistem memeriksa stok item
    
    ValidasiStok --> StokKurang: Stok tidak mencukupi
    StokKurang --> InputItem: Sesuaikan jumlah
    
    ValidasiStok --> HitungTotal: Stok OK
    HitungTotal --> InputPembayaran: Input Jumlah Bayar & Diskon
    InputPembayaran --> ProsesTransaksi: Kasir klik "Proses"
    
    state ProsesTransaksi {
        [*] --> MulaiTransaksiDB
        MulaiTransaksiDB --> PerbaruiStok: Kurangi Stok Item
        PerbaruiStok --> SimpanTransaksi: Buat Catatan Transaksi
        SimpanTransaksi --> SimpanDetail: Simpan Item Baris (Denormalisasi)
        SimpanDetail --> CatatAktivitas: Buat Log Audit
        CatatAktivitas --> Commit: Finalisasi Perubahan DB
    }
    
    ProsesTransaksi --> ResponBerhasil: Transaksi Selesai
    ResponBerhasil --> TampilkanNota: Tampilkan Nota/Cetak
    TampilkanNota --> [*]
```

## 2. Alur Autentikasi dan Otorisasi
Memastikan bahwa hanya pengguna yang berwenang yang dapat mengakses bagian tertentu dari sistem berdasarkan peran yang diberikan.

```mermaid
stateDiagram-v2
    [*] --> HalamanLogin: Pengguna mengunjungi aplikasi
    HalamanLogin --> UpayaLogin: Masukkan Email & Kata Sandi
    UpayaLogin --> VerifikasiKredensial: Pemeriksaan sistem
    
    VerifikasiKredensial --> LoginGagal: Kredensial tidak valid
    LoginGagal --> HalamanLogin: Tampilkan pesan kesalahan
    
    VerifikasiKredensial --> LoginBerhasil: Kredensial valid
    LoginBerhasil --> CekPeran: Redirect ke Dashboard
    
    state CekPeran {
        [*] --> PeranTeridentifikasi
        PeranTeridentifikasi --> Karyawan: Akses POS & Inventaris
        PeranTeridentifikasi --> Manager: Akses Laporan + Izin Karyawan
        PeranTeridentifikasi --> Master: Akses Semua + Pengaturan + God Mode
    }
    
    CekPeran --> [*]
```

## 3. Master God Mode (Pengeditan Transaksi)

![Master God Mode Flow](god_mode_flow.png)

Alur khusus untuk pengguna 'Master' untuk memperbaiki kesalahan transaksi sambil menjaga integritas stok.

```mermaid
stateDiagram-v2
    [*] --> RiwayatTransaksi: Master melihat riwayat
    RiwayatTransaksi --> AktifkanGodMode: Meminta Izin Edit
    AktifkanGodMode --> VerifikasiMaster: Sistem memeriksa Peran
    
    VerifikasiMaster --> FormEdit: God Mode Aktif
    FormEdit --> UbahData: Master menyesuaikan harga/jumlah
    
    UbahData --> ProsesPembaruan: Klik Perbarui
    
    state ProsesPembaruan {
        [*] --> HitungSelisihStok: Qty Baru vs Qty Lama
        HitungSelisihStok --> PerbaruiStokItem: Tambah/Kurang Stok
        PerbaruiStokItem --> PerbaruiCatatan: Simpan Perubahan
    }
    
    ProsesPembaruan --> Berhasil: Pembaruan Selesai
    Berhasil --> RiwayatTransaksi
```
