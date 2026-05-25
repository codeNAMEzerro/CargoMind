<?php $__env->startSection('title', 'Daftar Barang'); ?>

<?php $__env->startSection('content'); ?>
<div class="card">
    <div class="card-header">
        <h2><i class="ri-archive-2-fill"></i> Daftar Barang</h2>
        <a href="<?php echo e(route('items.create')); ?>" class="btn btn-primary"><i class="ri-add-fill"></i> Tambah Barang</a>
    </div>
    <div class="card-body">
        <form class="search-bar" method="GET">
            <input type="text" name="search" class="form-control" placeholder="Cari nama, SKU, atau rak..." value="<?php echo e(request('search')); ?>">
            <button class="btn btn-primary btn-sm"><i class="ri-search-line"></i> Cari</button>
            <?php if(request('search')): ?><a href="<?php echo e(route('items.index')); ?>" class="btn btn-secondary btn-sm">Reset</a><?php endif; ?>
        </form>
        <div class="table-wrapper">
            <table class="table">
                <thead>
                    <tr><th>Nama</th><th>SKU</th><th>Lokasi Rak</th><th class="text-right">Harga Jual</th><?php if(auth()->user()->isMaster()): ?><th class="text-right" style="color:var(--accent-dark);">Harga Beli</th><?php endif; ?><th class="text-center">Stok</th><th class="text-center">Aksi</th></tr>
                </thead>
                <tbody>
                    <?php $__empty_1 = true; $__currentLoopData = $items; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <tr>
                        <td><strong><?php echo e($item->name); ?></strong></td>
                        <td><?php echo e($item->sku ?? '-'); ?></td>
                        <td><span class="badge badge-success"><?php echo e($item->rack_display); ?></span></td>
                        <td class="text-right"><?php echo e(format_rupiah($item->price)); ?></td>
                        <?php if(auth()->user()->isMaster()): ?>
                        <td class="text-right" style="color:var(--accent-dark);"><?php echo e(format_rupiah($item->purchase_price)); ?></td>
                        <?php endif; ?>
                        <td class="text-center">
                            <?php if($item->stock <= 10): ?><span class="badge badge-danger"><?php echo e($item->stock); ?></span>
                            <?php else: ?> <span class="badge badge-success"><?php echo e($item->stock); ?></span><?php endif; ?>
                        </td>
                        <td class="text-center">
                            <div class="flex gap-1 justify-center">
                                <a href="<?php echo e(route('items.edit', $item)); ?>" class="btn btn-sm btn-secondary"><i class="ri-edit-fill"></i></a>
                                <form method="POST" action="<?php echo e(route('items.destroy', $item->id)); ?>" style="display:inline;" id="delete-form-<?php echo e($item->id); ?>">
                                    <?php echo csrf_field(); ?>
                                    <?php echo method_field('DELETE'); ?>
                                    <button type="button" class="btn btn-sm btn-danger" onclick="confirmDelete(document.getElementById('delete-form-<?php echo e($item->id); ?>'), 'Hapus Barang', 'Apakah Anda yakin ingin menghapus/menonaktifkan barang ini?')">
                                        <i class="ri-delete-bin-fill"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <tr><td colspan="<?php echo e(auth()->user()->isMaster() ? 7 : 6); ?>" class="text-center" style="padding:40px;color:var(--text-muted);">Belum ada barang</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        <?php if($items->hasPages()): ?><div class="pagination"><?php echo e($items->withQueryString()->links('pagination')); ?></div><?php endif; ?>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /Users/ILHAM-sama/Documents/CargoMind/CargoMind/resources/views/items/index.blade.php ENDPATH**/ ?>