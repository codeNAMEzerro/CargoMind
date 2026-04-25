<?php $__env->startSection('title', 'Dashboard'); ?>

<?php $__env->startSection('content'); ?>

<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-icon green"><i class="ri-shopping-cart-2-fill"></i></div>
        <div>
            <div class="stat-label">Transaksi Hari Ini</div>
            <div class="stat-value"><?php echo e($todayTransactions); ?></div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon blue"><i class="ri-money-dollar-circle-fill"></i></div>
        <div>
            <div class="stat-label">Pendapatan Hari Ini</div>
            <div class="stat-value"><?php echo e(format_rupiah($todayRevenue)); ?></div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon orange"><i class="ri-archive-2-fill"></i></div>
        <div>
            <div class="stat-label">Total Barang Aktif</div>
            <div class="stat-value"><?php echo e($totalItems); ?></div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon red"><i class="ri-error-warning-fill"></i></div>
        <div>
            <div class="stat-label">Stok Menipis</div>
            <div class="stat-value"><?php echo e($lowStockItems); ?></div>
        </div>
    </div>
</div>

<div class="grid-2">
    
    <div class="card">
        <div class="card-header">
            <h2><i class="ri-file-list-3-fill"></i> Transaksi Terakhir</h2>
            <a href="<?php echo e(route('transactions.index')); ?>" class="btn btn-sm btn-secondary">Lihat Semua</a>
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
                    <?php $__empty_1 = true; $__currentLoopData = $recentTransactions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $t): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <tr>
                        <td><strong><?php echo e($t->invoice_number); ?></strong></td>
                        <td><?php echo e($t->cashier->name ?? '-'); ?></td>
                        <td class="text-right"><strong><?php echo e(format_rupiah($t->total)); ?></strong></td>
                        <td><?php echo e($t->created_at->format('H:i')); ?></td>
                    </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <tr><td colspan="4" class="text-center" style="padding:30px;color:var(--text-muted);">Belum ada transaksi hari ini</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    
    <?php if(auth()->user()->isMaster()): ?>
    <div class="card">
        <div class="card-header">
            <h2><i class="ri-history-fill"></i> Log Aktivitas</h2>
            <a href="<?php echo e(route('reports.index')); ?>" class="btn btn-sm btn-secondary">Lihat Semua</a>
        </div>
        <div class="card-body" style="max-height:400px;overflow-y:auto;">
            <?php $__empty_1 = true; $__currentLoopData = $recentLogs; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $log): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
            <div style="padding:10px 0;border-bottom:1px solid var(--border-light);">
                <div style="display:flex;justify-content:space-between;">
                    <strong style="font-size:0.85rem;"><?php echo e($log->user->name ?? 'System'); ?></strong>
                    <small style="color:var(--text-muted);"><?php echo e($log->created_at->format('d/m H:i')); ?></small>
                </div>
                <p style="font-size:0.85rem;color:var(--text-secondary);margin-top:2px;"><?php echo e($log->description); ?></p>
            </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
            <p style="text-align:center;color:var(--text-muted);padding:20px;">Belum ada aktivitas</p>
            <?php endif; ?>
        </div>
    </div>
    <?php else: ?>
    <div class="card">
        <div class="card-header"><h2><i class="ri-lightbulb-fill"></i> Info</h2></div>
        <div class="card-body">
            <p style="color:var(--text-secondary);">Selamat datang di <strong>CargoMind</strong>! Gunakan menu di samping untuk mulai bekerja.</p>
            <div class="mt-2">
                <a href="<?php echo e(route('pos')); ?>" class="btn btn-primary"><i class="ri-shopping-cart-2-fill"></i> Buka Kasir</a>
            </div>
        </div>
    </div>
    <?php endif; ?>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\repo-proyekpemrog\CargoMind\resources\views/dashboard.blade.php ENDPATH**/ ?>