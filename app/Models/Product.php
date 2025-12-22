<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class Product extends Model
{
    /**
     * Standard Laravel products table with bigint auto-increment ID
     */
    // Using default bigint auto-increment - no need to set keyType or incrementing

    protected $fillable = [
        'name',
        'price',
        'capacity',
        'category',
        'specifications',
        'image_urls',
        'rating',
        'review_count',
        'is_active',
        'created_by',
    ];

    protected $casts = [
        'specifications' => 'json',
        'image_urls' => 'json',
        'price' => 'decimal:2',
        'rating' => 'decimal:2',
        'is_active' => 'boolean',
        'review_count' => 'integer',
        'created_by' => 'integer',
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

