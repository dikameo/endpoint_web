<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Profile extends Model
{
    /**
     * Primary key is UUID from auth.users
     * profiles.id = auth.users.id (same value, 1-to-1)
     * Supabase schema: id = uuid (PK, FK to auth.users)
     */
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'id',       // UUID from auth.users
        'email',    // Required NOT NULL UNIQUE
        'name',
        'phone',
        'role',
    ];

    protected $casts = [
        'id' => 'string',  // UUID
    ];

    /**
     * Note: No user relationship needed
     * profiles.id IS the user id (not a foreign key column)
     */
}
