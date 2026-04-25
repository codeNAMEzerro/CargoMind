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
                        <th>Tanggal</th>
                        <th>Kasir</th>
                        <th class="text-right">Total</th>
                        <th>Status</th>
                        <th class="text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($transactions as $t)
                    <tr>
                        <td><strong>{{ $t->invoice_number }}</strong></td>
                        <td>{{ $t->created_at->format('d/m/Y H:i') }}</td>
                        <td>{{ $t->cashier->name ?? '-' }}</td>
                        <td class="text-right"><strong>{{ format_rupiah($t->total) }}</strong></td>
                        <td><span class="badge {{ $t->status === 'completed' ? 'badge-success' : 'badge-danger' }}">{{ $t->status }}</span></td>
                        <td class="text-center">
                            <a href="{{ route('transactions.receipt', $t->id) }}" class="btn btn-sm btn-secondary" title="Cetak Ulang Nota"><i class="ri-printer-fill"></i> Cetak Ulang</a>
                            <a href="{{ route('transactions.pdf', $t->id) }}" class="btn btn-sm btn-secondary" title="Download PDF"><i class="ri-file-pdf-2-fill"></i></a>
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
