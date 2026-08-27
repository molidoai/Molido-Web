<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Invoice extends Model
{
    protected $fillable = [
        'invoice_number',
        'organization_id',
        'customer_id',
        'payment_transaction_id',
        'subtotal',
        'discount',
        'tax',
        'total',
        'currency',
        'status',
        'items',
        'issued_at',
        'paid_at',
    ];

    protected function casts(): array
    {
        return [
            'subtotal' => 'decimal:2',
            'discount' => 'decimal:2',
            'tax' => 'decimal:2',
            'total' => 'decimal:2',
            'items' => 'array',
            'issued_at' => 'datetime',
            'paid_at' => 'datetime',
        ];
    }

    public function transaction()
    {
        return $this->belongsTo(PaymentTransaction::class, 'payment_transaction_id');
    }
}
