@extends('layouts.app')
@section('title', $item->exists ? 'Edit Barang' : 'Tambah Barang')

@section('content')
<div class="card" style="max-width:700px;">
    <div class="card-header">
        <h2><i class="ri-archive-2-fill"></i> {{ $item->exists ? 'Edit Barang' : 'Tambah Barang Baru' }}</h2>
    </div>
    <div class="card-body">
        <form method="POST" action="{{ $item->exists ? route('items.update', $item) : route('items.store') }}" enctype="multipart/form-data">
            @csrf
            @if($item->exists) @method('PUT') @endif

            <div class="form-group">
                <label class="form-label">Nama Barang *</label>
                <input type="text" name="name" class="form-control" value="{{ old('name', $item->name) }}" required>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label class="form-label">SKU</label>
                    <input type="text" name="sku" class="form-control" value="{{ old('sku', $item->sku) }}" placeholder="BT-010">
                </div>
                <div class="form-group">
                    <label class="form-label">Harga Jual *</label>
                    <input type="number" name="price" class="form-control" value="{{ old('price', $item->price) }}" required min="0">
                </div>
            </div>

            @if(auth()->user()->isMaster())
            <div class="form-group">
                <label class="form-label">Harga Beli * <small>(Hanya Master yang bisa melihat & mengisi)</small></label>
                <input type="number" name="purchase_price" class="form-control" value="{{ old('purchase_price', $item->purchase_price) }}" required min="0" style="border-color: var(--accent);">
            </div>
            @else
            <input type="hidden" name="purchase_price" value="{{ $item->purchase_price ?? 0 }}">
            @endif

            <div class="form-group">
                <label class="form-label">Stok *</label>
                <input type="number" name="stock" class="form-control" value="{{ old('stock', $item->stock) }}" required min="0">
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label class="form-label">Rak Utama (Primary)</label>
                    <input type="text" name="rack_primary" class="form-control" value="{{ old('rack_primary', $item->rack_primary) }}" placeholder="A-01">
                </div>
                <div class="form-group">
                    <label class="form-label">Rak Cadangan (Secondary)</label>
                    <input type="text" name="rack_secondary" class="form-control" value="{{ old('rack_secondary', $item->rack_secondary) }}" placeholder="B-03">
                </div>
            </div>

            <div class="form-group">
                <label class="form-label">Deskripsi</label>
                <textarea name="description" class="form-control">{{ old('description', $item->description) }}</textarea>
            </div>

            <div class="form-group">
                <label class="form-label">Foto Barang (maks 5MB, otomatis dikompres)</label>
                <input type="file" name="image" class="form-control" accept="image/*">
                @if($item->image)
                <small style="color:var(--text-muted);">Sudah ada foto. Upload baru untuk mengganti.</small>
                @endif
            </div>

            <div class="flex gap-1 mt-2">
                <button type="submit" class="btn btn-primary"><i class="ri-save-fill"></i> Simpan</button>
                <a href="{{ route('items.index') }}" class="btn btn-secondary">Batal</a>
            </div>
        </form>
    </div>
</div>
@endsection
