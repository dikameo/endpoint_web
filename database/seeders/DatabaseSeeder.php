<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Profile;
use App\Models\Product;
use App\Models\Order;
use App\Models\UserAddress;
use App\Helpers\IdGenerator;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * IMPORTANT: This seeder assumes users already exist in auth.users (Supabase Auth)
     * You need to create users via Supabase Auth first, then get their UUIDs
     */
    public function run(): void
    {
        // Real UUIDs from Supabase auth.users
        $adminId = '8f411fc5-b498-4449-ab84-dadf4acf700c'; // admin@roastmaster.com
        
        
        $customerId = null; // Replace with real customer UUID when available

        // Create admin profile
        Profile::firstOrCreate(
            ['id' => $adminId],
            [
                'email' => 'admin@roastmaster.com',
                'name' => 'Admin Roastmaster',
                'phone' => '081234567890',
                'role' => 'admin',
            ]
        );

        // Create customer profile only if UUID available
        if ($customerId) {
            Profile::firstOrCreate(
                ['id' => $customerId],
                [
                    'email' => 'customer@example.com',
                    'name' => 'John Doe',
                    'phone' => '081234567891',
                    'role' => 'customer',
                ]
            );

            // Create user address only if customer exists
            UserAddress::firstOrCreate(
                ['user_id' => $customerId],
                [
                    'alamat' => 'Jl. Koffee No. 123, Jakarta',
                    'latitude' => -6.200000,
                    'longitude' => 106.816666,
                    'accuracy' => 'high', // text field, not numeric!
                ]
            );
        }

        // Create products with manual IDs (required because id is text, not auto increment)
        $products = [
            [
                'id' => IdGenerator::generateProductId('arabica'),
                'name' => 'Premium Arabica',
                'price' => 150000,
                'capacity' => '250g',
                'category' => 'arabica',
                'description' => 'Premium quality Arabica coffee from Sumatra highlands',
                'specifications' => [
                    'roast_level' => 'medium',
                    'origin' => 'Sumatra',
                    'flavor_notes' => ['chocolate', 'caramel', 'nutty']
                ],
                'image_urls' => [
                    'https://example.com/arabica1.jpg',
                    'https://example.com/arabica2.jpg'
                ],
                'rating' => 4.5,
                'review_count' => 120,
                'is_active' => true,
                'created_by' => $adminId, // UUID from auth.users
            ],
            [
                'id' => IdGenerator::generateProductId('robusta'),
                'name' => 'Robusta Bold',
                'price' => 120000,
                'capacity' => '250g',
                'category' => 'robusta',
                'description' => 'Strong and bold Robusta coffee from Java',
                'specifications' => [
                    'roast_level' => 'dark',
                    'origin' => 'Java',
                    'flavor_notes' => ['earthy', 'strong', 'bitter']
                ],
                'image_urls' => [
                    'https://example.com/robusta1.jpg'
                ],
                'rating' => 4.2,
                'review_count' => 85,
                'is_active' => true,
                'created_by' => $adminId,
            ],
            [
                'id' => IdGenerator::generateProductId('blend'),
                'name' => 'Specialty Blend',
                'price' => 180000,
                'capacity' => '500g',
                'category' => 'blend',
                'description' => 'Carefully crafted blend of premium beans from multiple origins',
                'specifications' => [
                    'roast_level' => 'medium-dark',
                    'origin' => 'Multi-origin',
                    'flavor_notes' => ['fruity', 'floral', 'sweet']
                ],
                'image_urls' => [
                    'https://example.com/blend1.jpg',
                    'https://example.com/blend2.jpg',
                    'https://example.com/blend3.jpg'
                ],
                'rating' => 4.8,
                'review_count' => 200,
                'is_active' => true,
                'created_by' => $adminId,
            ],
        ];

        foreach ($products as $productData) {
            Product::firstOrCreate(
                ['id' => $productData['id']],
                $productData
            );
        }

        // Create sample order only if customer exists
        if ($customerId) {
            $product = Product::first();
            if ($product) {
                Order::firstOrCreate(
                    ['id' => IdGenerator::generateOrderId()],
                    [
                        'user_id' => $customerId, // UUID from auth.users
                        'status' => 'pendingPayment', // Must match CHECK constraint
                        'order_date' => now(),
                        'subtotal' => 300000,
                        'shipping_cost' => 15000,
                        'total' => 315000,
                        'shipping_address' => 'Jl. Koffee No. 123, Jakarta',
                        'payment_method' => 'cash_on_delivery',
                        'tracking_number' => IdGenerator::generateTrackingNumber(),
                        'items' => [
                            [
                                'product_id' => $product->id,
                                'product_name' => $product->name,
                                'quantity' => 2,
                                'price' => $product->price,
                                'subtotal' => $product->price * 2,
                            ]
                        ],
                    ]
                );
            }
        }
    }
}
