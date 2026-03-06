<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_can_login()
    {
        // Preparacion
        $user = User::factory()->create([
            'password' => bcrypt('test123'),
        ]);

        // Ejecucion
        $response = $this->post('/api/v1/login', [
            'email' => $user->email,
            'password' => 'test123',
        ]);

        // Verificacion
        $response->assertStatus(200);
        $response->assertJsonStructure([
            'access_token',
            'token_type',
            'user',
        ]);

        $this->assertAuthenticatedAs($user);
    }

    public function test_login_with_invalid_credentials() 
    {
        $response = $this->postJson('/api/v1/login', [
            'email' => 'noexistes@example.com',
            'password' => 'passincorrecta',
        ]);

        $response->assertStatus(422)
            ->assertJson(['message' => 'Invalid credentials']);
    }

    public function test_user_can_logout()
    {
        $user = User::factory()->create([
            'password' => bcrypt('test123')
        ]);

        $loginResponse = $this->postJson('/api/v1/login', [
            'email'    => $user->email,
            'password' => 'test123',
        ]);

        $token = $loginResponse->json('access_token');

        $response = $this->withToken($token)
            ->postJson('/api/v1/logout');

        $response->assertStatus(200)
            ->assertJson(['message' => 'Logged out successfully']);
    }

    public function test_logout_fails_without_auth()
    {
        $response = $this->postJson('/api/v1/logout');

        $response->assertStatus(401);
    }

    public function test_user_can_see_profile()
    {
        $user = User::factory()->create([
            'password' => bcrypt('test123')
        ]);

        $loginResponse = $this->postJson('/api/v1/login', [
            'email'    => $user->email,
            'password' => 'test123',
        ]);

        $token = $loginResponse->json('access_token');

        $response = $this->withToken($token)
            ->getJson('/api/v1/profile');

        $response->assertStatus(200)
            ->assertJsonPath('user.email', $user->email);
    }

    public function test_profile_fails_without_auth()
    {
        $response = $this->getJson('/api/v1/profile');

        $response->assertStatus(401);
    }

    
}
