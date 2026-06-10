<?php

declare(strict_types=1);

namespace Tests\Feature\SFconnecting\Maestros;

use App\Domain\Maestros\Models\MaestroComercial;
use App\Domain\Pipeline\Models\PipelineEstado;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MaestroGestionApiTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        $this->crearRol('admin');
        $this->crearPermiso('maestros.gestionar');
        $user = User::factory()->create();
        $user->assignRole('admin');
        $user->givePermissionTo('maestros.gestionar');
        return $user;
    }

    private function asesor(): User
    {
        $this->crearRol('comercial');
        $user = User::factory()->create();
        $user->assignRole('comercial');
        return $user;
    }

    // â”€â”€â”€ Maestros comerciales â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€

    public function test_asesor_no_puede_crear_maestro(): void
    {
        $this->actingAs($this->asesor())
            ->postJson('/api/maestros/comercial', ['nombre' => 'Nuevo', 'tipo' => 'prioridad'])
            ->assertForbidden();
    }

    public function test_admin_puede_crear_maestro(): void
    {
        $this->actingAs($this->admin())
            ->postJson('/api/maestros/comercial', [
                'nombre' => 'VIP',
                'tipo'   => 'clasificacion',
                'color'  => '#6366f1',
            ])
            ->assertCreated()
            ->assertJson(['success' => true]);

        $this->assertDatabaseHas('sf_maestros_comerciales', ['nombre' => 'VIP', 'tipo' => 'clasificacion']);
    }

    public function test_crear_maestro_genera_slug_automatico(): void
    {
        $this->actingAs($this->admin())
            ->postJson('/api/maestros/comercial', [
                'nombre' => 'Alto Valor',
                'tipo'   => 'segmento',
            ])
            ->assertCreated();

        $this->assertDatabaseHas('sf_maestros_comerciales', ['slug' => 'alto-valor']);
    }

    public function test_admin_puede_actualizar_maestro(): void
    {
        $maestro = MaestroComercial::create(['nombre' => 'Viejo', 'tipo' => 'prioridad', 'slug' => 'viejo', 'orden' => 1, 'activo' => true]);

        $this->actingAs($this->admin())
            ->putJson("/api/maestros/comercial/{$maestro->id}", [
                'nombre' => 'Nuevo Nombre',
                'tipo'   => $maestro->tipo,
                'color'  => '#10b981',
            ])
            ->assertOk()
            ->assertJson(['success' => true]);

        $this->assertDatabaseHas('sf_maestros_comerciales', ['id' => $maestro->id, 'nombre' => 'Nuevo Nombre']);
    }

    public function test_admin_puede_toggle_maestro(): void
    {
        $maestro = MaestroComercial::create(['nombre' => 'Toggle', 'tipo' => 'prioridad', 'slug' => 'toggle', 'orden' => 1, 'activo' => true]);

        $this->actingAs($this->admin())
            ->patchJson("/api/maestros/comercial/{$maestro->id}/toggle")
            ->assertOk()
            ->assertJson(['success' => true]);

        $this->assertDatabaseHas('sf_maestros_comerciales', ['id' => $maestro->id, 'activo' => false]);
    }

    public function test_nombre_requerido_al_crear_maestro(): void
    {
        $this->actingAs($this->admin())
            ->postJson('/api/maestros/comercial', ['tipo' => 'prioridad'])
            ->assertUnprocessable();
    }

    // â”€â”€â”€ Pipeline estados â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€

    public function test_admin_puede_crear_estado_pipeline(): void
    {
        $this->actingAs($this->admin())
            ->postJson('/api/maestros/pipeline-estado', [
                'nombre'            => 'En revisiÃ³n',
                'tipo'              => 'negocio',
                'color'             => '#f59e0b',
                'porcentaje_cierre' => 40,
            ])
            ->assertCreated()
            ->assertJson(['success' => true]);

        $this->assertDatabaseHas('sf_pipeline_estados', ['nombre' => 'En revisiÃ³n', 'tipo' => 'negocio']);
    }

    public function test_crear_estado_pipeline_genera_slug(): void
    {
        $this->actingAs($this->admin())
            ->postJson('/api/maestros/pipeline-estado', [
                'nombre'            => 'Propuesta Enviada',
                'tipo'              => 'negocio',
                'color'             => '#3b82f6',
                'porcentaje_cierre' => 60,
            ])
            ->assertCreated();

        $this->assertDatabaseHas('sf_pipeline_estados', ['slug' => 'propuesta-enviada']);
    }

    public function test_admin_puede_actualizar_estado_pipeline(): void
    {
        $estado = PipelineEstado::create(['nombre' => 'Viejo', 'slug' => 'viejo', 'tipo' => 'negocio', 'color' => '#aaa', 'porcentaje_cierre' => 50, 'orden' => 1, 'activo' => true]);

        $this->actingAs($this->admin())
            ->putJson("/api/maestros/pipeline-estado/{$estado->id}", [
                'nombre'            => 'Actualizado',
                'tipo'              => 'negocio',
                'color'             => '#ef4444',
                'porcentaje_cierre' => 75,
            ])
            ->assertOk();

        $this->assertDatabaseHas('sf_pipeline_estados', ['id' => $estado->id, 'nombre' => 'Actualizado']);
    }

    public function test_admin_puede_toggle_estado_pipeline(): void
    {
        $estado = PipelineEstado::create(['nombre' => 'Toggle', 'slug' => 'toggle-p', 'tipo' => 'negocio', 'color' => '#bbb', 'porcentaje_cierre' => 30, 'orden' => 2, 'activo' => true]);

        $this->actingAs($this->admin())
            ->patchJson("/api/maestros/pipeline-estado/{$estado->id}/toggle")
            ->assertOk();

        $this->assertDatabaseHas('sf_pipeline_estados', ['id' => $estado->id, 'activo' => false]);
    }

    public function test_porcentaje_cierre_debe_ser_entre_0_y_100(): void
    {
        $this->actingAs($this->admin())
            ->postJson('/api/maestros/pipeline-estado', [
                'nombre'            => 'Test',
                'tipo'              => 'negocio',
                'color'             => '#000000',
                'porcentaje_cierre' => 150,
            ])
            ->assertUnprocessable();
    }

    public function test_tipo_pipeline_invalido_retorna_422(): void
    {
        $this->actingAs($this->admin())
            ->postJson('/api/maestros/pipeline-estado', [
                'nombre'            => 'Test',
                'tipo'              => 'factura',
                'color'             => '#000000',
                'porcentaje_cierre' => 50,
            ])
            ->assertUnprocessable();
    }
}

