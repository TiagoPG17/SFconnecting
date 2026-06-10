<?php

declare(strict_types=1);

namespace Tests\Feature\SFconnecting\Prospectos;

use App\Domain\Pipeline\Models\PipelineEstado;
use App\Domain\Prospectos\Models\Prospecto;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class ProspectoApiTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private User $asesor;
    private PipelineEstado $estado;

    protected function setUp(): void
    {
        parent::setUp();
        app()['cache']->forget('spatie.permission.cache');

        $permisos = ['prospectos.ver', 'prospectos.crear', 'prospectos.editar', 'prospectos.eliminar', 'prospectos.convertir'];
        foreach ($permisos as $p) {
            Permission::firstOrCreate(['name' => $p, 'guard_name' => 'web']);
        }

        Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web'])->syncPermissions(Permission::all());
        Role::firstOrCreate(['name' => 'comercial', 'guard_name' => 'web'])->syncPermissions(
            ['prospectos.ver', 'prospectos.crear', 'prospectos.editar']
        );

        $this->admin  = User::factory()->create()->assignRole('admin');
        $this->asesor = User::factory()->create()->assignRole('comercial');

        $this->estado = PipelineEstado::create([
            'nombre' => 'Nuevo Lead', 'slug' => 'nuevo-lead-api', 'tipo' => 'prospecto',
            'color' => '#6B7280', 'orden' => 1, 'porcentaje_cierre' => 5,
            'es_final' => false, 'es_ganado' => false, 'es_perdido' => false, 'activo' => true,
        ]);
    }

    public function test_lista_prospectos(): void
    {
        Prospecto::create([
            'codigo' => 'PROS-00001', 'empresa' => 'Empresa Uno', 'contacto' => 'Juan',
            'estado_pipeline_id' => $this->estado->id, 'asesor_id' => $this->admin->id, 'activo' => true,
        ]);

        $response = $this->actingAs($this->admin, 'sanctum')
            ->getJson('/api/prospectos');

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.meta.total', 1);
    }

    public function test_crea_prospecto(): void
    {
        $response = $this->actingAs($this->admin, 'sanctum')
            ->postJson('/api/prospectos', [
                'empresa'           => 'Empresa Nueva',
                'contacto'          => 'Ana GarcÃ­a',
                'estado_pipeline_id' => $this->estado->id,
                'email'             => 'ana@empresa.co',
            ]);

        $response->assertCreated()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.empresa', 'Empresa Nueva');

        $this->assertDatabaseHas('sf_prospectos', ['empresa' => 'Empresa Nueva']);
    }

    public function test_muestra_prospecto(): void
    {
        $prospecto = Prospecto::create([
            'codigo' => 'PROS-00001', 'empresa' => 'Empresa Show', 'contacto' => 'Juan',
            'estado_pipeline_id' => $this->estado->id, 'asesor_id' => $this->admin->id, 'activo' => true,
        ]);

        $response = $this->actingAs($this->admin, 'sanctum')
            ->getJson("/api/prospectos/{$prospecto->id}");

        $response->assertOk()->assertJsonPath('data.empresa', 'Empresa Show');
    }

    public function test_actualiza_prospecto(): void
    {
        $prospecto = Prospecto::create([
            'codigo' => 'PROS-00001', 'empresa' => 'Original', 'contacto' => 'Juan',
            'estado_pipeline_id' => $this->estado->id, 'asesor_id' => $this->admin->id, 'activo' => true,
        ]);

        $response = $this->actingAs($this->admin, 'sanctum')
            ->putJson("/api/prospectos/{$prospecto->id}", ['empresa' => 'Actualizada']);

        $response->assertOk()->assertJsonPath('data.empresa', 'Actualizada');
    }

    public function test_elimina_prospecto(): void
    {
        $prospecto = Prospecto::create([
            'codigo' => 'PROS-00001', 'empresa' => 'A Eliminar', 'contacto' => 'Juan',
            'estado_pipeline_id' => $this->estado->id, 'asesor_id' => $this->admin->id, 'activo' => true,
        ]);

        $response = $this->actingAs($this->admin, 'sanctum')
            ->deleteJson("/api/prospectos/{$prospecto->id}");

        $response->assertOk();
        $this->assertSoftDeleted('sf_prospectos', ['id' => $prospecto->id]);
    }

    public function test_asesor_no_puede_ver_prospecto_ajeno(): void
    {
        $otroAsesor = User::factory()->create()->assignRole('comercial');
        $prospecto  = Prospecto::create([
            'codigo' => 'PROS-00001', 'empresa' => 'Ajena', 'contacto' => 'Juan',
            'estado_pipeline_id' => $this->estado->id, 'asesor_id' => $otroAsesor->id, 'activo' => true,
        ]);

        $response = $this->actingAs($this->asesor, 'sanctum')
            ->getJson("/api/prospectos/{$prospecto->id}");

        $response->assertForbidden();
    }

    public function test_usuario_sin_autenticacion_recibe_401(): void
    {
        $this->getJson('/api/prospectos')->assertUnauthorized();
    }

    public function test_validacion_falla_sin_empresa(): void
    {
        $response = $this->actingAs($this->admin, 'sanctum')
            ->postJson('/api/prospectos', ['contacto' => 'Juan']);

        $response->assertUnprocessable();
    }
}

