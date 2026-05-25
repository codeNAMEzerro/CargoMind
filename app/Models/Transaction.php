<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'invoice_number',
        'cashier_id',
        'subtotal',
        'discount_amount',
        'discount_note',
        'total',
        'payment_amount',
        'change_amount',
        'status', // completed, voided
    ];

    protected function casts(): array
    {
        return [
            'subtotal' => 'decimal:2',
            'discount_amount' => 'decimal:2',
            'total' => 'decimal:2',
            'payment_amount' => 'decimal:2',
            'change_amount' => 'decimal:2',
        ];
    }

    /**
     * Buat nomor invoice unik - Ben gampang di-track transaksine.
     */
    public static function generateInvoiceNumber(): string
    {
        $prefix = 'CM';
        $date = now()->format('Ymd');
        $lastTransaction = static::whereDate('created_at', today())
            ->orderBy('id', 'desc')
            ->first();

        $sequence = $lastTransaction
            ? (int) substr($lastTransaction->invoice_number, -4) + 1
            : 1;

        return $prefix . $date . str_pad($sequence, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Kasir/User sing mroses transaksi iki.
     */
    public function cashier()
    {
        return $this->belongsTo(User::class, 'cashier_id');
    }

    /**
     * Rincian transaksi (barang-barang sing dituku).
     */
    public function details()
    {
        return $this->hasMany(TransactionDetail::class);
    }
}
