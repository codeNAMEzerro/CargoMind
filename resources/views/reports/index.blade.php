@extends('layouts.app')
@section('title', 'Laporan')

@section('content')
{{-- Date Filter --}}
<div class="card mb-3">
    <div class="card-body">
        <form class="search-bar" method="GET" style="margin-bottom:0;">
            <div class="form-group" style="margin-bottom:0;">
                <label class="form-label">Dari</label>
                <input type="date" name="start_date" class="form-control" value="{{ $startDate }}">
            </div>
            <div class="form-group" style="margin-bottom:0;">
                <label class="form-label">Sampai</label>
                <input type="date" name="end_date" class="form-control" value="{{ $endDate }}">
            </div>
            <div style="display:flex;align-items:flex-end;gap:8px;">
                <button class="btn btn-primary"><i class="ri-filter-fill"></i> Filter</button>
                <a href="{{ route('reports.export', ['start_date' => $startDate, 'end_date' => $endDate]) }}" class="btn btn-success"><i class="ri-file-excel-2-fill"></i> Ekspor CSV</a>
            </div>
        </form>
    </div>
</div>

{{-- Stats --}}
<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-icon green"><i class="ri-shopping-cart-2-fill"></i></div>
        <div><div class="stat-label">Total Transaksi</div><div class="stat-value">{{ $totalTransactions }}</div></div>
    </div>
    <div class="stat-card">
        <div class="stat-icon blue"><i class="ri-money-dollar-circle-fill"></i></div>
        <div><div class="stat-label">Total Pendapatan</div><div class="stat-value">{{ format_rupiah($totalRevenue) }}</div></div>
    </div>
    <div class="stat-card">
        <div class="stat-icon orange"><i class="ri-discount-percent-fill"></i></div>
        <div><div class="stat-label">Total Diskon</div><div class="stat-value">{{ format_rupiah($totalDiscount) }}</div></div>
    </div>
    @if(auth()->user()->isMaster())
    <div class="stat-card" style="border-color: var(--accent);">
        <div class="stat-icon" style="background: var(--accent); color: white;"><i class="ri-hand-coin-fill"></i></div>
        <div>
            <div class="stat-label">Total Laba Bersih</div>
            <div class="stat-value" style="color: var(--accent-dark);">{{ format_rupiah($totalProfit) }}</div>
        </div>
    </div>
    @endif
</div>

<div class="grid-2">
    {{-- Transaction List --}}
    <div class="card">
        <div class="card-header"><h2><i class="ri-file-list-3-fill"></i> Detail Transaksi</h2></div>
        <div class="table-wrapper">
            <table class="table">
                <thead><tr><th>Invoice</th><th>Tanggal</th><th>Kasir</th><th class="text-right">Total</th></tr></thead>
                <tbody>
                @forelse($transactions as $t)
                <tr>
                    <td><strong>{{ $t->invoice_number }}</strong></td>
                    <td>{{ $t->created_at->format('d/m/Y H:i') }}</td>
                    <td>{{ $t->cashier->name ?? '-' }}</td>
                    <td class="text-right"><strong>{{ format_rupiah($t->total) }}</strong></td>
                </tr>
                @empty
                <tr><td colspan="4" class="text-center" style="padding:30px;color:var(--text-muted);">Tidak ada data</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Activity Logs --}}
    @if(auth()->user()->isMaster())
    <div class="card">
        <div class="card-header"><h2><i class="ri-history-fill"></i> Log Aktivitas</h2></div>
        <div class="card-body" style="max-height:500px;overflow-y:auto;">
            @forelse($activityLogs as $log)
            <div style="padding:10px 0;border-bottom:1px solid var(--border-light);">
                <div style="display:flex;justify-content:space-between;">
                    <strong style="font-size:0.85rem;">{{ $log->user->name ?? 'System' }}</strong>
                    <small style="color:var(--text-muted);">{{ $log->created_at->format('d/m H:i') }}</small>
                </div>
                <p style="font-size:0.85rem;color:var(--text-secondary);margin-top:2px;">{{ $log->description }}</p>
            </div>
            @empty
            <p class="text-center" style="color:var(--text-muted);padding:20px;">Tidak ada log</p>
            @endforelse
        </div>
    </div>
    @endif
</div>
@endsection
