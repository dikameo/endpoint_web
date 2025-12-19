<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Order extends Model
{
    /**
     * Primary key is text/string, not auto increment
     * Supabase schema: id = text
     */
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'id',              // Required because not auto increment
        'user_id',
        'status',
        'subtotal',
        'shipping_cost',
        'total',
        'order_date',
        'shipping_address',
        'payment_method',
        'tracking_number',
        'items',
    ];

    protected $casts = [
        'user_id' => 'string',           // UUID from auth.users
        'items' => 'json',               // JSONB → json (not array!)
        'subtotal' => 'decimal:2',
        'shipping_cost' => 'decimal:2',
        'total' => 'decimal:2',
        'order_date' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
