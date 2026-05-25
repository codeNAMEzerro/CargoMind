@extends('layouts.app')
@section('title', 'Daftar Barang')

@section('content')
<div class="card">
    <div class="card-header">
        <h2><i class="ri-archive-2-fill"></i> Daftar Barang</h2>
        <a href="{{ route('items.create') }}" class="btn btn-primary"><i class="ri-add-fill"></i> Tambah Barang</a>
    </div>
    <div class="card-body">
        <form class="search-bar" method="GET">
            <input type="text" name="search" class="form-control" placeholder="Cari nama, SKU, atau rak..." value="{{ request('search') }}">
            <button class="btn btn-primary btn-sm"><i class="ri-search-line"></i> Cari</button>
            @if(request('search'))<a href="{{ route('items.index') }}" class="btn btn-secondary btn-sm">Reset</a>@endif
        </form>
        <div class="table-wrapper">
            <table class="table">
                <thead>
                    <tr><th>Nama</th><th>SKU</th><th>Lokasi Rak</th><th class="text-right">Harga Jual</th>@if(auth()->user()->isMaster())<th class="text-right" style="color:var(--accent-dark);">Harga Beli</th>@endif<th class="text-center">Stok</th><th class="text-center">Aksi</th></tr>
                </thead>
                <tbody>
                    @forelse($items as $item)
                    <tr>
                        <td><strong>{{ $item->name }}</strong></td>
                        <td>{{ $item->sku ?? '-' }}</td>
                        <td><span class="badge badge-success">{{ $item->rack_display }}</span></td>
                        <td class="text-right">{{ format_rupiah($item->price) }}</td>
                        @if(auth()->user()->isMaster())
                        <td class="text-right" style="color:var(--accent-dark);">{{ format_rupiah($item->purchase_price) }}</td>
                        @endif
                        <td class="text-center">
                            @if($item->stock <= 10)<span class="badge badge-danger">{{ $item->stock }}</span>
                            @else <span class="badge badge-success">{{ $item->stock }}</span>@endif
                        </td>
                        <td class="text-center">
                            <div class="flex gap-1 justify-center">
                                <a href="{{ route('items.edit', $item) }}" class="btn btn-sm btn-secondary"><i class="ri-edit-fill"></i></a>
                                <form method="POST" action="{{ route('items.destroy', $item->id) }}" style="display:inline;" id="delete-form-{{ $item->id }}">
                                    @csrf
                                    @method('DELETE')
                                    <button type="button" class="btn btn-sm btn-danger" onclick="confirmDelete(document.getElementById('delete-form-{{ $item->id }}'), 'Hapus Barang', 'Apakah Anda yakin ingin menghapus/menonaktifkan barang ini?')">
                                        <i class="ri-delete-bin-fill"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="{{ auth()->user()->isMaster() ? 7 : 6 }}" class="text-center" style="padding:40px;color:var(--text-muted);">Belum ada barang</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($items->hasPages())<div class="pagination">{{ $items->withQueryString()->links('pagination') }}</div>@endif
    </div>
</div>
@endsection
