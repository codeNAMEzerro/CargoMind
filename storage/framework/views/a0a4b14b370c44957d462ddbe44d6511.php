<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nota - <?php echo e($transaction->invoice_number); ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800;900&display=swap" rel="stylesheet">
    <style>
        :root { --fs: <?php echo e(setting('receipt_font_size', '14')); ?>px; }
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: 'Inter', monospace, sans-serif; color: #000; background: #fff; font-size: var(--fs); }

        .receipt { max-width: 420px; margin: 20px auto; padding: 24px; }

        /* Header */
        .receipt-header { text-align: center; border-bottom: 3px solid #000; padding-bottom: 16px; margin-bottom: 16px; }
        .store-name { font-size: 2em; font-weight: 900; letter-spacing: -1px; text-transform: uppercase; }
        .store-info { font-size: 0.85em; color: #333; margin-top: 4px; line-height: 1.4; }
        .invoice-num { font-size: 0.9em; font-weight: 700; margin-top: 10px; }
        .invoice-meta { font-size: 0.8em; color: #555; }

        /* Items Table */
        .items-table { width: 100%; border-collapse: collapse; margin: 16px 0; }
        .items-table th { border-bottom: 2px solid #000; padding: 8px 4px; text-align: left; font-size: 0.85em; font-weight: 700; text-transform: uppercase; }
        .items-table td { padding: 8px 4px; border-bottom: 1px dashed #999; vertical-align: top; }
        .items-table .item-name { font-weight: 700; }
        .items-table .item-rack { font-size: 0.75em; color: #666; }
        .items-table .text-right { text-align: right; }

        /* Totals */
        .totals { border-top: 2px solid #000; padding-top: 12px; margin-top: 8px; }
        .total-row { display: flex; justify-content: space-between; padding: 4px 0; font-size: 1em; }
        .total-row.grand { font-size: 1.6em; font-weight: 900; border-top: 3px solid #000; padding-top: 12px; margin-top: 8px; }
        .total-row.payment { color: #333; }
        .discount-note { font-size: 0.8em; color: #666; font-style: italic; margin-top: 4px; }

        /* Footer */
        .receipt-footer { text-align: center; margin-top: 24px; padding-top: 16px; border-top: 2px dashed #999; }
        .footer-msg { font-weight: 700; font-size: 1em; }
        .footer-small { font-size: 0.75em; color: #888; margin-top: 8px; }

        /* Print controls */
        .no-print { text-align: center; margin: 20px; }
        .no-print a, .no-print button {
            display: inline-flex; align-items: center; gap: 6px;
            padding: 12px 24px; border-radius: 8px; font-weight: 600;
            font-size: 0.95rem; border: none; cursor: pointer;
            text-decoration: none; margin: 4px;
        }
        .btn-print { background: #1a5c38; color: #fff; }
        .btn-print:hover { background: #0f3d24; }
        .btn-back { background: #e5e7eb; color: #374151; }
        .btn-back:hover { background: #d1d5db; }
        .btn-pdf { background: #dc2626; color: #fff; }
        .btn-pdf:hover { background: #b91c1c; }

        @media print {
            .no-print { display: none !important; }
            body { font-size: var(--fs); }
            .receipt { margin: 0; padding: 10px; max-width: 100%; }
        }
    </style>
</head>
<body>
    <div class="no-print">
        <a href="<?php echo e(route('dashboard')); ?>" class="btn-back">← Dashboard</a>
        <button onclick="window.print()" class="btn-print">🖨️ Cetak Nota</button>
        <a href="<?php echo e(route('transactions.pdf', $transaction->id)); ?>" class="btn-pdf">📄 Download PDF</a>
    </div>

    <div class="receipt">
        
        <div class="receipt-header">
            <div class="store-name"><?php echo e(setting('store_name', 'CargoMind')); ?></div>
            <div class="store-info">
                <?php echo e(setting('store_address', '')); ?><br>
                Telp: <?php echo e(setting('store_phone', '')); ?>

            </div>
            <div class="invoice-num"><?php echo e($transaction->invoice_number); ?></div>
            <div class="invoice-meta">
                <?php echo e($transaction->created_at->format('d/m/Y H:i')); ?> &bull;
                Kasir: <?php echo e($transaction->cashier->name ?? '-'); ?>

            </div>
        </div>

        
        <table class="items-table">
            <thead>
                <tr>
                    <th>Barang</th>
                    <th class="text-right">Qty</th>
                    <th class="text-right">Harga</th>
                    <th class="text-right">Subtotal</th>
                </tr>
            </thead>
            <tbody>
                <?php $__currentLoopData = $transaction->details; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $d): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <tr>
                    <td>
                        <div class="item-name"><?php echo e($d->item_name); ?></div>
                        <?php if($d->rack_location): ?>
                        <div class="item-rack">📍 Rak: <?php echo e($d->rack_location); ?></div>
                        <?php endif; ?>
                        <?php if($d->discount > 0): ?>
                        <div class="item-rack">Diskon: -<?php echo e(format_rupiah($d->discount)); ?></div>
                        <?php endif; ?>
                    </td>
                    <td class="text-right"><?php echo e($d->quantity); ?></td>
                    <td class="text-right"><?php echo e(format_rupiah($d->unit_price)); ?></td>
                    <td class="text-right"><strong><?php echo e(format_rupiah($d->subtotal)); ?></strong></td>
                </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </tbody>
        </table>

        
        <div class="totals">
            <div class="total-row"><span>Subtotal</span><span><?php echo e(format_rupiah($transaction->subtotal)); ?></span></div>
            <?php if($transaction->discount_amount > 0): ?>
            <div class="total-row"><span>Diskon</span><span>-<?php echo e(format_rupiah($transaction->discount_amount)); ?></span></div>
            <?php if($transaction->discount_note): ?><div class="discount-note"><?php echo e($transaction->discount_note); ?></div><?php endif; ?>
            <?php endif; ?>
            <div class="total-row grand"><span>TOTAL</span><span><?php echo e(format_rupiah($transaction->total)); ?></span></div>
            <div class="total-row payment"><span>Bayar</span><span><?php echo e(format_rupiah($transaction->payment_amount)); ?></span></div>
            <div class="total-row payment"><span>Kembali</span><span><?php echo e(format_rupiah($transaction->change_amount)); ?></span></div>
        </div>

        
        <div class="receipt-footer">
            <div class="footer-msg"><?php echo e(setting('receipt_footer', 'Terima kasih telah berbelanja di CargoMind')); ?></div>
            <div class="footer-small"><?php echo e($transaction->invoice_number); ?> &bull; <?php echo e($transaction->created_at->format('d/m/Y H:i:s')); ?></div>
        </div>
    </div>

    <script>
        // Auto-trigger print dialog
        window.onload = function() {
            setTimeout(function() { window.print(); }, 500);
        };
    </script>
</body>
</html>
<?php /**PATH D:\repo-proyekpemrog\CargoMind\resources\views/transactions/receipt.blade.php ENDPATH**/ ?>