<?php $__env->startSection('title', $item->exists ? 'Edit Barang' : 'Tambah Barang'); ?>

<?php $__env->startSection('content'); ?>
<div class="card" style="max-width:700px;">
    <div class="card-header">
        <h2><i class="ri-archive-2-fill"></i> <?php echo e($item->exists ? 'Edit Barang' : 'Tambah Barang Baru'); ?></h2>
    </div>
    <div class="card-body">
        <form method="POST" action="<?php echo e($item->exists ? route('items.update', $item) : route('items.store')); ?>" enctype="multipart/form-data">
            <?php echo csrf_field(); ?>
            <?php if($item->exists): ?> <?php echo method_field('PUT'); ?> <?php endif; ?>

            <div class="form-group">
                <label class="form-label">Nama Barang *</label>
                <input type="text" name="name" class="form-control" value="<?php echo e(old('name', $item->name)); ?>" required>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label class="form-label">SKU</label>
                    <input type="text" name="sku" class="form-control" value="<?php echo e(old('sku', $item->sku)); ?>" placeholder="BT-010">
                </div>
                <div class="form-group">
                    <label class="form-label">Harga Jual *</label>
                    <input type="number" name="price" class="form-control" value="<?php echo e(old('price', $item->price)); ?>" required min="0">
                </div>
            </div>

            <?php if(auth()->user()->isMaster()): ?>
            <div class="form-group">
                <label class="form-label">Harga Beli * <small>(Hanya Master yang bisa melihat & mengisi)</small></label>
                <input type="number" name="purchase_price" class="form-control" value="<?php echo e(old('purchase_price', $item->purchase_price)); ?>" required min="0" style="border-color: var(--accent);">
            </div>
            <?php else: ?>
            <input type="hidden" name="purchase_price" value="<?php echo e($item->purchase_price ?? 0); ?>">
            <?php endif; ?>

            <div class="form-group">
                <label class="form-label">Stok *</label>
                <input type="number" name="stock" class="form-control" value="<?php echo e(old('stock', $item->stock)); ?>" required min="0">
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label class="form-label">Rak Utama (Primary)</label>
                    <input type="text" name="rack_primary" class="form-control" value="<?php echo e(old('rack_primary', $item->rack_primary)); ?>" placeholder="A-01">
                </div>
                <div class="form-group">
                    <label class="form-label">Rak Cadangan (Secondary)</label>
                    <input type="text" name="rack_secondary" class="form-control" value="<?php echo e(old('rack_secondary', $item->rack_secondary)); ?>" placeholder="B-03">
                </div>
            </div>

            <div class="form-group">
                <label class="form-label">Deskripsi</label>
                <textarea name="description" class="form-control"><?php echo e(old('description', $item->description)); ?></textarea>
            </div>

            <div class="form-group">
                <label class="form-label">Foto Barang (maks 5MB, otomatis dikompres)</label>
                <input type="file" name="image" class="form-control" accept="image/*">
                <?php if($item->image): ?>
                <small style="color:var(--text-muted);">Sudah ada foto. Upload baru untuk mengganti.</small>
                <?php endif; ?>
            </div>

            <div class="flex gap-1 mt-2">
                <button type="submit" class="btn btn-primary"><i class="ri-save-fill"></i> Simpan</button>
                <a href="<?php echo e(route('items.index')); ?>" class="btn btn-secondary">Batal</a>
            </div>
        </form>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /Users/ILHAM-sama/Documents/CargoMind/CargoMind/resources/views/items/form.blade.php ENDPATH**/ ?>