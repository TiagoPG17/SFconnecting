<?php

declare(strict_types=1);

namespace Tests\Unit\SFconnecting\Policies;

use App\Domain\Pipeline\Models\PipelineEstado;
use App\Domain\Prospectos\Models\Prospecto;
use App\Domain\Prospectos\Policies\ProspectoPolicy;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class ProspectoPolicyTest extends TestCase
{
    use RefreshDatabase;

    private ProspectoPolicy $policy;
    private PipelineEstado $estado;

    protected function setUp(): void
    {
        parent::setUp();
        app()['cache']->forget('spatie.permission.cache');

        $this->policy = new ProspectoPolicy();
        $this->estado = PipelineEstado::create([
            'nombre' => 'Test', 'slug' => 'test', 'tipo' => 'prospecto',
            'color' => '#000', 'orden' => 1, 'porcentaje_cierre' => 0,
            'es_final' => false, 'es_ganado' => false, 'es_perdido' => false, 'activo' => true,
        ]);

        $permisos = ['prospectos.ver', 'prospectos.crear', 'prospectos.editar', 'prospectos.eliminar', 'prospectos.convertir'];
        foreach ($permisos as $p) {
            Permission::firstOrCreate(['name' => $p, 'guard_name' => 'web']);
        }

        Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web'])->syncPermissions(Permission::all());
        Role::firstOrCreate(['name' => 'gerente', 'guard_name' => 'web'])->syncPermissions(['prospectos.ver']);
        Role::firstOrCreate(['name' => 'comercial', 'guard_name' => 'web'])->syncPermissions(
            ['prospectos.ver', 'prospectos.crear', 'prospectos.editar']
        );
    }

    public function test_admin_puede_ver_cualquier_prospecto(): void
    {
        $admin     = User::factory()->create()->assignRole('admin');
        $prospecto = $this->crearProspecto(User::factory()->create()->id);

        $this->assertTrue($this->policy->view($admin, $prospecto));
    }

    public function test_asesor_solo_puede_ver_sus_prospectos(): void
    {
        $asesor    = User::factory()->create()->assignRole('comercial');
        $otro      = User::factory()->create();
        $propio    = $this->crearProspecto($asesor->id);
        $ajeno     = $this->crearProspecto($otro->id);

        $this->assertTrue($this->policy->view($asesor, $propio));
        $this->assertFalse($this->policy->view($asesor, $ajeno));
    }

    public function test_asesor_no_puede_eliminar(): void
    {
        $asesor    = User::factory()->create()->assignRole('comercial');
        $prospecto = $this->crearProspecto($asesor->id);

        $this->assertFalse($this->policy->delete($asesor, $prospecto));
    }

    public function test_gerente_no_puede_eliminar(): void
    {
        $gerente   = User::factory()->create()->assignRole('gerente');
        $prospecto = $this->crearProspecto($gerente->id);

        $this->assertFalse($this->policy->delete($gerente, $prospecto));
    }

    public function test_asesor_sin_permiso_convertir_no_puede_convertir(): void
    {
        $asesor    = User::factory()->create()->assignRole('comercial');
        $prospecto = $this->crearProspecto($asesor->id);

        $this->assertFalse($this->policy->convertir($asesor, $prospecto));
    }

    public function test_gerente_no_puede_convertir(): void
    {
        $gerente   = User::factory()->create()->assignRole('gerente');
        $prospecto = $this->crearProspecto($gerente->id);

        $this->assertFalse($this->policy->convertir($gerente, $prospecto));
    }

    public function test_admin_puede_todo(): void
    {
        $admin     = User::factory()->create()->assignRole('admin');
        $prospecto = $this->crearProspecto($admin->id);

        $this->assertTrue($this->policy->viewAny($admin));
        $this->assertTrue($this->policy->view($admin, $prospecto));
        $this->assertTrue($this->policy->create($admin));
        $this->assertTrue($this->policy->update($admin, $prospecto));
        $this->assertTrue($this->policy->delete($admin, $prospecto));
        $this->assertTrue($this->policy->convertir($admin, $prospecto));
    }

    private function crearProspecto(int $asesorId): Prospecto
    {
        return Prospecto::create([
            'codigo'             => 'PROS-' . rand(1000, 9999),
            'empresa'            => 'Empresa Test',
            'contacto'           => 'Contacto',
            'estado_pipeline_id' => $this->estado->id,
            'asesor_id'          => $asesorId,
            'activo'             => true,
        ]);
    }
}

