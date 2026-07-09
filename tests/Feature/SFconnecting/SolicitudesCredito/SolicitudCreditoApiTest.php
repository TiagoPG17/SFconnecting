<?php

declare(strict_types=1);

namespace Tests\Feature\SFconnecting\SolicitudesCredito;

use App\Domain\Clientes\Models\Cliente;
use App\Domain\Negocios\Models\Negocio;
use App\Domain\Pipeline\Models\PipelineEstado;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class SolicitudCreditoApiTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private User $gerente;
    private User $asesor;
    private Cliente $cliente;
    private Negocio $negocio;

    protected function setUp(): void
    {
        parent::setUp();
        app()['cache']->forget('spatie.permission.cache');

        $permisos = [
            'negocios.ver', 'negocios.crear',
            'solicitudes_credito.ver', 'solicitudes_credito.crear',
            'solicitudes_credito.eliminar', 'solicitudes_credito.revisar',
        ];
        foreach ($permisos as $p) {
            Permission::firstOrCreate(['name' => $p, 'guard_name' => 'web']);
        }

        Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web'])->syncPermissions(Permission::all());
        Role::firstOrCreate(['name' => 'gerente', 'guard_name' => 'web'])->syncPermissions([
            'solicitudes_credito.ver', 'solicitudes_credito.revisar',
        ]);
        Role::firstOrCreate(['name' => 'comercial', 'guard_name' => 'web'])->syncPermissions([
            'negocios.ver', 'negocios.crear', 'solicitudes_credito.ver', 'solicitudes_credito.crear',
        ]);

        $this->admin   = User::factory()->create()->assignRole('admin');
        $this->gerente = User::factory()->create()->assignRole('gerente');
        $this->asesor  = User::factory()->create()->assignRole('comercial');
        $this->cliente = Cliente::factory()->create(['nit' => '900123456']);

        $estadoNegocio = PipelineEstado::create([
            'nombre' => 'Negociación', 'slug' => 'negociacion-sc-api', 'tipo' => 'negocio',
            'color' => '#F97316', 'orden' => 4, 'porcentaje_cierre' => 75,
            'es_final' => false, 'es_ganado' => false, 'es_perdido' => false, 'activo' => true,
        ]);

        $this->negocio = Negocio::create([
            'nombre_negocio'     => 'Negocio con Cliente',
            'pipeline_estado_id' => $estadoNegocio->id,
            'asesor_id'          => $this->asesor->id,
            'cliente_id'         => $this->cliente->id,
            'valor_estimado'     => 20000000,
            'activo'             => true,
        ]);

        foreach ([
            ['nombre' => 'Radicada', 'slug' => 'credito-radicada', 'orden' => 1, 'porcentaje_cierre' => 10, 'es_final' => false, 'es_ganado' => false, 'es_perdido' => false],
            ['nombre' => 'En Revisión', 'slug' => 'credito-en-revision', 'orden' => 2, 'porcentaje_cierre' => 50, 'es_final' => false, 'es_ganado' => false, 'es_perdido' => false],
            ['nombre' => 'Aprobada', 'slug' => 'credito-aprobada', 'orden' => 3, 'porcentaje_cierre' => 100, 'es_final' => true, 'es_ganado' => true, 'es_perdido' => false],
            ['nombre' => 'Aprobada con Condiciones', 'slug' => 'credito-aprobada-condiciones', 'orden' => 4, 'porcentaje_cierre' => 100, 'es_final' => true, 'es_ganado' => true, 'es_perdido' => false],
            ['nombre' => 'Rechazada', 'slug' => 'credito-rechazada', 'orden' => 5, 'porcentaje_cierre' => 0, 'es_final' => true, 'es_ganado' => false, 'es_perdido' => true],
        ] as $estado) {
            PipelineEstado::create(array_merge($estado, ['tipo' => 'solicitud_credito', 'color' => '#6B7280', 'activo' => true]));
        }

        $this->fakeErp->agregarCliente('900123456', [
            'CUPO_CREDITO'        => 10000000,
            'CALIFICACION_CLIENTE' => 'A',
            'BLOQUEADO'           => 0,
        ]);
        $this->fakeErp->agregarCartera('900123456', [
            ['TIPO_DOCTO' => 'FV', 'NUM_DOCTO' => 1, 'SALDO' => 2000000, 'DIAS_VENCIDO' => 5, 'TRAMO_AGING' => '1-30'],
        ]);
    }

    public function test_crea_solicitud_credito_con_dossier_erp_snapshot(): void
    {
        $response = $this->actingAs($this->asesor, 'sanctum')
            ->postJson('/api/solicitudes-credito', [
                'negocio_id'       => $this->negocio->id,
                'monto_solicitado' => 5000000,
                'justificacion'    => 'Cliente requiere ampliar cupo para nuevo pedido.',
            ]);

        $response->assertCreated()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.dossier_erp.cupo_credito', 10000000)
            ->assertJsonPath('data.dossier_erp.saldo_total', 2000000)
            ->assertJsonPath('data.pipeline_estado.nombre', 'Radicada');

        $this->assertDatabaseHas('sf_solicitudes_credito', [
            'negocio_id'       => $this->negocio->id,
            'cliente_id'       => $this->cliente->id,
            'monto_solicitado' => 5000000,
        ]);
    }

    public function test_falla_si_negocio_no_tiene_cliente(): void
    {
        $estadoNegocio = PipelineEstado::where('tipo', 'negocio')->first();
        $negocioSinCliente = Negocio::create([
            'nombre_negocio'     => 'Sin Cliente',
            'pipeline_estado_id' => $estadoNegocio->id,
            'asesor_id'          => $this->asesor->id,
            'valor_estimado'     => 1000,
            'activo'             => true,
        ]);

        $response = $this->actingAs($this->asesor, 'sanctum')
            ->postJson('/api/solicitudes-credito', [
                'negocio_id'       => $negocioSinCliente->id,
                'monto_solicitado' => 1000000,
            ]);

        $response->assertUnprocessable();
    }

    public function test_falla_si_erp_no_disponible(): void
    {
        $this->fakeErp->simularDesconexion();

        $response = $this->actingAs($this->asesor, 'sanctum')
            ->postJson('/api/solicitudes-credito', [
                'negocio_id'       => $this->negocio->id,
                'monto_solicitado' => 1000000,
            ]);

        $response->assertUnprocessable();
    }

    public function test_no_permite_dos_solicitudes_activas_para_el_mismo_negocio(): void
    {
        $this->actingAs($this->asesor, 'sanctum')->postJson('/api/solicitudes-credito', [
            'negocio_id'       => $this->negocio->id,
            'monto_solicitado' => 1000000,
        ])->assertCreated();

        $response = $this->actingAs($this->asesor, 'sanctum')->postJson('/api/solicitudes-credito', [
            'negocio_id'       => $this->negocio->id,
            'monto_solicitado' => 2000000,
        ]);

        $response->assertUnprocessable();
    }

    public function test_gerente_aprueba_solicitud(): void
    {
        $creada = $this->actingAs($this->asesor, 'sanctum')->postJson('/api/solicitudes-credito', [
            'negocio_id'       => $this->negocio->id,
            'monto_solicitado' => 1000000,
        ])->json('data');

        $response = $this->actingAs($this->gerente, 'sanctum')
            ->postJson("/api/solicitudes-credito/{$creada['id']}/decidir", [
                'decision' => 'aprobada',
            ]);

        $response->assertOk()->assertJsonPath('data.pipeline_estado.nombre', 'Aprobada');

        $this->assertDatabaseHas('sf_solicitudes_credito', [
            'id'           => $creada['id'],
            'revisado_por' => $this->gerente->id,
        ]);
        $this->assertDatabaseHas('sf_auditoria_pipeline', [
            'auditable_id'   => $creada['id'],
            'evento'         => 'solicitud_credito_aprobada',
        ]);
    }

    public function test_gerente_aprueba_con_condiciones_requiere_condiciones(): void
    {
        $creada = $this->actingAs($this->asesor, 'sanctum')->postJson('/api/solicitudes-credito', [
            'negocio_id'       => $this->negocio->id,
            'monto_solicitado' => 1000000,
        ])->json('data');

        $response = $this->actingAs($this->gerente, 'sanctum')
            ->postJson("/api/solicitudes-credito/{$creada['id']}/decidir", [
                'decision' => 'aprobada_condiciones',
            ]);

        $response->assertUnprocessable();
    }

    public function test_gerente_rechaza_solicitud(): void
    {
        $creada = $this->actingAs($this->asesor, 'sanctum')->postJson('/api/solicitudes-credito', [
            'negocio_id'       => $this->negocio->id,
            'monto_solicitado' => 1000000,
        ])->json('data');

        $response = $this->actingAs($this->gerente, 'sanctum')
            ->postJson("/api/solicitudes-credito/{$creada['id']}/decidir", [
                'decision'            => 'rechazada',
                'comentario_revision' => 'Cliente con mora reciente.',
            ]);

        $response->assertOk()->assertJsonPath('data.pipeline_estado.nombre', 'Rechazada');
    }

    public function test_comercial_no_puede_revisar(): void
    {
        $creada = $this->actingAs($this->asesor, 'sanctum')->postJson('/api/solicitudes-credito', [
            'negocio_id'       => $this->negocio->id,
            'monto_solicitado' => 1000000,
        ])->json('data');

        $response = $this->actingAs($this->asesor, 'sanctum')
            ->postJson("/api/solicitudes-credito/{$creada['id']}/decidir", [
                'decision' => 'aprobada',
            ]);

        $response->assertForbidden();
    }

    public function test_comercial_no_ve_solicitud_ajena(): void
    {
        $creada = $this->actingAs($this->asesor, 'sanctum')->postJson('/api/solicitudes-credito', [
            'negocio_id'       => $this->negocio->id,
            'monto_solicitado' => 1000000,
        ])->json('data');

        $otro = User::factory()->create()->assignRole('comercial');

        $response = $this->actingAs($otro, 'sanctum')
            ->getJson("/api/solicitudes-credito/{$creada['id']}");

        $response->assertForbidden();
    }

    public function test_no_puede_decidir_solicitud_ya_finalizada(): void
    {
        $creada = $this->actingAs($this->asesor, 'sanctum')->postJson('/api/solicitudes-credito', [
            'negocio_id'       => $this->negocio->id,
            'monto_solicitado' => 1000000,
        ])->json('data');

        $this->actingAs($this->gerente, 'sanctum')
            ->postJson("/api/solicitudes-credito/{$creada['id']}/decidir", ['decision' => 'aprobada'])
            ->assertOk();

        $response = $this->actingAs($this->gerente, 'sanctum')
            ->postJson("/api/solicitudes-credito/{$creada['id']}/decidir", ['decision' => 'rechazada']);

        $response->assertUnprocessable();
    }

    public function test_sin_autenticacion_retorna_401(): void
    {
        $this->getJson('/api/solicitudes-credito')->assertUnauthorized();
    }
}
