@extends('layouts.app')
@section('title', 'Riwayat Transaksi')

@section('content')
<div class="card">
    <div class="card-header">
        <h2><i class="ri-file-list-3-fill"></i> Riwayat Transaksi</h2>
    </div>
    <div class="card-body">
        <form class="search-bar" method="GET">
            <input type="text" name="search" class="form-control" placeholder="Cari no. invoice..." value="{{ request('search') }}">
            <input type="date" name="date" class="form-control" value="{{ request('date') }}" style="max-width:200px;">
            <button class="btn btn-primary btn-sm"><i class="ri-search-line"></i> Cari</button>
            @if(request()->hasAny(['search','date']))
            <a href="{{ route('transactions.index') }}" class="btn btn-secondary btn-sm">Reset</a>
            @endif
        </form>

        <div class="table-wrapper">
            <table class="table">
                <thead>
                    <tr>
                        <th>Invoice</th>
                        <th>Kasir</th>
                        <th>Waktu</th>
                        <th class="text-right">Total</th>
                        <th class="text-center">Status</th>
                        <th class="text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($transactions as $t)
                    <tr>
                        <td><strong>{{ $t->invoice_number }}</strong></td>
                        <td>{{ $t->cashier->name ?? '-' }}</td>
                        <td>{{ $t->created_at->format('d/m/Y H:i') }}</td>
                        <td class="text-right"><strong>{{ format_rupiah($t->total) }}</strong></td>
                        <td class="text-center">
                            <span class="badge {{ $t->status === 'completed' ? 'badge-success' : 'badge-danger' }}">
                                {{ $t->status === 'completed' ? 'Selesai' : 'Batal' }}
                            </span>
                        </td>
                        <td class="text-center">
                            <div class="flex gap-1 justify-center">
                                <a href="{{ route('transactions.receipt', $t) }}" class="btn btn-sm btn-secondary" title="Lihat Nota"><i class="ri-printer-fill"></i></a>
                                
                                @if(auth()->user()->isMaster() && session('master_god_mode'))
                                <a href="{{ route('transactions.edit', $t) }}" class="btn btn-sm btn-primary" style="background: var(--accent-dark);" title="Edit (God Mode)"><i class="ri-edit-fill"></i></a>
                                <form method="POST" action="{{ route('transactions.destroy', $t->id) }}" style="display:inline;" id="delete-tx-{{ $t->id }}">
                                    @csrf
                                    @method('DELETE')
                                    <button type="button" class="btn btn-sm btn-danger" onclick="confirmDelete(document.getElementById('delete-tx-{{ $t->id }}'), 'Hapus Transaksi', 'Hapus transaksi ini? Stok akan dikembalikan otomatis.')" title="Hapus (God Mode)">
                                        <i class="ri-delete-bin-fill"></i>
                                    </button>
                                </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="6" class="text-center" style="padding:40px;color:var(--text-muted);">Belum ada transaksi</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($transactions->hasPages())
        <div class="pagination">{{ $transactions->withQueryString()->links('pagination') }}</div>
        @endif
    </div>
</div>
@endsection
