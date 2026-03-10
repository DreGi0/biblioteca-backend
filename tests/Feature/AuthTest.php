<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    // =========================================================
    //  LOGIN
    // =========================================================

    /** @test */
    public function test_it_can_login()
    {
        // Preparacion
        $user = User::factory()->create([
            'password' => bcrypt('test123'),
        ]);

        // Ejecucion
        $response = $this->post('/api/v1/login', [
            'email'    => $user->email,
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

    /** @test */
    public function test_login_fails_with_wrong_password()
    {
        // Preparacion
        $user = User::factory()->create([
            'password' => bcrypt('correct_password'),
        ]);

        // Ejecucion
        $response = $this->post('/api/v1/login', [
            'email'    => $user->email,
            'password' => 'wrong_password',
        ]);

        // Verificacion: el controlador retorna 422 con mensaje de error
        $response->assertStatus(422);
        $response->assertJsonStructure(['message']);
    }

    /** @test */
    public function test_login_fails_with_nonexistent_email()
    {
        // Ejecucion — email que no existe en la BD
        $response = $this->post('/api/v1/login', [
            'email'    => 'noexiste@example.com',
            'password' => 'cualquier_pass',
        ]);

        // Verificacion: el controlador retorna 422 con mensaje de error
        $response->assertStatus(422);
        $response->assertJsonStructure(['message']);
    }

    /** @test */
    public function test_login_fails_when_fields_are_missing()
    {
        // Ejecucion — sin enviar datos
        $response = $this->post('/api/v1/login', []);

        // Verificacion: Auth::attempt falla y devuelve 422 + message
        $response->assertStatus(422);
        $response->assertJsonStructure(['message']);
    }

    // =========================================================
    //  LOGOUT
    // =========================================================

    /** @test */
    public function test_authenticated_user_can_logout()
    {
        // Preparacion — usuario autenticado via Sanctum
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        // Ejecucion
        $response = $this->post('/api/v1/logout');

        // Verificacion
        $response->assertStatus(200);
        $response->assertJson(['message' => 'Logged out successfully']);
    }

    /** @test */
    public function test_unauthenticated_user_cannot_logout()
    {
        // IMPORTANTE: el header Accept: application/json le indica a Laravel
        // que es una peticion de API y debe devolver 401 en vez de redirigir.
        $response = $this->withHeaders(['Accept' => 'application/json'])
                         ->post('/api/v1/logout');

        // Verificacion: debe rechazar con 401
        $response->assertStatus(401);
    }

    // =========================================================
    //  PERFIL
    // =========================================================

    /** @test */
    public function test_authenticated_user_can_view_profile()
    {
        // Preparacion
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        // Ejecucion
        $response = $this->get('/api/v1/profile');

        // Verificacion: la respuesta es { "user": { id, name, email, ... } }
        $response->assertStatus(200);
        $response->assertJsonStructure([
            'user' => [
                'id',
                'name',
                'email',
            ],
        ]);
    }

    /** @test */
    public function test_unauthenticated_user_cannot_view_profile()
    {
        // IMPORTANTE: header Accept para que Laravel devuelva 401 y no redirija
        $response = $this->withHeaders(['Accept' => 'application/json'])
                         ->get('/api/v1/profile');

        // Verificacion: debe devolver 401
        $response->assertStatus(401);
    }

    /** @test */
    public function test_profile_returns_correct_user_data()
    {
        // Preparacion
        $user = User::factory()->create([
            'name'  => 'Juan Pérez',
            'email' => 'juan@example.com',
        ]);
        Sanctum::actingAs($user);

        // Ejecucion
        $response = $this->get('/api/v1/profile');

        // Verificacion: los datos dentro de "user" son del usuario autenticado
        $response->assertStatus(200);
        $response->assertJsonFragment([
            'name'  => 'Juan Pérez',
            'email' => 'juan@example.com',
        ]);
    }
}