<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserAddress extends Model
{
    /**
     * Primary key is user_id (uuid), not 'id'
     * Supabase schema: user_id = uuid (PK, FK to auth.users)
     * No 'id' column exists in this table!
     */
    protected $table = 'user_address';
    protected $primaryKey = 'user_id';
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'user_id',     // This is the PK
        'alamat',
        'latitude',
        'longitude',
        'accuracy',
    ];

    protected $casts = [
        'user_id' => 'string',     // UUID from auth.users
        'latitude' => 'float',     // double precision
        'longitude' => 'float',    // double precision
        'accuracy' => 'string',    // text (not decimal!)
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
