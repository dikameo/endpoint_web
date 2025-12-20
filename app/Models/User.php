<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Laravel\Sanctum\HasApiTokens;

use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable implements FilamentUser
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, HasRoles, HasApiTokens;

    /**
     * Use Supabase Auth table (auth.users)
     * Primary key is UUID from Supabase Auth
     */
    protected $table = 'auth.users';
    protected $keyType = 'string';
    public $incrementing = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'id',                    // UUID from Supabase Auth
        'email',
        'encrypted_password',    // Supabase uses this instead of 'password'
        'raw_user_meta_data',    // JSONB for custom user data (name, etc)
        'raw_app_meta_data',     // JSONB for app metadata
        'email_confirmed_at',
        'phone',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'encrypted_password',
        'recovery_token',
        'email_change_token_new',
        'email_change_token_current',
        'confirmation_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'id' => 'string',
            'email_confirmed_at' => 'datetime',
            'raw_user_meta_data' => 'json',  // JSONB for storing name, avatar, etc
            'raw_app_meta_data' => 'json',   // JSONB for app-specific data
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
            'last_sign_in_at' => 'datetime',
            'confirmed_at' => 'datetime',
        ];
    }

    /**
     * Get user's name from metadata
     * Supabase stores custom fields in raw_user_meta_data
     */
    public function getNameAttribute(): ?string
    {
        return $this->raw_user_meta_data['name'] ?? $this->email;
    }

    /**
     * Set user's name to metadata
     */
    public function setNameAttribute($value): void
    {
        $metadata = $this->raw_user_meta_data ?? [];
        $metadata['name'] = $value;
        $this->raw_user_meta_data = $metadata;
    }

    /**
     * Override password accessor for Supabase
     */
    public function getAuthPassword()
    {
        return $this->encrypted_password;
    }

    /**
     * Get the name of the unique identifier for the user
     */
    public function getAuthIdentifierName()
    {
        return 'id';
    }

    /**
     * Get the unique identifier for the user
     */
    public function getAuthIdentifier()
    {
        return $this->id;
    }

    public function profile(): HasOne
    {
        // Supabase: profiles.id = auth.users.id (same UUID, 1-to-1)
        return $this->hasOne(Profile::class, 'id', 'id');
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function addresses(): HasMany
    {
        return $this->hasMany(UserAddress::class);
    }

    public function createdProducts(): HasMany
    {
        return $this->hasMany(Product::class, 'created_by');
    }

    public function canAccessPanel(Panel $panel): bool
    {
        return $this->profile?->role === 'admin' || $this->hasRole('super_admin');
    }
}
