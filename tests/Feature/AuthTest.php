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
        $response = $this->postJson('/api/v1/login', [
            'email' => $user->email,
            'password' => 'test123',
        ]);

        $response->dump(); // agrega esta línea antes del assertStatus
        $response->assertStatus(200);
        // Verificacion
        $response->assertStatus(200);
        $response->assertJsonStructure([
            'access_token',
            'token_type',
            'user',
        ]);

        $this->assertAuthenticatedAs($user);
    }
}
