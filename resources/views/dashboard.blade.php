@extends('layouts.app')
@section('title', 'Dashboard')

@section('content')
{{-- Stats Grid --}}
<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-icon green"><i class="ri-shopping-cart-2-fill"></i></div>
        <div>
            <div class="stat-label">Transaksi Hari Ini</div>
            <div class="stat-value">{{ $todayTransactions }}</div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon blue"><i class="ri-money-dollar-circle-fill"></i></div>
        <div>
            <div class="stat-label">Pendapatan Hari Ini</div>
            <div class="stat-value">{{ format_rupiah($todayRevenue) }}</div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon orange"><i class="ri-archive-2-fill"></i></div>
        <div>
            <div class="stat-label">Total Barang Aktif</div>
            <div class="stat-value">{{ $totalItems }}</div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon red"><i class="ri-error-warning-fill"></i></div>
        <div>
            <div class="stat-label">Stok Menipis</div>
            <div class="stat-value">{{ $lowStockItems }}</div>
        </div>
    </div>
</div>

<div class="grid-2">
    {{-- Recent Transactions --}}
    <div class="card">
        <div class="card-header">
            <h2><i class="ri-file-list-3-fill"></i> Transaksi Terakhir</h2>
            <a href="{{ route('transactions.index') }}" class="btn btn-sm btn-secondary">Lihat Semua</a>
        </div>
        <div class="table-wrapper">
            <table class="table">
                <thead>
                    <tr>
                        <th>Invoice</th>
                        <th>Kasir</th>
                        <th class="text-right">Total</th>
                        <th>Waktu</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($recentTransactions as $t)
                    <tr>
                        <td><strong>{{ $t->invoice_number }}</strong></td>
                        <td>{{ $t->cashier->name ?? '-' }}</td>
                        <td class="text-right"><strong>{{ format_rupiah($t->total) }}</strong></td>
                        <td>{{ $t->created_at->format('H:i') }}</td>
                    </tr>
                    @empty
                    <tr><td colspan="4" class="text-center" style="padding:30px;color:var(--text-muted);">Belum ada transaksi hari ini</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Activity Logs (Master only) --}}
    @if(auth()->user()->isMaster())
    <div class="card">
        <div class="card-header">
            <h2><i class="ri-history-fill"></i> Log Aktivitas</h2>
            <a href="{{ route('reports.index') }}" class="btn btn-sm btn-secondary">Lihat Semua</a>
        </div>
        <div class="card-body" style="max-height:400px;overflow-y:auto;">
            @forelse($recentLogs as $log)
            <div style="padding:10px 0;border-bottom:1px solid var(--border-light);">
                <div style="display:flex;justify-content:space-between;">
                    <strong style="font-size:0.85rem;">{{ $log->user->name ?? 'System' }}</strong>
                    <small style="color:var(--text-muted);">{{ $log->created_at->format('d/m H:i') }}</small>
                </div>
                <p style="font-size:0.85rem;color:var(--text-secondary);margin-top:2px;">{{ $log->description }}</p>
            </div>
            @empty
            <p style="text-align:center;color:var(--text-muted);padding:20px;">Belum ada aktivitas</p>
            @endforelse
        </div>
    </div>
    @else
    <div class="card">
        <div class="card-header"><h2><i class="ri-lightbulb-fill"></i> Info</h2></div>
        <div class="card-body">
            <p style="color:var(--text-secondary);">Selamat datang di <strong>CargoMind</strong>! Gunakan menu di samping untuk mulai bekerja.</p>
            <div class="mt-2">
                <a href="{{ route('pos') }}" class="btn btn-primary"><i class="ri-shopping-cart-2-fill"></i> Buka Kasir</a>
            </div>
        </div>
    </div>
    @endif
</div>
@endsection
