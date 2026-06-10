<?php

declare(strict_types=1);

namespace Tests\Feature\SFconnecting\Contactos;

use App\Domain\Clientes\Models\Cliente;
use App\Domain\Clientes\Models\Contacto;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ContactoApiTest extends TestCase
{
    use RefreshDatabase;

    private function asesor(): User
    {
        $this->crearRol('comercial');
        $user = User::factory()->create();
        $user->assignRole('comercial');
        return $user;
    }

    private function gerente(): User
    {
        $this->crearRol('gerente');
        $user = User::factory()->create();
        $user->assignRole('gerente');
        return $user;
    }

    // â€” AUTH â€”

    public function test_no_autenticado_recibe_401(): void
    {
        $cliente = Cliente::factory()->create();

        $this->getJson("/api/clientes/{$cliente->id}/contactos")->assertStatus(401);
        $this->postJson("/api/clientes/{$cliente->id}/contactos", [])->assertStatus(401);
    }

    // â€” LISTAR â€”

    public function test_asesor_puede_listar_contactos_de_su_cliente(): void
    {
        $asesor  = $this->asesor();
        $cliente = Cliente::factory()->create(['user_id' => $asesor->id]);
        Contacto::factory()->count(3)->create(['cliente_id' => $cliente->id]);

        $this->actingAs($asesor)
            ->getJson("/api/clientes/{$cliente->id}/contactos")
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonCount(3, 'data');
    }

    public function test_asesor_no_puede_listar_contactos_de_cliente_ajeno(): void
    {
        $asesor  = $this->asesor();
        $cliente = Cliente::factory()->create();

        $this->actingAs($asesor)
            ->getJson("/api/clientes/{$cliente->id}/contactos")
            ->assertStatus(403);
    }

    public function test_gerente_puede_listar_contactos_de_cualquier_cliente(): void
    {
        $gerente = $this->gerente();
        $cliente = Cliente::factory()->create();
        Contacto::factory()->count(2)->create(['cliente_id' => $cliente->id]);

        $this->actingAs($gerente)
            ->getJson("/api/clientes/{$cliente->id}/contactos")
            ->assertOk()
            ->assertJsonCount(2, 'data');
    }

    // â€” CREAR â€”

    public function test_asesor_puede_crear_contacto_en_su_cliente(): void
    {
        $asesor  = $this->asesor();
        $cliente = Cliente::factory()->create(['user_id' => $asesor->id]);

        $this->actingAs($asesor)
            ->postJson("/api/clientes/{$cliente->id}/contactos", [
                'nombre'    => 'Laura Mendez',
                'cargo'     => 'Directora',
                'email'     => 'laura@empresa.com',
                'telefono'  => '3109876543',
                'principal' => false,
            ])
            ->assertStatus(201)
            ->assertJsonPath('success', true);

        $this->assertDatabaseHas('contactos', ['nombre' => 'Laura Mendez', 'cliente_id' => $cliente->id]);
    }

    public function test_asesor_no_puede_crear_contacto_en_cliente_ajeno(): void
    {
        $asesor  = $this->asesor();
        $cliente = Cliente::factory()->create();

        $this->actingAs($asesor)
            ->postJson("/api/clientes/{$cliente->id}/contactos", [
                'nombre' => 'Intruso',
            ])
            ->assertStatus(403);
    }

    public function test_crear_falla_sin_nombre(): void
    {
        $asesor  = $this->asesor();
        $cliente = Cliente::factory()->create(['user_id' => $asesor->id]);

        $this->actingAs($asesor)
            ->postJson("/api/clientes/{$cliente->id}/contactos", [
                'cargo' => 'Gerente',
            ])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['nombre']);
    }

    public function test_crear_falla_con_email_invalido(): void
    {
        $asesor  = $this->asesor();
        $cliente = Cliente::factory()->create(['user_id' => $asesor->id]);

        $this->actingAs($asesor)
            ->postJson("/api/clientes/{$cliente->id}/contactos", [
                'nombre' => 'Test',
                'email'  => 'no-es-email',
            ])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    }

    public function test_crear_contacto_principal_desmarca_anterior(): void
    {
        $asesor   = $this->asesor();
        $cliente  = Cliente::factory()->create(['user_id' => $asesor->id]);
        $anterior = Contacto::factory()->create(['cliente_id' => $cliente->id, 'principal' => true]);

        $this->actingAs($asesor)
            ->postJson("/api/clientes/{$cliente->id}/contactos", [
                'nombre'    => 'Nuevo Principal',
                'principal' => true,
            ])
            ->assertStatus(201);

        $this->assertDatabaseHas('contactos', ['id' => $anterior->id, 'principal' => false]);
    }

    // â€” VER â€”

    public function test_asesor_puede_ver_contacto_de_su_cliente(): void
    {
        $asesor   = $this->asesor();
        $cliente  = Cliente::factory()->create(['user_id' => $asesor->id]);
        $contacto = Contacto::factory()->create(['cliente_id' => $cliente->id]);

        $this->actingAs($asesor)
            ->getJson("/api/contactos/{$contacto->id}")
            ->assertOk()
            ->assertJsonPath('data.id', $contacto->id);
    }

    public function test_asesor_no_puede_ver_contacto_de_cliente_ajeno(): void
    {
        $asesor   = $this->asesor();
        $contacto = Contacto::factory()->create();

        $this->actingAs($asesor)
            ->getJson("/api/contactos/{$contacto->id}")
            ->assertStatus(403);
    }

    public function test_ver_contacto_inexistente_retorna_404(): void
    {
        $asesor = $this->asesor();

        $this->actingAs($asesor)
            ->getJson('/api/contactos/99999')
            ->assertStatus(404);
    }

    // â€” ACTUALIZAR â€”

    public function test_asesor_puede_actualizar_contacto_de_su_cliente(): void
    {
        $asesor   = $this->asesor();
        $cliente  = Cliente::factory()->create(['user_id' => $asesor->id]);
        $contacto = Contacto::factory()->create(['cliente_id' => $cliente->id]);

        $this->actingAs($asesor)
            ->putJson("/api/contactos/{$contacto->id}", [
                'nombre' => 'Nombre Actualizado',
                'cargo'  => 'Nuevo Cargo',
            ])
            ->assertOk()
            ->assertJsonPath('success', true);

        $this->assertDatabaseHas('contactos', ['id' => $contacto->id, 'nombre' => 'Nombre Actualizado']);
    }

    public function test_asesor_no_puede_actualizar_contacto_de_cliente_ajeno(): void
    {
        $asesor   = $this->asesor();
        $contacto = Contacto::factory()->create();

        $this->actingAs($asesor)
            ->putJson("/api/contactos/{$contacto->id}", ['nombre' => 'Hack'])
            ->assertStatus(403);
    }

    // â€” ELIMINAR â€”

    public function test_asesor_puede_eliminar_contacto_de_su_cliente(): void
    {
        $asesor   = $this->asesor();
        $cliente  = Cliente::factory()->create(['user_id' => $asesor->id]);
        $contacto = Contacto::factory()->create(['cliente_id' => $cliente->id]);

        $this->actingAs($asesor)
            ->deleteJson("/api/contactos/{$contacto->id}")
            ->assertOk()
            ->assertJsonPath('success', true);

        $this->assertSoftDeleted('contactos', ['id' => $contacto->id]);
    }

    public function test_asesor_no_puede_eliminar_contacto_de_cliente_ajeno(): void
    {
        $asesor   = $this->asesor();
        $contacto = Contacto::factory()->create();

        $this->actingAs($asesor)
            ->deleteJson("/api/contactos/{$contacto->id}")
            ->assertStatus(403);
    }
}

