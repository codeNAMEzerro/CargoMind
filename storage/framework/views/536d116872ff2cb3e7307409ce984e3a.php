<?php $__env->startSection('title', 'Analisa Omset & Keuntungan'); ?>

<?php $__env->startSection('content'); ?>
<div class="flex-between mb-3">
    <div>
        <h2 style="font-weight: 800; color: var(--primary-dark);">Analisa Keuangan</h2>
        <p style="color: var(--text-secondary);">Pantau arus kas dan performa tokomu</p>
    </div>
    <div class="flex gap-1">
        <a href="?period=today" class="btn btn-sm <?php echo e($period == 'today' ? 'btn-primary' : 'btn-secondary'); ?>">Hari Ini</a>
        <a href="?period=week" class="btn btn-sm <?php echo e($period == 'week' ? 'btn-primary' : 'btn-secondary'); ?>">7 Hari</a>
        <a href="?period=month" class="btn btn-sm <?php echo e($period == 'month' ? 'btn-primary' : 'btn-secondary'); ?>">30 Hari</a>
        <a href="?period=year" class="btn btn-sm <?php echo e($period == 'year' ? 'btn-primary' : 'btn-secondary'); ?>">Tahun Ini</a>
    </div>
</div>

<div class="grid-2 mb-3">
    <div class="stat-card" style="border-left: 5px solid var(--primary);">
        <div class="stat-icon green"><i class="ri-money-dollar-circle-fill"></i></div>
        <div>
            <div class="stat-label">Total Omset (<?php echo e(ucfirst($period)); ?>)</div>
            <div class="stat-value"><?php echo e(format_rupiah($totalRevenue)); ?></div>
        </div>
    </div>
    <?php if($isMaster): ?>
    <div class="stat-card" style="border-left: 5px solid var(--accent);">
        <div class="stat-icon" style="background: var(--accent); color: white;"><i class="ri-hand-coin-fill"></i></div>
        <div>
            <div class="stat-label">Total Laba Bersih (<?php echo e(ucfirst($period)); ?>)</div>
            <div class="stat-value" style="color: var(--accent-dark);"><?php echo e(format_rupiah($totalProfit)); ?></div>
        </div>
    </div>
    <?php else: ?>
    <div class="stat-card">
        <div class="stat-icon blue"><i class="ri-line-chart-fill"></i></div>
        <div>
            <div class="stat-label">Periode Analisa</div>
            <div class="stat-value" style="font-size: 1.2rem;"><?php echo e(now()->format('d M Y')); ?></div>
        </div>
    </div>
    <?php endif; ?>
</div>

<div class="card">
    <div class="card-header">
        <h2><i class="ri-bar-chart-fill"></i> Grafik Performa Keuangan</h2>
        <div class="badge badge-success">Data Terupdate</div>
    </div>
    <div class="card-body">
        <div style="height: 450px;">
            <canvas id="detailedRevenueChart"></canvas>
        </div>
    </div>
</div>

<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    const ctx = document.getElementById('detailedRevenueChart').getContext('2d');
    const labels = <?php echo json_encode($labels, 15, 512) ?>;
    const revenues = <?php echo json_encode($revenues, 15, 512) ?>;
    const profits = <?php echo json_encode($profits, 15, 512) ?>;
    const isMaster = <?php echo json_encode($isMaster, 15, 512) ?>;

    const datasets = [
        {
            label: 'Omset (Revenue)',
            data: revenues,
            borderColor: '#1a5c38',
            backgroundColor: 'rgba(26, 92, 56, 0.1)',
            borderWidth: 4,
            tension: 0.4,
            fill: true,
            pointBackgroundColor: '#1a5c38',
            pointBorderColor: '#fff',
            pointBorderWidth: 2,
            pointRadius: 5,
            pointHoverRadius: 7
        }
    ];

    if (isMaster) {
        datasets.push({
            label: 'Laba Bersih (Profit)',
            data: profits,
            borderColor: '#34d399',
            backgroundColor: 'rgba(52, 211, 153, 0.1)',
            borderWidth: 4,
            tension: 0.4,
            fill: true,
            pointBackgroundColor: '#34d399',
            pointBorderColor: '#fff',
            pointBorderWidth: 2,
            pointRadius: 5,
            pointHoverRadius: 7
        });
    }

    new Chart(ctx, {
        type: 'line',
        data: {
            labels: labels,
            datasets: datasets
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
                        padding: 25,
                        font: { family: 'Inter', size: 13, weight: '600' }
                    }
                },
                tooltip: {
                    backgroundColor: 'rgba(255, 255, 255, 0.95)',
                    titleColor: '#1a2e23',
                    bodyColor: '#1a2e23',
                    borderColor: '#d1e7d9',
                    borderWidth: 1,
                    padding: 15,
                    displayColors: true,
                    titleFont: { size: 14, weight: '700' },
                    bodyFont: { size: 13 },
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
                    ticks: { 
                        font: { family: 'Inter', size: 11 },
                        maxRotation: 0
                    },
                    grid: { display: false }
                }
            }
        }
    });
</script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /Users/ILHAM-sama/Documents/CargoMind/CargoMind/resources/views/reports/revenue.blade.php ENDPATH**/ ?>