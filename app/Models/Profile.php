<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Profile extends Model
{
    /**
     * Standard Laravel profiles table with bigint ID
     * Foreign key: user_id references users(id)
     */
    protected $table = 'profiles';

    protected $fillable = [
        'user_id',
        'name',
        'phone',
        'role',
    ];

    /**
     * Get the user that owns the profile.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }
}

