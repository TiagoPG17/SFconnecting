<?php

declare(strict_types=1);

namespace Tests\Feature\SFconnecting\Clientes;

use App\Domain\Clientes\Models\Cliente;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ClienteApiTest extends TestCase
{
    use RefreshDatabase;

    private function asesor(): User
    {
        $this->crearRol('comercial');
        $user = User::factory()->create();
        $user->assignRole('comercial');
        return $user;
    }

    private function admin(): User
    {
        $this->crearRol('admin');
        $this->crearRol('gerente');
        $this->crearRol('comercial');
        $user = User::factory()->create();
        $user->assignRole('admin');
        return $user;
    }

    // â€” AUTENTICACIÃ“N â€”

    public function test_usuario_no_autenticado_recibe_401(): void
    {
        $this->getJson('/api/clientes')->assertStatus(401);
    }

    // â€” LISTADO â€”

    public function test_asesor_puede_listar_clientes(): void
    {
        $asesor = $this->asesor();
        Cliente::factory(3)->create(['user_id' => $asesor->id]);

        $this->actingAs($asesor)
            ->getJson('/api/clientes')
            ->assertOk()
            ->assertJsonStructure(['success', 'message', 'data']);
    }

    public function test_listado_retorna_estructura_correcta(): void
    {
        $asesor = $this->asesor();

        $this->actingAs($asesor)
            ->getJson('/api/clientes')
            ->assertOk()
            ->assertJsonPath('success', true);
    }

    // â€” CREAR â€”

    public function test_asesor_puede_crear_cliente(): void
    {
        $asesor = $this->asesor();

        $this->actingAs($asesor)
            ->postJson('/api/clientes', [
                'razon_social' => 'Empresa Nueva S.A.S',
                'nit'          => '900111000',
                'email'        => 'nueva@empresa.co',
                'ciudad'       => 'BogotÃ¡',
            ])
            ->assertStatus(201)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.nit', '900111000');

        $this->assertDatabaseHas('clientes', ['nit' => '900111000']);
    }

    public function test_crear_falla_sin_nit(): void
    {
        $asesor = $this->asesor();

        $this->actingAs($asesor)
            ->postJson('/api/clientes', ['razon_social' => 'Sin NIT'])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['nit']);
    }

    public function test_crear_falla_sin_razon_social(): void
    {
        $asesor = $this->asesor();

        $this->actingAs($asesor)
            ->postJson('/api/clientes', ['nit' => '900111000'])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['razon_social']);
    }

    public function test_crear_falla_con_nit_duplicado(): void
    {
        $asesor = $this->asesor();
        Cliente::factory()->create(['nit' => '900111000']);

        $this->actingAs($asesor)
            ->postJson('/api/clientes', ['razon_social' => 'Otra', 'nit' => '900111000'])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['nit']);
    }

    public function test_crear_falla_con_email_invalido(): void
    {
        $asesor = $this->asesor();

        $this->actingAs($asesor)
            ->postJson('/api/clientes', ['razon_social' => 'X', 'nit' => '900111001', 'email' => 'no-es-email'])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    }

    public function test_crear_falla_con_nit_con_letras(): void
    {
        $asesor = $this->asesor();

        $this->actingAs($asesor)
            ->postJson('/api/clientes', ['razon_social' => 'X', 'nit' => 'ABC123'])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['nit']);
    }

    // â€” VER â€”

    public function test_asesor_puede_ver_su_cliente(): void
    {
        $asesor  = $this->asesor();
        $cliente = Cliente::factory()->create(['user_id' => $asesor->id]);

        $this->actingAs($asesor)
            ->getJson("/api/clientes/{$cliente->id}")
            ->assertOk()
            ->assertJsonPath('data.id', $cliente->id);
    }

    public function test_asesor_no_puede_ver_cliente_ajeno(): void
    {
        $asesor  = $this->asesor();
        $otro    = User::factory()->create();
        $cliente = Cliente::factory()->create(['user_id' => $otro->id]);

        $this->actingAs($asesor)
            ->getJson("/api/clientes/{$cliente->id}")
            ->assertStatus(403);
    }

    public function test_ver_cliente_inexistente_retorna_404(): void
    {
        $asesor = $this->asesor();

        $this->actingAs($asesor)
            ->getJson('/api/clientes/99999')
            ->assertStatus(404);
    }

    // â€” ACTUALIZAR â€”

    public function test_asesor_puede_actualizar_su_cliente(): void
    {
        $asesor  = $this->asesor();
        $cliente = Cliente::factory()->create(['user_id' => $asesor->id]);

        $this->actingAs($asesor)
            ->putJson("/api/clientes/{$cliente->id}", ['ciudad' => 'MedellÃ­n'])
            ->assertOk()
            ->assertJsonPath('data.ciudad', 'MedellÃ­n');
    }

    public function test_asesor_no_puede_actualizar_cliente_ajeno(): void
    {
        $asesor  = $this->asesor();
        $otro    = User::factory()->create();
        $cliente = Cliente::factory()->create(['user_id' => $otro->id]);

        $this->actingAs($asesor)
            ->putJson("/api/clientes/{$cliente->id}", ['ciudad' => 'Cali'])
            ->assertStatus(403);
    }

    // â€” ELIMINAR â€”

    public function test_admin_puede_eliminar_cliente(): void
    {
        $admin   = $this->admin();
        $cliente = Cliente::factory()->create();

        $this->actingAs($admin)
            ->deleteJson("/api/clientes/{$cliente->id}")
            ->assertOk()
            ->assertJsonPath('success', true);

        $this->assertSoftDeleted('clientes', ['id' => $cliente->id]);
    }

    public function test_asesor_no_puede_eliminar_cliente(): void
    {
        $asesor  = $this->asesor();
        $cliente = Cliente::factory()->create(['user_id' => $asesor->id]);

        $this->actingAs($asesor)
            ->deleteJson("/api/clientes/{$cliente->id}")
            ->assertStatus(403);
    }

    // â€” RESTAURAR â€”

    public function test_admin_puede_restaurar_cliente_eliminado(): void
    {
        $admin   = $this->admin();
        $cliente = Cliente::factory()->create();
        $cliente->delete();

        $this->actingAs($admin)
            ->postJson("/api/clientes/{$cliente->id}/restore")
            ->assertOk()
            ->assertJsonPath('success', true);

        $this->assertDatabaseHas('clientes', ['id' => $cliente->id, 'deleted_at' => null]);
    }
}

