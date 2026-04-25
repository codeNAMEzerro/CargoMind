<?php $__env->startSection('title', 'Laporan'); ?>

<?php $__env->startSection('content'); ?>

<div class="card mb-3">
    <div class="card-body">
        <form class="search-bar" method="GET" style="margin-bottom:0;">
            <div class="form-group" style="margin-bottom:0;">
                <label class="form-label">Dari</label>
                <input type="date" name="start_date" class="form-control" value="<?php echo e($startDate); ?>">
            </div>
            <div class="form-group" style="margin-bottom:0;">
                <label class="form-label">Sampai</label>
                <input type="date" name="end_date" class="form-control" value="<?php echo e($endDate); ?>">
            </div>
            <div style="display:flex;align-items:flex-end;gap:8px;">
                <button class="btn btn-primary"><i class="ri-filter-fill"></i> Filter</button>
                <a href="<?php echo e(route('reports.export', ['start_date' => $startDate, 'end_date' => $endDate])); ?>" class="btn btn-success"><i class="ri-file-excel-2-fill"></i> Ekspor CSV</a>
            </div>
        </form>
    </div>
</div>


<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-icon green"><i class="ri-shopping-cart-2-fill"></i></div>
        <div><div class="stat-label">Total Transaksi</div><div class="stat-value"><?php echo e($totalTransactions); ?></div></div>
    </div>
    <div class="stat-card">
        <div class="stat-icon blue"><i class="ri-money-dollar-circle-fill"></i></div>
        <div><div class="stat-label">Total Pendapatan</div><div class="stat-value"><?php echo e(format_rupiah($totalRevenue)); ?></div></div>
    </div>
    <div class="stat-card">
        <div class="stat-icon orange"><i class="ri-discount-percent-fill"></i></div>
        <div><div class="stat-label">Total Diskon</div><div class="stat-value"><?php echo e(format_rupiah($totalDiscount)); ?></div></div>
    </div>
</div>

<div class="grid-2">
    
    <div class="card">
        <div class="card-header"><h2><i class="ri-file-list-3-fill"></i> Detail Transaksi</h2></div>
        <div class="table-wrapper">
            <table class="table">
                <thead><tr><th>Invoice</th><th>Tanggal</th><th>Kasir</th><th class="text-right">Total</th></tr></thead>
                <tbody>
                <?php $__empty_1 = true; $__currentLoopData = $transactions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $t): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <tr>
                    <td><strong><?php echo e($t->invoice_number); ?></strong></td>
                    <td><?php echo e($t->created_at->format('d/m/Y H:i')); ?></td>
                    <td><?php echo e($t->cashier->name ?? '-'); ?></td>
                    <td class="text-right"><strong><?php echo e(format_rupiah($t->total)); ?></strong></td>
                </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <tr><td colspan="4" class="text-center" style="padding:30px;color:var(--text-muted);">Tidak ada data</td></tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    
    <?php if(auth()->user()->isMaster()): ?>
    <div class="card">
        <div class="card-header"><h2><i class="ri-history-fill"></i> Log Aktivitas</h2></div>
        <div class="card-body" style="max-height:500px;overflow-y:auto;">
            <?php $__empty_1 = true; $__currentLoopData = $activityLogs; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $log): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
            <div style="padding:10px 0;border-bottom:1px solid var(--border-light);">
                <div style="display:flex;justify-content:space-between;">
                    <strong style="font-size:0.85rem;"><?php echo e($log->user->name ?? 'System'); ?></strong>
                    <small style="color:var(--text-muted);"><?php echo e($log->created_at->format('d/m H:i')); ?></small>
                </div>
                <p style="font-size:0.85rem;color:var(--text-secondary);margin-top:2px;"><?php echo e($log->description); ?></p>
            </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
            <p class="text-center" style="color:var(--text-muted);padding:20px;">Tidak ada log</p>
            <?php endif; ?>
        </div>
    </div>
    <?php endif; ?>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\repo-proyekpemrog\CargoMind\resources\views/reports/index.blade.php ENDPATH**/ ?>