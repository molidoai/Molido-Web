<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class PaymentTransaction extends Model
{
    protected $fillable = [
        'uuid',
        'organization_id',
        'customer_id',
        'user_id',
        'module_id',
        'order_reference',
        'amount',
        'currency',
        'provider',
        'provider_transaction_id',
        'status',
        'idempotency_key',
        'request_payload',
        'callback_payload',
        'failure_reason',
        'redirected_at',
        'verified_at',
        'paid_at',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'request_payload' => 'array',
            'callback_payload' => 'array',
            'redirected_at' => 'datetime',
            'verified_at' => 'datetime',
            'paid_at' => 'datetime',
        ];
    }

    protected static function boot()
    {
        parent::boot();
        static::creating(function ($model) {
            if (empty($model->uuid)) {
                $model->uuid = (string) Str::uuid();
            }
        });
    }

    public function organization()
    {
        return $this->belongsTo(Organization::class);
    }

    public function module()
    {
        return $this->belongsTo(Module::class);
    }

    public function isPaid(): bool
    {
        return $this->status === 'paid';
    }
}
