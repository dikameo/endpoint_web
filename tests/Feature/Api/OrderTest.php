<?php

namespace Tests\Feature\Api;

use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use App\Models\Profile;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class OrderTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_create_order()
    {
        $user = User::factory()->create();
        $token = auth('api')->login($user);
        
        $product = Product::create([
            'name' => 'Test Coffee',
            'price' => 100000,
            'is_active' => true
        ]);

        $response = $this->postJson('/api/orders', [
            'items' => [
                [
                    'product_id' => $product->id,
                    'quantity' => 1,
                    'price' => 100000
                ]
            ],
            'shipping_address' => 'Jl. Test',
            'payment_method' => 'cod',
            'subtotal' => 100000,
            'shipping_cost' => 10000,
            'total' => 110000,
        ], [
            'Authorization' => 'Bearer ' . $token,
        ]);

        $response->assertStatus(201);
        $this->assertDatabaseHas('orders', ['user_id' => $user->id, 'total' => 110000]);
    }

    public function test_can_list_orders()
    {
        $user = User::factory()->create();
        $token = auth('api')->login($user);

        Order::create([
            'user_id' => $user->id,
            'items' => [],
            'status' => 'pending',
            'subtotal' => 100000,
            'total' => 110000,
            'shipping_address' => 'Test',
            'payment_method' => 'cod'
        ]);

        $response = $this->getJson('/api/orders', [
            'Authorization' => 'Bearer ' . $token,
        ]);

        $response->assertStatus(200)
            ->assertJsonCount(1, 'data.data');
    }

    public function test_admin_can_view_all_orders()
    {
        // User A creates order
        $userA = User::factory()->create();
        Order::create([
            'user_id' => $userA->id,
            'items' => [],
            'status' => 'pending',
            'subtotal' => 100000,
            'total' => 110000,
            'shipping_address' => 'Test',
            'payment_method' => 'cod'
        ]);

        // Admin checks orders
        $admin = User::factory()->create();
        Profile::create(['user_id' => $admin->id, 'role' => 'admin', 'name' => 'Admin User']);
        $token = auth('api')->login($admin);

        $response = $this->getJson('/api/orders', [
            'Authorization' => 'Bearer ' . $token,
        ]);

        $response->assertStatus(200)
            ->assertJsonCount(1, 'data.data');
    }

    public function test_user_cannot_view_others_orders()
    {
        // User A creates order
        $userA = User::factory()->create();
        Order::create([
            'user_id' => $userA->id,
            'items' => [],
            'status' => 'pending',
            'subtotal' => 100000,
            'total' => 110000,
            'shipping_address' => 'Test',
            'payment_method' => 'cod'
        ]);

        // User B tries to view
        $userB = User::factory()->create();
        Profile::create(['user_id' => $userB->id, 'role' => 'customer', 'name' => 'Customer User']);
        $token = auth('api')->login($userB);

        $response = $this->getJson('/api/orders', [
            'Authorization' => 'Bearer ' . $token,
        ]);

        $response->assertStatus(200)
            ->assertJsonCount(0, 'data.data'); // Should be empty
    }

    public function test_admin_can_update_order_status()
    {
        $user = User::factory()->create();
        $order = Order::create([
            'user_id' => $user->id,
            'items' => [],
            'status' => 'pending',
            'subtotal' => 100000,
            'total' => 110000,
            'shipping_address' => 'Test',
            'payment_method' => 'cod'
        ]);

        $admin = User::factory()->create();
        Profile::create(['user_id' => $admin->id, 'role' => 'admin', 'name' => 'Admin User']);
        $token = auth('api')->login($admin);

        $response = $this->putJson("/api/orders/{$order->id}", [
            'status' => 'shipped',
            'tracking_number' => 'AWB123456'
        ], [
            'Authorization' => 'Bearer ' . $token,
        ]);

        $response->assertStatus(200);
        $this->assertDatabaseHas('orders', ['id' => $order->id, 'status' => 'shipped']);
    }
}
