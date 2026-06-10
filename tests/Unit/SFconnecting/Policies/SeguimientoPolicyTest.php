<?php

declare(strict_types=1);

namespace Tests\Unit\SFconnecting\Policies;

use App\Domain\Seguimientos\Models\Seguimiento;
use App\Domain\Seguimientos\Policies\SeguimientoPolicy;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SeguimientoPolicyTest extends TestCase
{
    use RefreshDatabase;

    private SeguimientoPolicy $policy;

    protected function setUp(): void
    {
        parent::setUp();
        $this->policy = new SeguimientoPolicy();
    }

    private function usuario(string $rol): User
    {
        $this->crearRol($rol);
        $user = User::factory()->create();
        $user->assignRole($rol);
        return $user;
    }

    private function seguimientoDeAsesor(User $asesor): Seguimiento
    {
        $s          = Seguimiento::factory()->make();
        $s->user_id = $asesor->id;
        return $s;
    }

    private function seguimientoAjeno(): Seguimiento
    {
        $otro       = User::factory()->create();
        $s          = Seguimiento::factory()->make();
        $s->user_id = $otro->id;
        return $s;
    }

    // â€” viewAny â€”

    public function test_admin_puede_listar_seguimientos(): void
    {
        $this->assertTrue($this->policy->viewAny($this->usuario('admin')));
    }

    public function test_gerente_puede_listar_seguimientos(): void
    {
        $this->assertTrue($this->policy->viewAny($this->usuario('gerente')));
    }

    public function test_asesor_puede_listar_seguimientos(): void
    {
        $this->assertTrue($this->policy->viewAny($this->usuario('comercial')));
    }

    // â€” view â€”

    public function test_admin_puede_ver_cualquier_seguimiento(): void
    {
        $this->assertTrue($this->policy->view($this->usuario('admin'), $this->seguimientoAjeno()));
    }

    public function test_gerente_puede_ver_cualquier_seguimiento(): void
    {
        $this->assertTrue($this->policy->view($this->usuario('gerente'), $this->seguimientoAjeno()));
    }

    public function test_asesor_puede_ver_cualquier_seguimiento(): void
    {
        $asesor = $this->usuario('comercial');
        $this->assertTrue($this->policy->view($asesor, $this->seguimientoAjeno()));
    }

    // â€” create â€”

    public function test_admin_puede_crear_seguimiento(): void
    {
        $this->assertTrue($this->policy->create($this->usuario('admin')));
    }

    public function test_gerente_no_puede_crear_seguimiento(): void
    {
        $this->assertFalse($this->policy->create($this->usuario('gerente')));
    }

    public function test_asesor_puede_crear_seguimiento(): void
    {
        $this->assertTrue($this->policy->create($this->usuario('comercial')));
    }

    // â€” delete â€”

    public function test_admin_puede_eliminar_cualquier_seguimiento(): void
    {
        $this->assertTrue($this->policy->delete($this->usuario('admin'), $this->seguimientoAjeno()));
    }

    public function test_gerente_no_puede_eliminar_seguimiento(): void
    {
        $this->assertFalse($this->policy->delete($this->usuario('gerente'), $this->seguimientoAjeno()));
    }

    public function test_asesor_puede_eliminar_su_seguimiento(): void
    {
        $asesor = $this->usuario('comercial');
        $this->assertTrue($this->policy->delete($asesor, $this->seguimientoDeAsesor($asesor)));
    }

    public function test_asesor_no_puede_eliminar_seguimiento_ajeno(): void
    {
        $asesor = $this->usuario('comercial');
        $this->assertFalse($this->policy->delete($asesor, $this->seguimientoAjeno()));
    }
}

