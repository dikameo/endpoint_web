<?php

namespace App\Helpers;

use Illuminate\Support\Str;

class IdGenerator
{
    /**
     * Generate unique Order ID
     * Format: ORD-YYYYMMDD-TIMESTAMP-RANDOM
     * Example: ORD-20251219-1734567890-A3F2
     */
    public static function generateOrderId(): string
    {
        $date = now()->format('Ymd');
        $timestamp = now()->timestamp;
        $random = strtoupper(Str::random(4));
        
        return "ORD-{$date}-{$timestamp}-{$random}";
    }

    /**
     * Generate unique Product ID
     * Format: PROD-CATEGORY-RANDOM
     * Example: PROD-ARABICA-A1B2C3D4
     */
    public static function generateProductId(?string $category = null): string
    {
        $categoryCode = $category 
            ? strtoupper(substr($category, 0, 8)) 
            : 'GENERAL';
        
        $random = strtoupper(Str::random(8));
        
        return "PROD-{$categoryCode}-{$random}";
    }

    /**
     * Generate unique Product ID with timestamp
     * Format: PROD-TIMESTAMP-RANDOM
     * Example: PROD-1734567890-A1B2C3D4
     */
    public static function generateProductIdSimple(): string
    {
        $timestamp = now()->timestamp;
        $random = strtoupper(Str::random(8));
        
        return "PROD-{$timestamp}-{$random}";
    }

    /**
     * Generate tracking number
     * Format: TRK-YYYYMMDD-RANDOM
     * Example: TRK-20251219-A3F2E1B5
     */
    public static function generateTrackingNumber(): string
    {
        $date = now()->format('Ymd');
        $random = strtoupper(Str::random(8));
        
        return "TRK-{$date}-{$random}";
    }

    /**
     * Generate UUID v4
     * Untuk compatibility dengan auth.users
     */
    public static function generateUuid(): string
    {
        return (string) Str::uuid();
    }
}
