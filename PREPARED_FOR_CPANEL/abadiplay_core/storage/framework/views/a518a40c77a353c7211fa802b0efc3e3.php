<?php $__env->startSection('title', 'Edit Transaksi (God Mode)'); ?>

<?php $__env->startSection('content'); ?>
<div class="card" style="max-width: 900px; margin: 0 auto;">
    <div class="card-header" style="background: rgba(52, 211, 153, 0.1); border-bottom: 2px solid var(--accent);">
        <div>
            <h2><i class="ri-shield-flash-fill"></i> God Mode: Edit Transaksi</h2>
            <p style="font-size: 0.85rem; color: var(--text-secondary);">Mengubah data transaksi akan menyesuaikan stok barang secara otomatis.</p>
        </div>
        <div class="badge badge-success"><?php echo e($transaction->invoice_number); ?></div>
    </div>
    <div class="card-body">
        <form method="POST" action="<?php echo e(route('transactions.update', $transaction)); ?>">
            <?php echo csrf_field(); ?>
            <?php echo method_field('PUT'); ?>

            <div class="grid-2 mb-2">
                <div class="form-group">
                    <label class="form-label">Tanggal Transaksi</label>
                    <input type="datetime-local" name="created_at" class="form-control" value="<?php echo e($transaction->created_at->format('Y-m-d\TH:i')); ?>" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Kasir (Read-only)</label>
                    <input type="text" class="form-control" value="<?php echo e($transaction->cashier->name ?? '-'); ?>" disabled>
                </div>
            </div>

            <h3 style="margin: 20px 0 10px; font-size: 1rem; border-bottom: 1px solid var(--border-light); padding-bottom: 5px;">Rincian Barang</h3>
            
            <div class="table-wrapper">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Barang</th>
                            <th style="width: 120px;">Qty</th>
                            <th>Harga Unit</th>
                            <th>Diskon Item</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $__currentLoopData = $transaction->details; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $detail): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <tr>
                            <td>
                                <strong><?php echo e($detail->item_name); ?></strong>
                                <input type="hidden" name="items[<?php echo e($index); ?>][id]" value="<?php echo e($detail->id); ?>">
                            </td>
                            <td>
                                <input type="number" name="items[<?php echo e($index); ?>][quantity]" class="form-control" value="<?php echo e($detail->quantity); ?>" min="1" required>
                            </td>
                            <td>
                                <input type="number" name="items[<?php echo e($index); ?>][unit_price]" class="form-control" value="<?php echo e($detail->unit_price); ?>" min="0" required>
                            </td>
                            <td>
                                <input type="number" name="items[<?php echo e($index); ?>][discount]" class="form-control" value="<?php echo e($detail->discount); ?>" min="0" required>
                            </td>
                        </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </tbody>
                </table>
            </div>

            <h3 style="margin: 20px 0 10px; font-size: 1rem; border-bottom: 1px solid var(--border-light); padding-bottom: 5px;">Total & Diskon Global</h3>

            <div class="grid-2">
                <div class="form-group">
                    <label class="form-label">Diskon Transaksi (Global)</label>
                    <input type="number" name="discount_amount" class="form-control" value="<?php echo e($transaction->discount_amount); ?>" min="0" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Catatan Diskon</label>
                    <input type="text" name="discount_note" class="form-control" value="<?php echo e($transaction->discount_note); ?>" placeholder="Alasan diskon...">
                </div>
            </div>

            <div class="alert alert-info mt-2" style="background: rgba(52, 211, 153, 0.05); border: 1px dashed var(--accent);">
                <i class="ri-information-line"></i>
                <span>Subtotal dan Total akan dihitung ulang otomatis setelah disimpan.</span>
            </div>

            <div class="flex gap-1 mt-3">
                <button type="submit" class="btn btn-primary" style="background: var(--accent-dark);"><i class="ri-save-fill"></i> Simpan Perubahan</button>
                <a href="<?php echo e(route('transactions.index')); ?>" class="btn btn-secondary">Batal</a>
            </div>
        </form>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\repo-proyekpemrog\CargoMind\resources\views/transactions/edit.blade.php ENDPATH**/ ?>