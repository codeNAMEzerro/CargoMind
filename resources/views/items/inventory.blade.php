@extends('layouts.app')
@section('title', 'Cek Inventory')

@section('content')
<div class="card">
    <div class="card-header">
        <h2><i class="ri-archive-fill"></i> Cek Inventory</h2>
        <div class="flex gap-1">
            <a href="?filter=low_stock" class="btn btn-sm {{ request('filter') == 'low_stock' ? 'btn-danger' : 'btn-secondary' }}">
                <i class="ri-error-warning-fill"></i> Stok Menipis
            </a>
            @if(request('filter') || request('search'))
                <a href="{{ route('inventory.index') }}" class="btn btn-sm btn-secondary">Reset</a>
            @endif
        </div>
    </div>
    <div class="card-body">
        <form class="search-bar" method="GET">
            <input type="text" name="search" class="form-control" placeholder="Cari nama atau SKU..." value="{{ request('search') }}">
            @if(request('filter'))<input type="hidden" name="filter" value="{{ request('filter') }}">@endif
            <button class="btn btn-primary"><i class="ri-search-line"></i> Cari</button>
        </form>

        <div class="inventory-grid">
            @forelse($items as $item)
                <div class="inventory-card {{ auth()->user()->hasRole('master') || auth()->user()->hasRole('manager') ? 'clickable' : '' }}" 
                     @if(auth()->user()->hasRole('master') || auth()->user()->hasRole('manager'))
                     onclick="showItemDetail({{ $item->id }})"
                     @endif>
                    <div class="inventory-card-image">
                        @if($item->image)
                            <img src="{{ asset('storage/' . $item->image) }}" alt="{{ $item->name }}">
                        @else
                            <div class="image-placeholder">
                                <i class="ri-image-line"></i>
                            </div>
                        @endif
                    </div>
                    <div class="inventory-card-content">
                        <div class="inventory-card-sku">{{ $item->sku ?? '-' }}</div>
                        <h3 class="inventory-card-name">{{ $item->name }}</h3>
                        <div class="inventory-card-location">
                            <i class="ri-map-pin-2-line"></i> {{ $item->rack_display }}
                        </div>
                        <div class="inventory-card-footer">
                            <div class="inventory-card-price">{{ format_rupiah($item->price) }}</div>
                            <div class="inventory-card-stock">
                                @if($item->stock <= 10)
                                    <span class="badge badge-danger">{{ $item->stock }} Unit</span>
                                @else
                                    <span class="badge badge-success">{{ $item->stock }} Unit</span>
                                @endif
                            </div>
                        </div>
                        @if(auth()->user()->isMaster())
                            <div class="inventory-card-purchase-price" style="font-size: 0.75rem; color: var(--text-muted); margin-top: 4px;">
                                Beli: {{ format_rupiah($item->purchase_price) }}
                            </div>
                        @endif
                    </div>
                </div>
            @empty
                <div class="empty-state" style="grid-column: 1 / -1;">
                    <i class="ri-archive-line"></i>
                    <p>Barang tidak ditemukan</p>
                </div>
            @endforelse
        </div>

        @if($items->hasPages())
            <div class="pagination">{{ $items->withQueryString()->links('pagination') }}</div>
        @endif
    </div>
</div>

{{-- Detail Modal --}}
@if(auth()->user()->hasRole('master') || auth()->user()->hasRole('manager'))
<div id="item-detail-modal" class="modal-overlay">
    <div class="modal" style="max-width: 800px;">
        <div class="modal-header">
            <h3 id="modal-item-name">Detail Barang</h3>
            <button class="modal-close" onclick="closeItemDetail()">&times;</button>
        </div>
        <div class="modal-body">
            <div id="modal-loading" class="text-center" style="padding: 40px;">
                <i class="ri-loader-4-line ri-spin" style="font-size: 2rem; color: var(--primary);"></i>
                <p>Memuat data...</p>
            </div>
            <div id="modal-content" style="display: none;">
                <div class="grid-2">
                    <div>
                        <div class="form-group">
                            <label class="form-label">SKU</label>
                            <p id="modal-sku" class="text-secondary"></p>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Deskripsi</label>
                            <p id="modal-description" class="text-secondary" style="font-size: 0.9rem;"></p>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Lokasi Rak</label>
                            <p id="modal-location" class="text-secondary"></p>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Terakhir Laku</label>
                            <p id="modal-last-sold" class="text-primary" style="font-weight: 600;"></p>
                        </div>
                        
                        <div class="flex gap-2 mt-2">
                            <div class="form-group" style="flex: 1;">
                                <label class="form-label">Harga Jual</label>
                                <div id="modal-price" style="font-size: 1.25rem; font-weight: 800; color: var(--primary);"></div>
                            </div>
                            @if(auth()->user()->isMaster())
                            <div class="form-group" style="flex: 1;">
                                <label class="form-label">Harga Beli (Modal)</label>
                                <div id="modal-purchase-price" style="font-size: 1.25rem; font-weight: 800; color: #dc2626; background: #fee2e2; padding: 4px 12px; border-radius: 8px; display: inline-block;"></div>
                            </div>
                            @endif
                        </div>
                    </div>
                    <div>
                        <h4 style="margin-bottom: 12px; font-size: 0.9rem; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.5px;">Tren Penjualan (30 Hari Terakhir)</h4>
                        <div style="height: 250px;">
                            <canvas id="itemSalesChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="modal-footer">
            <button class="btn btn-secondary" onclick="closeItemDetail()">Tutup</button>
            <a id="modal-edit-link" href="#" class="btn btn-primary"><i class="ri-edit-line"></i> Edit Barang</a>
        </div>
    </div>
