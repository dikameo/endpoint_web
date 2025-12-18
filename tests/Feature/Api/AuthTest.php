<?php

namespace Tests\Feature\Api;

use App\Models\User;
use App\Models\Profile;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    // ... (previous methods remain same, just fixing the import location and the method I broke)

    public function test_user_can_register()
    {
        // ... (this method content is fine, I will just replace the top part and the broken method)
        $response = $this->postJson('/api/register', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password',
            'phone' => '08123456789',
        ]);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'user',
                    'token',
                ]
            ]);

        $this->assertDatabaseHas('users', ['email' => 'test@example.com']);
        $this->assertDatabaseHas('profiles', ['name' => 'Test User']);
    }

    public function test_user_can_login()
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => bcrypt('password'),
        ]);

        $response = $this->postJson('/api/login', [
            'email' => 'test@example.com',
            'password' => 'password',
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'token',
                ]
            ]);
    }

    public function test_user_can_get_profile()
    {
        // ... (previous content)
        $user = User::factory()->create();
        Profile::create([
            'user_id' => $user->id,
            'name' => $user->name,
            'role' => 'customer'
        ]);
        
        $token = auth('api')->login($user);

        $response = $this->getJson('/api/profile', [
            'Authorization' => 'Bearer ' . $token,
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'user_id' => $user->id,
                    'user' => [
                        'email' => $user->email,
                    ]
                ]
            ]);
    }

    public function test_user_can_update_profile()
    {
        $user = User::factory()->create();
        $profile = Profile::create([
            'user_id' => $user->id,
            'name' => 'Old Name',
            'phone' => '00000',
            'role' => 'customer'
        ]);

        $token = auth('api')->login($user);

        $response = $this->putJson('/api/profile', [
            'name' => 'New Name',
            'phone' => '081223344',
            'password' => 'newpassword'
        ], [
            'Authorization' => 'Bearer ' . $token
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Profile updated successfully'
            ]);

        $this->assertDatabaseHas('profiles', ['id' => $profile->id, 'name' => 'New Name', 'phone' => '081223344']);
    }
}
