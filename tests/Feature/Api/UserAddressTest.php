<?php

namespace Tests\Feature\Api;

use App\Models\User;
use App\Models\UserAddress;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class UserAddressTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_list_addresses()
    {
        $user = User::factory()->create();
        $token = auth('api')->login($user);

        UserAddress::create([
            'user_id' => $user->id,
            'alamat' => 'Jl. Test No. 1',
            'latitude' => -6.2,
            'longitude' => 106.8,
            'accuracy' => 10
        ]);

        $response = $this->getJson('/api/user-addresses', [
            'Authorization' => 'Bearer ' . $token
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'data' => [
                        '*' => ['id', 'alamat', 'latitude']
                    ]
                ]
            ]);
    }

    public function test_can_create_address()
    {
        $user = User::factory()->create();
        $token = auth('api')->login($user);

        $response = $this->postJson('/api/user-addresses', [
            'alamat' => 'Jl. Baru No. 99',
            'latitude' => -6.5,
            'longitude' => 107.0,
            'accuracy' => 5
        ], [
            'Authorization' => 'Bearer ' . $token
        ]);

        $response->assertStatus(201)
            ->assertJson([
                'success' => true,
                'data' => [
                    'alamat' => 'Jl. Baru No. 99'
                ]
            ]);
        
        $this->assertDatabaseHas('user_addresses', ['alamat' => 'Jl. Baru No. 99']);
    }

    public function test_can_update_address()
    {
        $user = User::factory()->create();
        $token = auth('api')->login($user);

        $address = UserAddress::create([
            'user_id' => $user->id,
            'alamat' => 'Jl. Lama',
            'latitude' => -6.2,
            'longitude' => 106.8
        ]);

        $response = $this->putJson("/api/user-addresses/{$address->id}", [
            'alamat' => 'Jl. Update',
            'latitude' => -6.3
        ], [
            'Authorization' => 'Bearer ' . $token
        ]);

        $response->assertStatus(200);
        $this->assertDatabaseHas('user_addresses', ['id' => $address->id, 'alamat' => 'Jl. Update']);
    }

    public function test_can_delete_address()
    {
        $user = User::factory()->create();
        $token = auth('api')->login($user);

        $address = UserAddress::create([
            'user_id' => $user->id,
            'alamat' => 'Jl. Hapus',
            'latitude' => -6.2,
            'longitude' => 106.8
        ]);

        $response = $this->deleteJson("/api/user-addresses/{$address->id}", [], [
            'Authorization' => 'Bearer ' . $token
        ]);

        $response->assertStatus(200);
        $this->assertDatabaseMissing('user_addresses', ['id' => $address->id]);
    }

    public function test_user_cannot_update_others_address()
    {
        $userA = User::factory()->create();
        $userB = User::factory()->create();
        $tokenB = auth('api')->login($userB);

        $addressA = UserAddress::create([
            'user_id' => $userA->id,
            'alamat' => 'Jl. User A',
            'latitude' => -6.2,
            'longitude' => 106.8
        ]);

        $response = $this->putJson("/api/user-addresses/{$addressA->id}", [
            'alamat' => 'Hacked Address'
        ], [
            'Authorization' => 'Bearer ' . $tokenB
        ]);

        $response->assertStatus(404); // Should be Not Found (Model scoping) or 403
    }
}