</div>
@endif

@endsection

@push('styles')
<style>
    .inventory-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
        gap: 20px;
        margin-top: 20px;
    }
    .inventory-card {
        background: var(--bg-card);
        border: 1px solid var(--border-light);
        border-radius: var(--radius-lg);
        overflow: hidden;
        transition: var(--transition);
        display: flex;
        flex-direction: column;
    }
    .inventory-card.clickable { cursor: pointer; }
    .inventory-card:hover {
        transform: translateY(-5px);
        box-shadow: var(--shadow-md);
        border-color: var(--primary-light);
    }
    .inventory-card-image {
        height: 180px;
        background: #f8fafc;
        display: flex;
        align-items: center;
        justify-content: center;
        overflow: hidden;
        border-bottom: 1px solid var(--border-light);
    }
    .inventory-card-image img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }
    .image-placeholder {
        color: var(--text-muted);
        font-size: 3rem;
    }
    .inventory-card-content {
        padding: 16px;
        flex: 1;
        display: flex;
        flex-direction: column;
    }
    .inventory-card-sku {
        font-size: 0.75rem;
        color: var(--text-muted);
        font-weight: 600;
        margin-bottom: 4px;
    }
    .inventory-card-name {
        font-size: 1rem;
        font-weight: 700;
        color: var(--text-primary);
        margin-bottom: 8px;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
        line-height: 1.4;
        height: 2.8em;
    }
    .inventory-card-location {
        font-size: 0.8rem;
        color: var(--text-secondary);
        margin-bottom: 16px;
        display: flex;
        align-items: center;
        gap: 4px;
    }
    .inventory-card-footer {
        margin-top: auto;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
    .inventory-card-price {
        font-size: 1.1rem;
        font-weight: 800;
        color: var(--primary);
    }
    
    @media (max-width: 768px) {
        .inventory-grid { grid-template-columns: 1fr 1fr; gap: 12px; }
        .inventory-card-image { height: 140px; }
    }
</style>
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    let salesChart = null;

    function showItemDetail(itemId) {
        const modal = document.getElementById('item-detail-modal');
        const loading = document.getElementById('modal-loading');
        const content = document.getElementById('modal-content');
        
        modal.classList.add('active');
        loading.style.display = 'block';
        content.style.display = 'none';

        fetch(`/items/${itemId}`)
            .then(response => response.json())
            .then(data => {
                if (data.error) {
                    alert(data.error);
                    closeItemDetail();
                    return;
                }

                document.getElementById('modal-item-name').innerText = data.item.name;
                document.getElementById('modal-sku').innerText = data.item.sku || '-';
                document.getElementById('modal-description').innerText = data.item.description || 'Tidak ada deskripsi';
                document.getElementById('modal-location').innerText = (data.item.rack_primary || '-') + (data.item.rack_secondary ? ' / ' + data.item.rack_secondary : '');
                document.getElementById('modal-last-sold').innerText = data.last_sold;
                document.getElementById('modal-price').innerText = data.formatted_price;
                
                if (data.is_master) {
                    const purchasePriceEl = document.getElementById('modal-purchase-price');
                    if (purchasePriceEl) purchasePriceEl.innerText = data.formatted_purchase_price;
                }

                document.getElementById('modal-edit-link').href = `/items/${data.item.id}/edit`;

                // Update Chart
                renderSalesChart(data.sales_chart);

                loading.style.display = 'none';
                content.style.display = 'block';
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Gagal mengambil data detail barang.');
                closeItemDetail();
            });
    }

    function renderSalesChart(salesData) {
        const ctx = document.getElementById('itemSalesChart').getContext('2d');
        
        // Prepare data for the last 30 days
        const labels = [];
        const dataValues = [];
        const today = new Date();
        
        for (let i = 29; i >= 0; i--) {
            const d = new Date();
            d.setDate(today.getDate() - i);
            const dateStr = d.toISOString().split('T')[0];
            
            labels.push(d.toLocaleDateString('id-ID', { day: 'numeric', month: 'short' }));
            
            const sale = salesData.find(s => s.date === dateStr);
            dataValues.push(sale ? parseInt(sale.total_qty) : 0);
        }

        if (salesChart) {
            salesChart.destroy();
        }

        salesChart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Unit Terjual',
                    data: dataValues,
                    borderColor: '#1a5c38',
                    backgroundColor: 'rgba(26, 92, 56, 0.1)',
                    borderWidth: 2,
                    tension: 0.3,
                    fill: true,
                    pointRadius: 2
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: { stepSize: 1, precision: 0 }
                    },
                    x: {
                        ticks: {
                            maxRotation: 45,
                            minRotation: 45,
                            font: { size: 10 }
                        },
                        grid: { display: false }
                    }
                }
            }
        });
    }

    function closeItemDetail() {
        document.getElementById('item-detail-modal').classList.remove('active');
    }

    // Close on click outside
    document.getElementById('item-detail-modal').addEventListener('click', function(e) {
        if (e.target === this) closeItemDetail();
    });
</script>
@endpush
