<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class Product extends Model
{
    use SoftDeletes;

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
        'specifications' => 'array',
        'image_urls' => 'array',
        'price' => 'decimal:2',
        'rating' => 'decimal:2',
        'is_active' => 'boolean',
        'review_count' => 'integer',
    ];

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function toArray()
    {
        $attributes = parent::toArray();

        // Convert image paths to full URLs
        if (isset($attributes['image_urls']) && is_array($attributes['image_urls'])) {
            $attributes['image_urls'] = array_map(function ($path) {
                if (filter_var($path, FILTER_VALIDATE_URL)) {
                    return $path;
                }
                return Storage::disk('s3')->url($path);
            }, $attributes['image_urls']);
        }

        return $attributes;
    }
}
