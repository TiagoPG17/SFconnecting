<?php

declare(strict_types=1);

namespace Tests\Feature\SFconnecting\Negocios;

use App\Domain\Clientes\Models\Cliente;
use App\Domain\Maestros\Models\MaestroComercial;
use App\Domain\Negocios\Models\Negocio;
use App\Domain\Pipeline\Models\PipelineEstado;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class NegocioApiTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private User $asesor;
    private PipelineEstado $estado;
    private PipelineEstado $estadoPerdido;
    private MaestroComercial $motivoPerdida;
    private Cliente $cliente;

    protected function setUp(): void
    {
        parent::setUp();
        app()['cache']->forget('spatie.permission.cache');

        $permisos = ['negocios.ver', 'negocios.crear', 'negocios.editar', 'negocios.eliminar', 'negocios.cerrar'];
        foreach ($permisos as $p) {
            Permission::firstOrCreate(['name' => $p, 'guard_name' => 'web']);
        }

        Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web'])->syncPermissions(Permission::all());
        Role::firstOrCreate(['name' => 'comercial', 'guard_name' => 'web'])->syncPermissions(
            ['negocios.ver', 'negocios.crear', 'negocios.editar']
        );

        $this->admin  = User::factory()->create()->assignRole('admin');
        $this->asesor = User::factory()->create()->assignRole('comercial');
        $this->cliente = Cliente::factory()->create();

        $this->estado = PipelineEstado::create([
            'nombre' => 'Prospectando', 'slug' => 'prosp-api', 'tipo' => 'negocio',
            'color' => '#6B7280', 'orden' => 1, 'porcentaje_cierre' => 10,
            'es_final' => false, 'es_ganado' => false, 'es_perdido' => false, 'activo' => true,
        ]);
        $this->estadoPerdido = PipelineEstado::create([
            'nombre' => 'Perdido', 'slug' => 'perdido-api', 'tipo' => 'negocio',
            'color' => '#EF4444', 'orden' => 6, 'porcentaje_cierre' => 0,
            'es_final' => true, 'es_ganado' => false, 'es_perdido' => true, 'activo' => true,
        ]);
        $this->motivoPerdida = MaestroComercial::create([
            'tipo' => 'motivo_perdida', 'nombre' => 'Precio', 'slug' => 'precio-api', 'orden' => 1, 'activo' => true,
        ]);
    }

    public function test_lista_negocios(): void
    {
        Negocio::create([
            'nombre_negocio'     => 'Negocio Listado',
            'pipeline_estado_id' => $this->estado->id,
            'asesor_id'          => $this->admin->id,
            'cliente_id'         => $this->cliente->id,
            'valor_estimado'     => 10000,
            'activo'             => true,
        ]);

        $response = $this->actingAs($this->admin, 'sanctum')
            ->getJson('/api/negocios');

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.meta.total', 1);
    }

    public function test_crea_negocio(): void
    {
        $response = $this->actingAs($this->admin, 'sanctum')
            ->postJson('/api/negocios', [
                'nombre_negocio'     => 'Negocio Nuevo',
                'pipeline_estado_id' => $this->estado->id,
                'cliente_id'         => $this->cliente->id,
                'valor_estimado'     => 75000,
            ]);

        $response->assertCreated()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.nombre_negocio', 'Negocio Nuevo');

        $this->assertDatabaseHas('sf_negocios', ['nombre_negocio' => 'Negocio Nuevo']);
    }

    public function test_muestra_negocio(): void
    {
        $negocio = Negocio::create([
            'nombre_negocio'     => 'Negocio Show',
            'pipeline_estado_id' => $this->estado->id,
            'asesor_id'          => $this->admin->id,
            'cliente_id'         => $this->cliente->id,
            'valor_estimado'     => 10000,
            'activo'             => true,
        ]);

        $response = $this->actingAs($this->admin, 'sanctum')
            ->getJson("/api/negocios/{$negocio->id}");

        $response->assertOk()->assertJsonPath('data.nombre_negocio', 'Negocio Show');
    }

    public function test_actualiza_negocio(): void
    {
        $negocio = Negocio::create([
            'nombre_negocio'     => 'Original',
            'pipeline_estado_id' => $this->estado->id,
            'asesor_id'          => $this->admin->id,
            'cliente_id'         => $this->cliente->id,
            'valor_estimado'     => 10000,
            'activo'             => true,
        ]);

        $response = $this->actingAs($this->admin, 'sanctum')
            ->putJson("/api/negocios/{$negocio->id}", [
                'nombre_negocio' => 'Actualizado',
                'valor_estimado' => 50000,
            ]);

        $response->assertOk()->assertJsonPath('data.nombre_negocio', 'Actualizado');
    }

    public function test_mover_a_perdido_requiere_motivo(): void
    {
        $negocio = Negocio::create([
            'nombre_negocio'     => 'Sin Motivo',
            'pipeline_estado_id' => $this->estado->id,
            'asesor_id'          => $this->admin->id,
            'cliente_id'         => $this->cliente->id,
            'valor_estimado'     => 10000,
            'activo'             => true,
        ]);

        $response = $this->actingAs($this->admin, 'sanctum')
            ->putJson("/api/negocios/{$negocio->id}", [
                'pipeline_estado_id' => $this->estadoPerdido->id,
            ]);

        $response->assertUnprocessable();
    }

    public function test_mover_a_perdido_con_motivo(): void
    {
        $negocio = Negocio::create([
            'nombre_negocio'     => 'Con Motivo',
            'pipeline_estado_id' => $this->estado->id,
            'asesor_id'          => $this->admin->id,
            'cliente_id'         => $this->cliente->id,
            'valor_estimado'     => 10000,
            'activo'             => true,
        ]);

        $response = $this->actingAs($this->admin, 'sanctum')
            ->putJson("/api/negocios/{$negocio->id}", [
                'pipeline_estado_id' => $this->estadoPerdido->id,
                'motivo_perdida_id'  => $this->motivoPerdida->id,
            ]);

        $response->assertOk();
    }

    public function test_elimina_negocio(): void
    {
        $negocio = Negocio::create([
            'nombre_negocio'     => 'A Eliminar',
            'pipeline_estado_id' => $this->estado->id,
            'asesor_id'          => $this->admin->id,
            'cliente_id'         => $this->cliente->id,
            'valor_estimado'     => 10000,
            'activo'             => true,
        ]);

        $response = $this->actingAs($this->admin, 'sanctum')
            ->deleteJson("/api/negocios/{$negocio->id}");

        $response->assertOk();
        $this->assertSoftDeleted('sf_negocios', ['id' => $negocio->id]);
    }

    public function test_kanban_devuelve_estructura_correcta(): void
    {
        $response = $this->actingAs($this->admin, 'sanctum')
            ->getJson('/api/negocios/kanban');

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonStructure(['data' => [['estado', 'negocios', 'total', 'valor_total', 'valor_forecast']]]);
    }

    public function test_forecast_devuelve_metricas(): void
    {
        $response = $this->actingAs($this->admin, 'sanctum')
            ->getJson('/api/negocios/forecast');

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonStructure(['data' => ['total_pipeline', 'total_forecast', 'cantidad', 'por_estado']]);
    }

    public function test_asesor_no_puede_ver_negocio_ajeno(): void
    {
        $otro    = User::factory()->create()->assignRole('comercial');
        $negocio = Negocio::create([
            'nombre_negocio'     => 'Ajeno',
            'pipeline_estado_id' => $this->estado->id,
            'asesor_id'          => $otro->id,
            'cliente_id'         => $this->cliente->id,
            'valor_estimado'     => 10000,
            'activo'             => true,
        ]);

        $response = $this->actingAs($this->asesor, 'sanctum')
            ->getJson("/api/negocios/{$negocio->id}");

        $response->assertForbidden();
    }

    public function test_sin_autenticacion_retorna_401(): void
    {
        $this->getJson('/api/negocios')->assertUnauthorized();
    }

    public function test_validacion_falla_sin_nombre(): void
    {
        $response = $this->actingAs($this->admin, 'sanctum')
            ->postJson('/api/negocios', ['cliente_id' => $this->cliente->id]);

        $response->assertUnprocessable();
    }
}

