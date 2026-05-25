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
                        <th>Kasir</th>
                        <th>Waktu</th>
                        <th class="text-right">Total</th>
                        <th class="text-center">Status</th>
                        <th class="text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $__empty_1 = true; $__currentLoopData = $transactions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $t): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <tr>
                        <td><strong><?php echo e($t->invoice_number); ?></strong></td>
                        <td><?php echo e($t->cashier->name ?? '-'); ?></td>
                        <td><?php echo e($t->created_at->format('d/m/Y H:i')); ?></td>
                        <td class="text-right"><strong><?php echo e(format_rupiah($t->total)); ?></strong></td>
                        <td class="text-center">
                            <span class="badge <?php echo e($t->status === 'completed' ? 'badge-success' : 'badge-danger'); ?>">
                                <?php echo e($t->status === 'completed' ? 'Selesai' : 'Batal'); ?>

                            </span>
                        </td>
                        <td class="text-center">
                            <div class="flex gap-1 justify-center">
                                <a href="<?php echo e(route('transactions.receipt', $t)); ?>" class="btn btn-sm btn-secondary" title="Lihat Nota"><i class="ri-printer-fill"></i></a>
                                
                                <?php if(auth()->user()->isMaster() && session('master_god_mode')): ?>
                                <a href="<?php echo e(route('transactions.edit', $t)); ?>" class="btn btn-sm btn-primary" style="background: var(--accent-dark);" title="Edit (God Mode)"><i class="ri-edit-fill"></i></a>
                                <form method="POST" action="<?php echo e(route('transactions.destroy', $t->id)); ?>" style="display:inline;" id="delete-tx-<?php echo e($t->id); ?>">
                                    <?php echo csrf_field(); ?>
                                    <?php echo method_field('DELETE'); ?>
                                    <button type="button" class="btn btn-sm btn-danger" onclick="confirmDelete(document.getElementById('delete-tx-<?php echo e($t->id); ?>'), 'Hapus Transaksi', 'Hapus transaksi ini? Stok akan dikembalikan otomatis.')" title="Hapus (God Mode)">
                                        <i class="ri-delete-bin-fill"></i>
                                    </button>
                                </form>
                                <?php endif; ?>
                            </div>
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

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /Users/ILHAM-sama/Documents/CargoMind/CargoMind/resources/views/transactions/index.blade.php ENDPATH**/ ?>