<?php

declare(strict_types=1);

namespace Tests\Unit\SFconnecting\Policies;

use App\Domain\Clientes\Models\Cliente;
use App\Domain\Clientes\Policies\ClientePolicy;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ClientePolicyTest extends TestCase
{
    use RefreshDatabase;

    private ClientePolicy $policy;

    protected function setUp(): void
    {
        parent::setUp();
        $this->policy = new ClientePolicy();
    }

    private function usuario(string $rol): User
    {
        $this->crearRol($rol);
        $user = User::factory()->create();
        $user->assignRole($rol);
        return $user;
    }

    // â€” viewAny â€”

    public function test_admin_puede_ver_listado(): void
    {
        $this->assertTrue($this->policy->viewAny($this->usuario('admin')));
    }

    public function test_gerente_puede_ver_listado(): void
    {
        $this->assertTrue($this->policy->viewAny($this->usuario('gerente')));
    }

    public function test_asesor_puede_ver_listado(): void
    {
        $this->assertTrue($this->policy->viewAny($this->usuario('comercial')));
    }

    // â€” view â€”

    public function test_admin_puede_ver_cualquier_cliente(): void
    {
        $admin   = $this->usuario('admin');
        $cliente = Cliente::factory()->create();

        $this->assertTrue($this->policy->view($admin, $cliente));
    }

    public function test_asesor_puede_ver_su_propio_cliente(): void
    {
        $asesor  = $this->usuario('comercial');
        $cliente = Cliente::factory()->create(['user_id' => $asesor->id]);

        $this->assertTrue($this->policy->view($asesor, $cliente));
    }

    public function test_asesor_no_puede_ver_cliente_de_otro(): void
    {
        $asesor  = $this->usuario('comercial');
        $otro    = User::factory()->create();
        $cliente = Cliente::factory()->create(['user_id' => $otro->id]);

        $this->assertFalse($this->policy->view($asesor, $cliente));
    }

    public function test_gerente_puede_ver_cualquier_cliente(): void
    {
        $gerente = $this->usuario('gerente');
        $cliente = Cliente::factory()->create();

        $this->assertTrue($this->policy->view($gerente, $cliente));
    }

    // â€” create â€”

    public function test_admin_puede_crear(): void
    {
        $this->assertTrue($this->policy->create($this->usuario('admin')));
    }

    public function test_asesor_puede_crear(): void
    {
        $this->assertTrue($this->policy->create($this->usuario('comercial')));
    }

    // â€” update â€”

    public function test_admin_puede_editar_cualquier_cliente(): void
    {
        $admin   = $this->usuario('admin');
        $cliente = Cliente::factory()->create();

        $this->assertTrue($this->policy->update($admin, $cliente));
    }

    public function test_asesor_puede_editar_su_cliente(): void
    {
        $asesor  = $this->usuario('comercial');
        $cliente = Cliente::factory()->create(['user_id' => $asesor->id]);

        $this->assertTrue($this->policy->update($asesor, $cliente));
    }

    public function test_asesor_no_puede_editar_cliente_ajeno(): void
    {
        $asesor  = $this->usuario('comercial');
        $otro    = User::factory()->create();
        $cliente = Cliente::factory()->create(['user_id' => $otro->id]);

        $this->assertFalse($this->policy->update($asesor, $cliente));
    }

    // â€” delete â€”

    public function test_admin_puede_eliminar(): void
    {
        $admin   = $this->usuario('admin');
        $cliente = Cliente::factory()->create();

        $this->assertTrue($this->policy->delete($admin, $cliente));
    }

    public function test_asesor_no_puede_eliminar(): void
    {
        $asesor  = $this->usuario('comercial');
        $cliente = Cliente::factory()->create(['user_id' => $asesor->id]);

        $this->assertFalse($this->policy->delete($asesor, $cliente));
    }

    public function test_gerente_no_puede_eliminar(): void
    {
        $gerente = $this->usuario('gerente');
        $cliente = Cliente::factory()->create();

        $this->assertFalse($this->policy->delete($gerente, $cliente));
    }

    // â€” restore â€”

    public function test_admin_puede_restaurar(): void
    {
        $admin   = $this->usuario('admin');
        $cliente = Cliente::factory()->create();

        $this->assertTrue($this->policy->restore($admin, $cliente));
    }

    public function test_asesor_no_puede_restaurar(): void
    {
        $asesor  = $this->usuario('comercial');
        $cliente = Cliente::factory()->create();

        $this->assertFalse($this->policy->restore($asesor, $cliente));
    }

    // â€” forceDelete â€”

    public function test_solo_admin_puede_eliminar_permanentemente(): void
    {
        $admin   = $this->usuario('admin');
        $cliente = Cliente::factory()->create();

        $this->assertTrue($this->policy->forceDelete($admin, $cliente));
    }

    public function test_gerente_no_puede_eliminar_permanentemente(): void
    {
        $gerente = $this->usuario('gerente');
        $cliente = Cliente::factory()->create();

        $this->assertFalse($this->policy->forceDelete($gerente, $cliente));
    }
}

