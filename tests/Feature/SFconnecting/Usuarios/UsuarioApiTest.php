<?php

declare(strict_types=1);

namespace Tests\Feature\SFconnecting\Usuarios;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UsuarioApiTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        $this->crearRol('admin');
        $this->crearRol('gerente');
        $this->crearRol('comercial');
        $this->crearPermiso('usuarios.gestionar');
        $user = User::factory()->create();
        $user->assignRole('admin');
        $user->givePermissionTo('usuarios.gestionar');
        return $user;
    }

    private function asesor(): User
    {
        $this->crearRol('comercial');
        $user = User::factory()->create();
        $user->assignRole('comercial');
        return $user;
    }

    // â€” AutenticaciÃ³n â€”

    public function test_usuario_no_autenticado_recibe_401(): void
    {
        $this->getJson('/api/usuarios')->assertStatus(401);
    }

    // â€” AutorizaciÃ³n â€”

    public function test_asesor_no_puede_listar_usuarios(): void
    {
        $this->actingAs($this->asesor())
            ->getJson('/api/usuarios')
            ->assertForbidden();
    }

    // â€” Listado â€”

    public function test_admin_puede_listar_usuarios(): void
    {
        $admin = $this->admin();
        User::factory(3)->create();

        $this->actingAs($admin)
            ->getJson('/api/usuarios')
            ->assertOk()
            ->assertJsonStructure(['success', 'message', 'data']);
    }

    public function test_listado_incluye_roles(): void
    {
        $admin = $this->admin();

        $response = $this->actingAs($admin)
            ->getJson('/api/usuarios')
            ->assertOk();

        $this->assertArrayHasKey('roles', $response->json('data.data')[0] ?? $response->json('data')[0]);
    }

    // â€” CreaciÃ³n â€”

    public function test_admin_puede_crear_usuario(): void
    {
        $admin = $this->admin();

        $this->actingAs($admin)
            ->postJson('/api/usuarios', [
                'name'                  => 'Juan Asesor',
                'email'                 => 'juan@sfconnecting.com',
                'password'              => 'Secret123!',
                'password_confirmation' => 'Secret123!',
                'rol'                   => 'comercial',
            ])
            ->assertCreated()
            ->assertJson(['success' => true]);
    }

    public function test_email_duplicado_retorna_error_422(): void
    {
        $admin = $this->admin();
        User::factory()->create(['email' => 'duplicado@sf.com']);

        $this->actingAs($admin)
            ->postJson('/api/usuarios', [
                'name'                  => 'Otro',
                'email'                 => 'duplicado@sf.com',
                'password'              => 'Secret123!',
                'password_confirmation' => 'Secret123!',
                'rol'                   => 'comercial',
            ])
            ->assertUnprocessable();
    }

    public function test_rol_invalido_retorna_error_422(): void
    {
        $admin = $this->admin();

        $this->actingAs($admin)
            ->postJson('/api/usuarios', [
                'name'                  => 'Test',
                'email'                 => 'test@sf.com',
                'password'              => 'Secret123!',
                'password_confirmation' => 'Secret123!',
                'rol'                   => 'superusuario',
            ])
            ->assertUnprocessable();
    }

    // â€” ActualizaciÃ³n â€”

    public function test_admin_puede_actualizar_nombre_y_rol(): void
    {
        $admin   = $this->admin();
        $usuario = User::factory()->create(['name' => 'Antiguo']);
        $usuario->assignRole('comercial');

        $this->actingAs($admin)
            ->putJson("/api/usuarios/{$usuario->id}", [
                'name'  => 'Nuevo Nombre',
                'email' => $usuario->email,
                'rol'   => 'gerente',
            ])
            ->assertOk()
            ->assertJson(['success' => true]);

        $this->assertDatabaseHas('users', ['id' => $usuario->id, 'name' => 'Nuevo Nombre']);
    }

    public function test_admin_puede_cambiar_password(): void
    {
        $admin   = $this->admin();
        $usuario = User::factory()->create();

        $this->actingAs($admin)
            ->putJson("/api/usuarios/{$usuario->id}", [
                'name'                  => $usuario->name,
                'email'                 => $usuario->email,
                'password'              => 'NuevaClave123!',
                'password_confirmation' => 'NuevaClave123!',
                'rol'                   => 'comercial',
            ])
            ->assertOk();
    }

    // â€” Toggle activo â€”

    public function test_admin_puede_desactivar_usuario(): void
    {
        $admin   = $this->admin();
        $usuario = User::factory()->create(['activo' => true]);

        $this->actingAs($admin)
            ->patchJson("/api/usuarios/{$usuario->id}/toggle")
            ->assertOk()
            ->assertJson(['success' => true]);

        $this->assertDatabaseHas('users', ['id' => $usuario->id, 'activo' => false]);
    }

    public function test_admin_no_puede_desactivarse_a_si_mismo(): void
    {
        $admin = $this->admin();

        $this->actingAs($admin)
            ->patchJson("/api/usuarios/{$admin->id}/toggle")
            ->assertForbidden();
    }

    // â€” Show â€”

    public function test_admin_puede_ver_usuario(): void
    {
        $admin   = $this->admin();
        $usuario = User::factory()->create();

        $this->actingAs($admin)
            ->getJson("/api/usuarios/{$usuario->id}")
            ->assertOk()
            ->assertJsonStructure(['success', 'data' => ['id', 'name', 'email', 'roles']]);
    }
}

