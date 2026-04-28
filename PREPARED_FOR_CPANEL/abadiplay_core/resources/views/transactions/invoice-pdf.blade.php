<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: 'DejaVu Sans', Arial, sans-serif; font-size: {{ setting('receipt_font_size', '12') }}pt; color: #000; }
        .receipt { padding: 20px; }
        .header { text-align: center; border-bottom: 3px solid #000; padding-bottom: 14px; margin-bottom: 14px; }
        .store-name { font-size: 24pt; font-weight: bold; text-transform: uppercase; }
        .store-info { font-size: 9pt; color: #333; margin-top: 4px; }
        .invoice-info { font-size: 10pt; margin-top: 8px; }
        table.items { width: 100%; border-collapse: collapse; margin: 12px 0; }
        table.items th { border-bottom: 2px solid #000; padding: 6px 4px; text-align: left; font-size: 9pt; text-transform: uppercase; }
        table.items td { padding: 6px 4px; border-bottom: 1px solid #ccc; vertical-align: top; }
        .item-rack { font-size: 8pt; color: #666; }
        .text-right { text-align: right; }
        .totals { border-top: 2px solid #000; padding-top: 10px; margin-top: 8px; }
        .total-row { display: flex; justify-content: space-between; padding: 3px 0; }
        .grand-total { font-size: 16pt; font-weight: bold; border-top: 3px solid #000; padding-top: 10px; margin-top: 6px; }
        .footer { text-align: center; margin-top: 20px; padding-top: 12px; border-top: 2px dashed #999; font-weight: bold; font-size: 10pt; }
        .footer-small { font-size: 8pt; color: #888; margin-top: 6px; }
    </style>
</head>
<body>
<div class="receipt">
    <div class="header">
        <div class="store-name">{{ setting('store_name', 'CargoMind') }}</div>
        <div class="store-info">{{ setting('store_address', '') }}<br>Telp: {{ setting('store_phone', '') }}</div>
        <div class="invoice-info"><strong>{{ $transaction->invoice_number }}</strong><br>{{ $transaction->created_at->format('d/m/Y H:i') }} | Kasir: {{ $transaction->cashier->name ?? '-' }}</div>
    </div>

    <table class="items">
        <thead><tr><th>Barang</th><th class="text-right">Qty</th><th class="text-right">Harga</th><th class="text-right">Subtotal</th></tr></thead>
        <tbody>
        @foreach($transaction->details as $d)
        <tr>
            <td>{{ $d->item_name }}@if($d->rack_location)<br><span class="item-rack">Rak: {{ $d->rack_location }}</span>@endif</td>
            <td class="text-right">{{ $d->quantity }}</td>
            <td class="text-right">{{ format_rupiah($d->unit_price) }}</td>
            <td class="text-right">{{ format_rupiah($d->subtotal) }}</td>
        </tr>
        @endforeach
        </tbody>
    </table>

    <div class="totals">
        <table style="width:100%;">
            <tr><td>Subtotal</td><td class="text-right">{{ format_rupiah($transaction->subtotal) }}</td></tr>
            @if($transaction->discount_amount > 0)
            <tr><td>Diskon</td><td class="text-right">-{{ format_rupiah($transaction->discount_amount) }}</td></tr>
            @endif
            <tr style="font-size:16pt;font-weight:bold;border-top:3px solid #000;"><td style="padding-top:10px;">TOTAL</td><td class="text-right" style="padding-top:10px;">{{ format_rupiah($transaction->total) }}</td></tr>
            <tr><td>Bayar</td><td class="text-right">{{ format_rupiah($transaction->payment_amount) }}</td></tr>
            <tr><td>Kembali</td><td class="text-right">{{ format_rupiah($transaction->change_amount) }}</td></tr>
        </table>
    </div>

    <div class="footer">
        {{ setting('receipt_footer', 'Terima kasih telah berbelanja di CargoMind') }}
        <div class="footer-small">{{ $transaction->invoice_number }}</div>
    </div>
</div>
</body>
</html>
