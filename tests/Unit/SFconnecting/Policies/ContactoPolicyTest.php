<?php

declare(strict_types=1);

namespace Tests\Unit\SFconnecting\Policies;

use App\Domain\Clientes\Models\Cliente;
use App\Domain\Clientes\Models\Contacto;
use App\Domain\Clientes\Policies\ContactoPolicy;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ContactoPolicyTest extends TestCase
{
    use RefreshDatabase;

    private ContactoPolicy $policy;

    protected function setUp(): void
    {
        parent::setUp();
        $this->policy = new ContactoPolicy();
    }

    private function usuario(string $rol): User
    {
        $this->crearRol($rol);
        $user = User::factory()->create();
        $user->assignRole($rol);
        return $user;
    }

    private function contactoDeCliente(?User $propietario = null): Contacto
    {
        $owner    = $propietario ?? User::factory()->create();
        $cliente  = Cliente::factory()->create(['user_id' => $owner->id]);
        $contacto = Contacto::factory()->make(['cliente_id' => $cliente->id]);
        $contacto->setRelation('cliente', $cliente);
        return $contacto;
    }

    // â€” viewAny (recibe el Cliente padre) â€”

    public function test_admin_puede_ver_contactos_de_cualquier_cliente(): void
    {
        $admin   = $this->usuario('admin');
        $cliente = Cliente::factory()->create();

        $this->assertTrue($this->policy->viewAny($admin, $cliente));
    }

    public function test_gerente_puede_ver_contactos_de_cualquier_cliente(): void
    {
        $gerente = $this->usuario('gerente');
        $cliente = Cliente::factory()->create();

        $this->assertTrue($this->policy->viewAny($gerente, $cliente));
    }

    public function test_asesor_puede_ver_contactos_de_su_cliente(): void
    {
        $asesor  = $this->usuario('comercial');
        $cliente = Cliente::factory()->create(['user_id' => $asesor->id]);

        $this->assertTrue($this->policy->viewAny($asesor, $cliente));
    }

    public function test_asesor_no_puede_ver_contactos_de_cliente_ajeno(): void
    {
        $asesor  = $this->usuario('comercial');
        $cliente = Cliente::factory()->create();

        $this->assertFalse($this->policy->viewAny($asesor, $cliente));
    }

    // â€” view â€”

    public function test_admin_puede_ver_cualquier_contacto(): void
    {
        $admin    = $this->usuario('admin');
        $contacto = $this->contactoDeCliente();

        $this->assertTrue($this->policy->view($admin, $contacto));
    }

    public function test_gerente_puede_ver_cualquier_contacto(): void
    {
        $gerente  = $this->usuario('gerente');
        $contacto = $this->contactoDeCliente();

        $this->assertTrue($this->policy->view($gerente, $contacto));
    }

    public function test_asesor_puede_ver_contacto_de_su_cliente(): void
    {
        $asesor   = $this->usuario('comercial');
        $contacto = $this->contactoDeCliente($asesor);

        $this->assertTrue($this->policy->view($asesor, $contacto));
    }

    public function test_asesor_no_puede_ver_contacto_de_cliente_ajeno(): void
    {
        $asesor   = $this->usuario('comercial');
        $contacto = $this->contactoDeCliente();

        $this->assertFalse($this->policy->view($asesor, $contacto));
    }

    // â€” create (recibe el Cliente padre) â€”

    public function test_admin_puede_crear_contacto_en_cualquier_cliente(): void
    {
        $admin   = $this->usuario('admin');
        $cliente = Cliente::factory()->create();

        $this->assertTrue($this->policy->create($admin, $cliente));
    }

    public function test_gerente_no_puede_crear_contacto(): void
    {
        $gerente = $this->usuario('gerente');
        $cliente = Cliente::factory()->create();

        $this->assertFalse($this->policy->create($gerente, $cliente));
    }

    public function test_asesor_puede_crear_contacto_en_su_cliente(): void
    {
        $asesor  = $this->usuario('comercial');
        $cliente = Cliente::factory()->create(['user_id' => $asesor->id]);

        $this->assertTrue($this->policy->create($asesor, $cliente));
    }

    public function test_asesor_no_puede_crear_contacto_en_cliente_ajeno(): void
    {
        $asesor  = $this->usuario('comercial');
        $cliente = Cliente::factory()->create();

        $this->assertFalse($this->policy->create($asesor, $cliente));
    }

    // â€” update â€”

    public function test_admin_puede_actualizar_cualquier_contacto(): void
    {
        $admin    = $this->usuario('admin');
        $contacto = $this->contactoDeCliente();

        $this->assertTrue($this->policy->update($admin, $contacto));
    }

    public function test_asesor_puede_actualizar_contacto_de_su_cliente(): void
    {
        $asesor   = $this->usuario('comercial');
        $contacto = $this->contactoDeCliente($asesor);

        $this->assertTrue($this->policy->update($asesor, $contacto));
    }

    public function test_asesor_no_puede_actualizar_contacto_de_cliente_ajeno(): void
    {
        $asesor   = $this->usuario('comercial');
        $contacto = $this->contactoDeCliente();

        $this->assertFalse($this->policy->update($asesor, $contacto));
    }

    // â€” delete â€”

    public function test_admin_puede_eliminar_contacto(): void
    {
        $admin    = $this->usuario('admin');
        $contacto = $this->contactoDeCliente();

        $this->assertTrue($this->policy->delete($admin, $contacto));
    }

    public function test_gerente_no_puede_eliminar_contacto(): void
    {
        $gerente  = $this->usuario('gerente');
        $contacto = $this->contactoDeCliente();

        $this->assertFalse($this->policy->delete($gerente, $contacto));
    }

    public function test_asesor_puede_eliminar_contacto_de_su_cliente(): void
    {
        $asesor   = $this->usuario('comercial');
        $contacto = $this->contactoDeCliente($asesor);

        $this->assertTrue($this->policy->delete($asesor, $contacto));
    }

    public function test_asesor_no_puede_eliminar_contacto_de_cliente_ajeno(): void
    {
        $asesor   = $this->usuario('comercial');
        $contacto = $this->contactoDeCliente();

        $this->assertFalse($this->policy->delete($asesor, $contacto));
    }

    // â€” restore â€”

    public function test_admin_puede_restaurar_contacto(): void
    {
        $admin    = $this->usuario('admin');
        $contacto = $this->contactoDeCliente();

        $this->assertTrue($this->policy->restore($admin, $contacto));
    }

    public function test_gerente_no_puede_restaurar_contacto(): void
    {
        $gerente  = $this->usuario('gerente');
        $contacto = $this->contactoDeCliente();

        $this->assertFalse($this->policy->restore($gerente, $contacto));
    }

    public function test_asesor_no_puede_restaurar_contacto(): void
    {
        $asesor   = $this->usuario('comercial');
        $contacto = $this->contactoDeCliente($asesor);

        $this->assertFalse($this->policy->restore($asesor, $contacto));
    }
}

