@extends('layouts.app') {{-- # Menggunakan layout utama aplikasi --}}
@section('title', $item->exists ? 'Edit Barang' : 'Tambah Barang') {{-- # Menentukan title halaman secara dinamis --}}

@section('content')
<div class="card" style="max-width:700px;">
    <div class="card-header">
        {{-- # Judul kartu dinamis: jika data barang sudah ada, tampilkan 'Edit Barang', jika baru tampilkan 'Tambah Barang Baru' --}}
        <h2><i class="ri-archive-2-fill"></i> {{ $item->exists ? 'Edit Barang' : 'Tambah Barang Baru' }}</h2>
    </div>
    <div class="card-body">
        {{-- # Form dinamis: mengarah ke route update jika edit, atau route store jika tambah data baru. enctype digunakan karena ada upload foto --}}
        <form method="POST" action="{{ $item->exists ? route('items.update', $item) : route('items.store') }}" enctype="multipart/form-data">
            @csrf {{-- # Token keamanan CSRF Laravel untuk mencegah serangan cross-site request forgery --}}
            @if($item->exists) @method('PUT') @endif {{-- # Mengubah method POST menjadi PUT jika form dalam mode Edit Data --}}

            {{-- # Input Nama Barang --}}
            <div class="form-group">
                <label class="form-label">Nama Barang *</label>
                {{-- # old() digunakan agar input tidak hilang jika validasi gagal, default-nya mengambil data dari database --}}
                <input type="text" name="name" class="form-control" value="{{ old('name', $item->name) }}" required>
            </div>

            <div class="form-row">
                {{-- # Input SKU (Stock Keeping Unit) --}}
                <div class="form-group">
                    <label class="form-label">SKU</label>
                    <input type="text" name="sku" class="form-control" value="{{ old('sku', $item->sku) }}" placeholder="BT-010">
                </div>
                {{-- # Input Harga Jual --}}
                <div class="form-group">
                    <label class="form-label">Harga Jual *</label>
                    <input type="number" name="price" class="form-control" value="{{ old('price', $item->price) }}" required min="0">
                </div>
            </div>

            {{-- # Pengecekan Hak Akses: Hanya user dengan role 'Master' yang bisa melihat dan mengubah Harga Beli --}}
            @if(auth()->user()->isMaster())
            <div class="form-group">
                <label class="form-label">Harga Beli * <small>(Hanya Master yang bisa melihat & mengisi)</small></label>
                <input type="number" name="purchase_price" class="form-control" value="{{ old('purchase_price', $item->purchase_price) }}" required min="0" style="border-color: var(--accent);">
            </div>
            @else
            {{-- # Jika bukan Master, Harga Beli disembunyikan dalam input hidden agar nilainya tidak hilang saat di-update --}}
            <input type="hidden" name="purchase_price" value="{{ $item->purchase_price ?? 0 }}">
            @endif

            {{-- # Input Jumlah Stok --}}
            <div class="form-group">
                <label class="form-label">Stok *</label>
                <input type="number" name="stock" class="form-control" value="{{ old('stock', $item->stock) }}" required min="0">
            </div>

            <div class="form-row">
                {{-- # Input Lokasi Rak Utama --}}
                <div class="form-group">
                    <label class="form-label">Rak Utama (Primary)</label>
                    <input type="text" name="rack_primary" class="form-control" value="{{ old('rack_primary', $item->rack_primary) }}" placeholder="A-01">
                </div>
                {{-- # Input Lokasi Rak Cadangan --}}
                <div class="form-group">
                    <label class="form-label">Rak Cadangan (Secondary)</label>
                    <input type="text" name="rack_secondary" class="form-control" value="{{ old('rack_secondary', $item->rack_secondary) }}" placeholder="B-03">
                </div>
            </div>

            {{-- # Input Deskripsi Produk --}}
            <div class="form-group">
                <label class="form-label">Deskripsi</label>
                <textarea name="description" class="form-control">{{ old('description', $item->description) }}</textarea>
            </div>

            {{-- # Input Unggah Foto Barang --}}
            <div class="form-group">
                <label class="form-label">Foto Barang (maks 5MB, otomatis dikompres)</label>
                <input type="file" name="image" class="form-control" accept="image/*">
                {{-- # Menampilkan teks pemberitahuan jika barang tersebut sudah memiliki foto sebelumnya --}}
                @if($item->image)
                <small style="color:var(--text-muted);">Sudah ada foto. Upload baru untuk mengganti.</small>
                @endif
            </div>

            {{-- # Tombol Aksi (Simpan dan Batal) --}}
            <div class="flex gap-1 mt-2">
                <button type="submit" class="btn btn-primary"><i class="ri-save-fill"></i> Simpan</button>
                <a href="{{ route('items.index') }}" class="btn btn-secondary">Batal</a>
            </div>
        </form>
    </div>
</div>
@endsection
