<?php

declare(strict_types=1);

namespace Tests\Unit\SFconnecting\Policies;

use App\Domain\Clientes\Models\Cliente;
use App\Domain\Negocios\Models\Negocio;
use App\Domain\Negocios\Policies\NegocioPolicy;
use App\Domain\Pipeline\Models\PipelineEstado;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class NegocioPolicyTest extends TestCase
{
    use RefreshDatabase;

    private NegocioPolicy $policy;
    private PipelineEstado $estado;

    protected function setUp(): void
    {
        parent::setUp();
        app()['cache']->forget('spatie.permission.cache');

        $this->policy = new NegocioPolicy();
        $this->estado = PipelineEstado::create([
            'nombre' => 'Test', 'slug' => 'test-neg', 'tipo' => 'negocio',
            'color' => '#000', 'orden' => 1, 'porcentaje_cierre' => 0,
            'es_final' => false, 'es_ganado' => false, 'es_perdido' => false, 'activo' => true,
        ]);

        $permisos = ['negocios.ver', 'negocios.crear', 'negocios.editar', 'negocios.eliminar', 'negocios.cerrar'];
        foreach ($permisos as $p) {
            Permission::firstOrCreate(['name' => $p, 'guard_name' => 'web']);
        }

        Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web'])->syncPermissions(Permission::all());
        Role::firstOrCreate(['name' => 'gerente', 'guard_name' => 'web'])->syncPermissions(['negocios.ver']);
        Role::firstOrCreate(['name' => 'comercial', 'guard_name' => 'web'])->syncPermissions(
            ['negocios.ver', 'negocios.crear', 'negocios.editar']
        );
    }

    public function test_admin_puede_ver_cualquier_negocio(): void
    {
        $admin   = User::factory()->create()->assignRole('admin');
        $negocio = $this->crearNegocio(User::factory()->create()->id);

        $this->assertTrue($this->policy->view($admin, $negocio));
    }

    public function test_asesor_solo_ve_sus_negocios(): void
    {
        $asesor  = User::factory()->create()->assignRole('comercial');
        $otro    = User::factory()->create();
        $propio  = $this->crearNegocio($asesor->id);
        $ajeno   = $this->crearNegocio($otro->id);

        $this->assertTrue($this->policy->view($asesor, $propio));
        $this->assertFalse($this->policy->view($asesor, $ajeno));
    }

    public function test_asesor_no_puede_eliminar(): void
    {
        $asesor  = User::factory()->create()->assignRole('comercial');
        $negocio = $this->crearNegocio($asesor->id);

        $this->assertFalse($this->policy->delete($asesor, $negocio));
    }

    public function test_gerente_no_puede_cerrar_negocio(): void
    {
        $gerente = User::factory()->create()->assignRole('gerente');
        $negocio = $this->crearNegocio($gerente->id);

        $this->assertFalse($this->policy->cerrar($gerente, $negocio));
    }

    public function test_asesor_no_puede_cerrar_negocio(): void
    {
        $asesor  = User::factory()->create()->assignRole('comercial');
        $negocio = $this->crearNegocio($asesor->id);

        $this->assertFalse($this->policy->cerrar($asesor, $negocio));
    }

    public function test_admin_puede_todo(): void
    {
        $admin   = User::factory()->create()->assignRole('admin');
        $negocio = $this->crearNegocio($admin->id);

        $this->assertTrue($this->policy->viewAny($admin));
        $this->assertTrue($this->policy->view($admin, $negocio));
        $this->assertTrue($this->policy->create($admin));
        $this->assertTrue($this->policy->update($admin, $negocio));
        $this->assertTrue($this->policy->delete($admin, $negocio));
        $this->assertTrue($this->policy->cerrar($admin, $negocio));
    }

    private function crearNegocio(int $asesorId): Negocio
    {
        $cliente = Cliente::factory()->create();

        return Negocio::create([
            'nombre_negocio'     => 'Negocio Test',
            'pipeline_estado_id' => $this->estado->id,
            'asesor_id'          => $asesorId,
            'cliente_id'         => $cliente->id,
            'valor_estimado'     => 10000,
            'activo'             => true,
        ]);
    }
}

