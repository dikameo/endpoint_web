<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class Product extends Model
{
    /**
     * Primary key is text/string, not auto increment
     * Supabase schema: id = text
     */
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'id',              // Required because not auto increment
        'name',
        'price',
        'capacity',
        'category',
        'specifications',
        'image_urls',
        'description',     // Added from schema
        'rating',
        'review_count',
        'is_active',
        'created_by',
    ];

    protected $casts = [
        'created_by' => 'string',        // UUID from auth.users
        'specifications' => 'json',      // JSONB → json (not array!)
        'image_urls' => 'json',          // JSONB → json (not array!)
        'price' => 'decimal:2',
        'rating' => 'decimal:2',
        'is_active' => 'boolean',
        'review_count' => 'integer',
    ];

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Convert the model instance to an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        $attributes = parent::toArray();

        // Convert image paths to full URLs
        if (isset($attributes['image_urls']) && is_array($attributes['image_urls'])) {
            $attributes['image_urls'] = array_map(function ($path) {
                if (filter_var($path, FILTER_VALIDATE_URL)) {
                    return $path;
                }
                return Storage::url($path);
            }, $attributes['image_urls']);
        }

        return $attributes;
    }
}
