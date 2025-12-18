<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Profile;
use App\Models\Product;
use App\Models\Order;
use App\Models\UserAddress;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Create admin user
        $admin = User::create([
            'name' => 'Admin',
            'email' => 'admin@example.com',
            'password' => Hash::make('password'),
        ]);

        Profile::create([
            'user_id' => $admin->id,
            'name' => 'Admin',
            'phone' => '081234567890',
            'role' => 'admin',
        ]);

        // Create customer user
        $customer = User::create([
            'name' => 'John Doe',
            'email' => 'customer@example.com',
            'password' => Hash::make('password'),
        ]);

        Profile::create([
            'user_id' => $customer->id,
            'name' => 'John Doe',
            'phone' => '081234567891',
            'role' => 'customer',
        ]);

        // Create user address
        UserAddress::create([
            'user_id' => $customer->id,
            'alamat' => 'Jl. Koffee No. 123, Jakarta',
            'latitude' => -6.200000,
            'longitude' => 106.816666,
            'accuracy' => 5,
        ]);

        // Create products
        $products = [
            [
                'name' => 'Premium Arabica',
                'price' => 150000,
                'capacity' => '250g',
                'category' => 'arabica',
                'specifications' => [
                    'roast_level' => 'medium',
                    'origin' => 'Sumatra',
                    'flavor_notes' => ['chocolate', 'caramel', 'nutty']
                ],
                'rating' => 4.5,
                'review_count' => 120,
                'is_active' => true,
                'created_by' => $admin->id,
            ],
            [
                'name' => 'Robusta Bold',
                'price' => 120000,
                'capacity' => '250g',
                'category' => 'robusta',
                'specifications' => [
                    'roast_level' => 'dark',
                    'origin' => 'Java',
                    'flavor_notes' => ['earthy', 'strong', 'bitter']
                ],
                'rating' => 4.2,
                'review_count' => 85,
                'is_active' => true,
                'created_by' => $admin->id,
            ],
            [
                'name' => 'Specialty Blend',
                'price' => 180000,
                'capacity' => '500g',
                'category' => 'blend',
                'specifications' => [
                    'roast_level' => 'medium-dark',
                    'origin' => 'Multi-origin',
                    'flavor_notes' => ['fruity', 'floral', 'sweet']
                ],
                'rating' => 4.8,
                'review_count' => 200,
                'is_active' => true,
                'created_by' => $admin->id,
            ],
        ];

        foreach ($products as $productData) {
            Product::create($productData);
        }

        // Create sample order
        $product = Product::first();
        Order::create([
            'user_id' => $customer->id,
            'status' => 'pending',
            'subtotal' => 300000,
            'shipping_cost' => 15000,
            'total' => 315000,
            'shipping_address' => 'Jl. Koffee No. 123, Jakarta',
            'payment_method' => 'cash_on_delivery',
            'tracking_number' => 'TRK-' . strtoupper(uniqid()),
            'items' => [
                [
                    'product_id' => $product->id,
                    'product_name' => $product->name,
                    'quantity' => 2,
                    'price' => $product->price,
                    'subtotal' => $product->price * 2,
                ]
            ],
        ]);
    }
}
