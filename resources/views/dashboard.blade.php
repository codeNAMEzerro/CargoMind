@extends('layouts.app')
@section('title', 'Dashboard')

@section('content')
{{-- Stats Grid --}}
<div class="stats-grid" style="grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));">
    <a href="{{ route('transactions.index', ['date' => date('Y-m-d')]) }}" class="stat-card">
        <div class="stat-icon green"><i class="ri-shopping-cart-2-fill"></i></div>
        <div>
            <div class="stat-label">Transaksi Hari Ini</div>
            <div class="stat-value">{{ $todayTransactions }}</div>
        </div>
    </a>
    <a href="{{ auth()->user()->hasRole('master') || auth()->user()->hasRole('manager') ? route('reports.revenue', ['period' => 'today']) : '#' }}" class="stat-card">
        <div class="stat-icon blue"><i class="ri-money-dollar-circle-fill"></i></div>
        <div>
            <div class="stat-label">Omset Hari Ini</div>
            <div class="stat-value">{{ format_rupiah($todayRevenue) }}</div>
        </div>
    </a>
    @if(auth()->user()->isMaster())
    <a href="{{ route('reports.revenue', ['period' => 'today']) }}" class="stat-card" style="border-color: var(--accent);">
        <div class="stat-icon" style="background: var(--accent); color: white;"><i class="ri-hand-coin-fill"></i></div>
        <div>
            <div class="stat-label">Untung Bersih Hari Ini</div>
            <div class="stat-value" style="color: var(--accent-dark);">{{ format_rupiah($todayProfit) }}</div>
        </div>
    </a>
    @endif
    <a href="{{ route('items.index') }}" class="stat-card">
        <div class="stat-icon orange"><i class="ri-archive-2-fill"></i></div>
        <div>
            <div class="stat-label">Total Barang Aktif</div>
            <div class="stat-value">{{ $totalItems }}</div>
        </div>
    </a>
    <a href="{{ route('items.index', ['filter' => 'low_stock']) }}" class="stat-card">
        <div class="stat-icon red"><i class="ri-error-warning-fill"></i></div>
        <div>
            <div class="stat-label">Stok Menipis</div>
            <div class="stat-value">{{ $lowStockItems }}</div>
        </div>
    </a>
</div>

{{-- Charts Section (Master Only) --}}
@if(auth()->user()->isMaster())
<div class="card mb-3">
    <div class="card-header">
        <div>
            <h2><i class="ri-bar-chart-fill"></i> Grafik Omset & Laba Bersih</h2>
            <p style="font-size: 0.8rem; color: var(--text-muted); margin-top: 4px;">Pantau performa tokomu secara berkala</p>
        </div>
        <div class="flex gap-1">
            <a href="?period=daily" class="btn btn-sm {{ $period == 'daily' ? 'btn-primary' : 'btn-secondary' }}">Harian</a>
            <a href="?period=weekly" class="btn btn-sm {{ $period == 'weekly' ? 'btn-primary' : 'btn-secondary' }}">Mingguan</a>
            <a href="?period=monthly" class="btn btn-sm {{ $period == 'monthly' ? 'btn-primary' : 'btn-secondary' }}">Bulanan</a>
        </div>
    </div>
    <div class="card-body">
        <div style="height: 350px; position: relative;">
            <canvas id="revenueChart"></canvas>
        </div>
    </div>
</div>
@endif

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

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    @if(auth()->user()->isMaster())
    const ctx = document.getElementById('revenueChart').getContext('2d');
    const chartData = @json($chartData);
    
    const labels = chartData.map(item => item.date);
    const revenues = chartData.map(item => item.revenue);
    const profits = chartData.map(item => item.profit);

    new Chart(ctx, {
        type: 'line',
        data: {
            labels: labels,
            datasets: [
                {
                    label: 'Omset (Revenue)',
                    data: revenues,
                    borderColor: '#1a5c38',
                    backgroundColor: 'rgba(26, 92, 56, 0.1)',
                    borderWidth: 3,
                    tension: 0.4,
                    fill: true,
                    pointBackgroundColor: '#1a5c38',
                    pointBorderColor: '#fff',
                    pointBorderWidth: 2,
                    pointRadius: 4
                },
                {
                    label: 'Laba Bersih (Profit)',
                    data: profits,
                    borderColor: '#34d399',
                    backgroundColor: 'rgba(52, 211, 153, 0.1)',
                    borderWidth: 3,
                    tension: 0.4,
                    fill: true,
                    pointBackgroundColor: '#34d399',
                    pointBorderColor: '#fff',
                    pointBorderWidth: 2,
                    pointRadius: 4
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            interaction: {
                intersect: false,
                mode: 'index',
            },
            plugins: {
                legend: {
                    position: 'top',
                    labels: {
                        usePointStyle: true,
                        padding: 20,
                        font: { family: 'Inter', size: 12, weight: '600' }
                    }
                },
                tooltip: {
                    backgroundColor: 'rgba(255, 255, 255, 0.9)',
                    titleColor: '#1a2e23',
                    bodyColor: '#1a2e23',
                    borderColor: '#d1e7d9',
                    borderWidth: 1,
                    padding: 12,
                    displayColors: true,
                    callbacks: {
                        label: function(context) {
                            let label = context.dataset.label || '';
                            if (label) label += ': ';
                            if (context.parsed.y !== null) {
                                label += new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', maximumFractionDigits: 0 }).format(context.parsed.y);
                            }
                            return label;
                        }
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        font: { family: 'Inter', size: 11 },
                        callback: function(value) {
                            if (value >= 1000000) return 'Rp ' + (value/1000000) + 'jt';
                            if (value >= 1000) return 'Rp ' + (value/1000) + 'rb';
                            return 'Rp ' + value;
                        }
                    },
                    grid: { color: 'rgba(0,0,0,0.03)' }
                },
                x: {
                    ticks: { font: { family: 'Inter', size: 11 } },
                    grid: { display: false }
                }
            }
        }
    });
    @endif
</script>
@endpush
