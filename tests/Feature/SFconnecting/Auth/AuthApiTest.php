<?php

declare(strict_types=1);

namespace Tests\Feature\SFconnecting\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthApiTest extends TestCase
{
    use RefreshDatabase;

    // â€” LOGIN â€”

    public function test_login_exitoso_retorna_token(): void
    {
        $user = User::factory()->create(['password' => bcrypt('secreto123')]);

        $this->postJson('/api/auth/login', [
            'email'    => $user->email,
            'password' => 'secreto123',
        ])
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonStructure(['data' => ['token', 'user' => ['id', 'name', 'email']]]);
    }

    public function test_login_falla_con_credenciales_incorrectas(): void
    {
        User::factory()->create(['email' => 'real@test.com', 'password' => bcrypt('correcta')]);

        $this->postJson('/api/auth/login', [
            'email'    => 'real@test.com',
            'password' => 'incorrecta',
        ])
            ->assertStatus(401)
            ->assertJsonPath('success', false);
    }

    public function test_login_falla_sin_email(): void
    {
        $this->postJson('/api/auth/login', ['password' => 'algo'])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    }

    public function test_login_falla_sin_password(): void
    {
        $this->postJson('/api/auth/login', ['email' => 'a@b.com'])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['password']);
    }

    public function test_login_falla_con_email_invalido(): void
    {
        $this->postJson('/api/auth/login', ['email' => 'no-es-email', 'password' => 'abc'])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    }

    // â€” LOGOUT â€”

    public function test_logout_exitoso_revoca_token(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('test')->plainTextToken;

        $this->withToken($token)
            ->postJson('/api/auth/logout')
            ->assertOk()
            ->assertJsonPath('success', true);

        // El token fue eliminado de la base de datos
        $this->assertSame(0, $user->tokens()->count());
    }

    public function test_logout_sin_autenticar_retorna_401(): void
    {
        $this->postJson('/api/auth/logout')->assertStatus(401);
    }

    // â€” ME â€”

    public function test_me_retorna_usuario_autenticado(): void
    {
        $this->crearRol('comercial');
        $user = User::factory()->create();
        $user->assignRole('comercial');

        $this->actingAs($user)
            ->getJson('/api/auth/me')
            ->assertOk()
            ->assertJsonPath('data.id', $user->id)
            ->assertJsonPath('data.email', $user->email)
            ->assertJsonStructure(['data' => ['id', 'name', 'email', 'roles']]);
    }

    public function test_me_sin_autenticar_retorna_401(): void
    {
        $this->getJson('/api/auth/me')->assertStatus(401);
    }
}

