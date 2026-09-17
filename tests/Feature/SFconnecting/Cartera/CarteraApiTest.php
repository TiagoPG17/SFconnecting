<?php

declare(strict_types=1);

namespace Tests\Feature\SFconnecting\Cartera;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CarteraApiTest extends TestCase
{
    use RefreshDatabase;

    private User $cartera;
    private User $admin;
    private User $gerente;
    private User $comercial;

    protected function setUp(): void
    {
        parent::setUp();

        foreach (['cartera', 'admin', 'gerente', 'comercial'] as $rol) {
            $this->crearRol($rol);
        }

        $this->cartera   = User::factory()->create()->assignRole('cartera');
        $this->admin     = User::factory()->create()->assignRole('admin');
        $this->gerente    = User::factory()->create()->assignRole('gerente');
        $this->comercial = User::factory()->create()->assignRole('comercial');

        $this->fakeErp->agregarGestionCartera([
            [
                'Compania'          => 1,
                'NroDocumento'      => 'FV-100',
                'Cliente'           => 'Cliente Uno',
                'Vendedor'          => 'Vendedor Uno',
                'FechaPedido'       => '2026-09-01',
                'FechaCumplimiento' => '2026-09-15',
                'SubtotalPendiente' => 500000,
            ],
        ]);
    }

    private function pedidoPayload(): array
    {
        return [
            'pedidos' => [
                [
                    'compania'           => 1,
                    'nro_documento'      => 'FV-100',
                    'fecha_inicio_cobro' => '2026-09-20',
                ],
            ],
        ];
    }

    public function test_cartera_puede_notificar_y_resolver_pedido(): void
    {
        $this->actingAs($this->cartera)
            ->postJson('/gestion-cartera/notificar', $this->pedidoPayload())
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('insertados', 1);

        $this->actingAs($this->cartera)
            ->patchJson('/gestion-cartera/resolver', ['compania' => 1, 'nro_documento' => 'FV-100'])
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('resuelto', true);
    }

    public function test_admin_puede_notificar(): void
    {
        $this->actingAs($this->admin)
            ->postJson('/gestion-cartera/notificar', $this->pedidoPayload())
            ->assertOk()
            ->assertJsonPath('success', true);
    }

    public function test_gerente_puede_ver_pero_no_notificar_ni_resolver(): void
    {
        $this->actingAs($this->gerente)
            ->get('/gestion-cartera')
            ->assertOk();

        $this->actingAs($this->gerente)
            ->postJson('/gestion-cartera/notificar', $this->pedidoPayload())
            ->assertForbidden();

        $this->actingAs($this->gerente)
            ->patchJson('/gestion-cartera/resolver', ['compania' => 1, 'nro_documento' => 'FV-100'])
            ->assertForbidden();
    }

    public function test_rol_sin_acceso_recibe_403_en_las_tres_rutas(): void
    {
        $this->actingAs($this->comercial)->get('/gestion-cartera')->assertForbidden();

        $this->actingAs($this->comercial)
            ->postJson('/gestion-cartera/notificar', $this->pedidoPayload())
            ->assertForbidden();

        $this->actingAs($this->comercial)
            ->patchJson('/gestion-cartera/resolver', ['compania' => 1, 'nro_documento' => 'FV-100'])
            ->assertForbidden();
    }

    public function test_notificar_es_idempotente_no_duplica(): void
    {
        $this->actingAs($this->cartera)
            ->postJson('/gestion-cartera/notificar', $this->pedidoPayload())
            ->assertJsonPath('insertados', 1);

        $this->actingAs($this->cartera)
            ->postJson('/gestion-cartera/notificar', $this->pedidoPayload())
            ->assertJsonPath('insertados', 0);
    }

    public function test_resolver_marca_como_notificado_y_desaparece_de_pendientes(): void
    {
        $this->actingAs($this->cartera)->postJson('/gestion-cartera/notificar', $this->pedidoPayload());

        $this->assertCount(1, $this->fakeErp->notificacionesCarteraPendientes(1));

        $this->actingAs($this->cartera)
            ->patchJson('/gestion-cartera/resolver', ['compania' => 1, 'nro_documento' => 'FV-100'])
            ->assertJsonPath('resuelto', true);

        $this->assertCount(0, $this->fakeErp->notificacionesCarteraPendientes(1));
    }

    public function test_resolver_pedido_inexistente_retorna_resuelto_false(): void
    {
        $this->actingAs($this->cartera)
            ->patchJson('/gestion-cartera/resolver', ['compania' => 1, 'nro_documento' => 'NO-EXISTE'])
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('resuelto', false);
    }

    public function test_index_no_truena_si_erp_no_disponible(): void
    {
        $this->fakeErp->simularDesconexion();

        $this->actingAs($this->cartera)
            ->get('/gestion-cartera')
            ->assertOk();
    }

    public function test_sin_autenticacion_redirige_a_login(): void
    {
        $this->get('/gestion-cartera')->assertRedirect('/login');
    }
}
