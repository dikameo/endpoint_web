<?php

namespace Tests\Feature\Api;

use App\Models\Product;
use App\Models\User;
use App\Models\Profile;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class ProductTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_list_products()
    {
        Product::create([
            'name' => 'Test Coffee',
            'price' => 100000,
            'is_active' => true
        ]);

        $user = User::factory()->create();
        $token = auth('api')->login($user);

        $response = $this->getJson('/api/products', [
            'Authorization' => 'Bearer ' . $token,
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'data' => [
                        '*' => ['id', 'name', 'price']
                    ]
                ]
            ]);
    }

    public function test_can_create_product()
    {
        $user = User::factory()->create();
        Profile::create(['user_id' => $user->id, 'role' => 'admin', 'name' => 'Admin User']);
        $token = auth('api')->login($user);

        $response = $this->postJson('/api/products', [
            'name' => 'New Coffee',
            'price' => 150000,
            'capacity' => '250g',
            'category' => 'arabica',
            'is_active' => true,
        ], [
            'Authorization' => 'Bearer ' . $token,
        ]);

        $response->assertStatus(201)
            ->assertJson([
                'success' => true,
                'data' => [
                    'name' => 'New Coffee',
                    'price' => 150000,
                ]
            ]);

        $this->assertDatabaseHas('products', ['name' => 'New Coffee']);
    }

    public function test_non_admin_cannot_create_product()
    {
        $user = User::factory()->create();
        Profile::create(['user_id' => $user->id, 'role' => 'customer', 'name' => 'Customer User']);
        $token = auth('api')->login($user);

        $response = $this->postJson('/api/products', [
            'name' => 'New Coffee',
            'price' => 150000,
        ], [
            'Authorization' => 'Bearer ' . $token,
        ]);

        $response->assertStatus(403);
    }

    public function test_can_update_product()
    {
        $product = Product::create([
            'name' => 'Old Coffee',
            'price' => 100000,
            'is_active' => true
        ]);

        $user = User::factory()->create();
        Profile::create(['user_id' => $user->id, 'role' => 'admin', 'name' => 'Admin User']); 
        $token = auth('api')->login($user);

        $response = $this->putJson("/api/products/{$product->id}", [
            'name' => 'Updated Coffee',
            'price' => 120000,
        ], [
            'Authorization' => 'Bearer ' . $token,
        ]);

        $response->assertStatus(200);
        $this->assertDatabaseHas('products', ['name' => 'Updated Coffee']);
    }

    public function test_can_delete_product()
    {
        $product = Product::create([
            'name' => 'To Delete',
            'price' => 100000,
            'is_active' => true
        ]);

        $user = User::factory()->create();
        Profile::create(['user_id' => $user->id, 'role' => 'admin', 'name' => 'Admin User']);
        $token = auth('api')->login($user);

        $response = $this->deleteJson("/api/products/{$product->id}", [], [
            'Authorization' => 'Bearer ' . $token,
        ]);

        $response->assertStatus(200);
        $this->assertSoftDeleted('products', ['id' => $product->id]);
    }
}

