<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Item extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'sku',
        'description',
        'price',
        'purchase_price',
        'stock',
        'rack_primary',
        'rack_secondary',
        'image',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'purchase_price' => 'decimal:2',
            'is_active' => 'boolean',
        ];
    }

    /**
     * Get the full rack location display string.
     */
    public function getRackDisplayAttribute(): string
    {
        $display = $this->rack_primary ?? '-';
        if ($this->rack_secondary) {
            $display .= ' / ' . $this->rack_secondary;
        }
        return $display;
    }

    /**
     * Transaction details that include this item.
     */
    public function transactionDetails()
    {
        return $this->hasMany(TransactionDetail::class);
    }
}
