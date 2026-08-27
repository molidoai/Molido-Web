<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Inventory extends Model
{
    protected $fillable = [
        'organization_id',
        'product_id',
        'warehouse',
        'quantity',
        'reserved',
        'reorder_level',
    ];

    protected function casts(): array
    {
        return [
            'quantity' => 'decimal:3',
            'reserved' => 'decimal:3',
            'reorder_level' => 'decimal:3',
        ];
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function getAvailableAttribute(): float
    {
        return (float) $this->quantity - (float) $this->reserved;
    }
}
