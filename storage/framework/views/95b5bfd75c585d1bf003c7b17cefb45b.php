<?php $__env->startSection('title', 'Riwayat Transaksi'); ?>

<?php $__env->startSection('content'); ?>
<div class="card">
    <div class="card-header">
        <h2><i class="ri-file-list-3-fill"></i> Riwayat Transaksi</h2>
    </div>
    <div class="card-body">
        <form class="search-bar" method="GET">
            <input type="text" name="search" class="form-control" placeholder="Cari no. invoice..." value="<?php echo e(request('search')); ?>">
            <input type="date" name="date" class="form-control" value="<?php echo e(request('date')); ?>" style="max-width:200px;">
            <button class="btn btn-primary btn-sm"><i class="ri-search-line"></i> Cari</button>
            <?php if(request()->hasAny(['search','date'])): ?>
            <a href="<?php echo e(route('transactions.index')); ?>" class="btn btn-secondary btn-sm">Reset</a>
            <?php endif; ?>
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
                    <?php $__empty_1 = true; $__currentLoopData = $transactions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $t): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <tr>
                        <td><strong><?php echo e($t->invoice_number); ?></strong></td>
                        <td><?php echo e($t->created_at->format('d/m/Y H:i')); ?></td>
                        <td><?php echo e($t->cashier->name ?? '-'); ?></td>
                        <td class="text-right"><strong><?php echo e(format_rupiah($t->total)); ?></strong></td>
                        <td><span class="badge <?php echo e($t->status === 'completed' ? 'badge-success' : 'badge-danger'); ?>"><?php echo e($t->status); ?></span></td>
                        <td class="text-center">
                            <a href="<?php echo e(route('transactions.receipt', $t->id)); ?>" class="btn btn-sm btn-secondary" title="Cetak Ulang Nota"><i class="ri-printer-fill"></i> Cetak Ulang</a>
                            <a href="<?php echo e(route('transactions.pdf', $t->id)); ?>" class="btn btn-sm btn-secondary" title="Download PDF"><i class="ri-file-pdf-2-fill"></i></a>
                        </td>
                    </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <tr><td colspan="6" class="text-center" style="padding:40px;color:var(--text-muted);">Belum ada transaksi</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        <?php if($transactions->hasPages()): ?>
        <div class="pagination"><?php echo e($transactions->withQueryString()->links('pagination')); ?></div>
        <?php endif; ?>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\repo-proyekpemrog\CargoMind\resources\views/transactions/index.blade.php ENDPATH**/ ?>