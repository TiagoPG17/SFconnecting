<?php

declare(strict_types=1);

namespace Tests\Unit\SFconnecting\Repositories;

use App\Domain\Clientes\Models\Cliente;
use App\Domain\Clientes\Models\Contacto;
use App\Domain\Clientes\Repositories\ContactoRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ContactoRepositoryTest extends TestCase
{
    use RefreshDatabase;

    private ContactoRepository $repo;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repo = new ContactoRepository();
    }

    public function test_puede_crear_contacto(): void
    {
        $cliente = Cliente::factory()->create();

        $contacto = $this->repo->crear([
            'cliente_id' => $cliente->id,
            'nombre'     => 'Ana GarcÃ­a',
            'cargo'      => 'Gerente',
            'email'      => 'ana@empresa.com',
            'telefono'   => '3001234567',
            'principal'  => false,
        ]);

        $this->assertInstanceOf(Contacto::class, $contacto);
        $this->assertDatabaseHas('contactos', ['nombre' => 'Ana GarcÃ­a', 'cliente_id' => $cliente->id]);
    }

    public function test_puede_actualizar_contacto(): void
    {
        $contacto = Contacto::factory()->create(['nombre' => 'Carlos Viejo']);

        $actualizado = $this->repo->actualizar($contacto, ['nombre' => 'Carlos Nuevo', 'cargo' => 'Director']);

        $this->assertSame('Carlos Nuevo', $actualizado->nombre);
        $this->assertSame('Director', $actualizado->cargo);
        $this->assertDatabaseHas('contactos', ['id' => $contacto->id, 'nombre' => 'Carlos Nuevo']);
    }

    public function test_puede_eliminar_contacto_soft_delete(): void
    {
        $contacto = Contacto::factory()->create();

        $this->repo->eliminar($contacto);

        $this->assertSoftDeleted('contactos', ['id' => $contacto->id]);
    }

    public function test_puede_restaurar_contacto(): void
    {
        $contacto = Contacto::factory()->create();
        $contacto->delete();

        $restaurado = $this->repo->restaurar($contacto->id);

        $this->assertNotNull($restaurado);
        $this->assertNull($restaurado->deleted_at);
        $this->assertDatabaseHas('contactos', ['id' => $contacto->id, 'deleted_at' => null]);
    }

    public function test_puede_buscar_por_id(): void
    {
        $contacto = Contacto::factory()->create();

        $encontrado = $this->repo->buscarPorId($contacto->id);

        $this->assertNotNull($encontrado);
        $this->assertSame($contacto->id, $encontrado->id);
    }

    public function test_buscar_por_id_inexistente_retorna_null(): void
    {
        $resultado = $this->repo->buscarPorId(99999);

        $this->assertNull($resultado);
    }

    public function test_buscar_por_id_no_retorna_eliminados(): void
    {
        $contacto = Contacto::factory()->create();
        $contacto->delete();

        $resultado = $this->repo->buscarPorId($contacto->id);

        $this->assertNull($resultado);
    }

    public function test_puede_listar_por_cliente(): void
    {
        $cliente = Cliente::factory()->create();
        Contacto::factory()->count(3)->create(['cliente_id' => $cliente->id]);
        Contacto::factory()->create();

        $contactos = $this->repo->porCliente($cliente->id);

        $this->assertCount(3, $contactos);
        $contactos->each(fn ($c) => $this->assertSame($cliente->id, $c->cliente_id));
    }

    public function test_listar_por_cliente_no_incluye_eliminados(): void
    {
        $cliente  = Cliente::factory()->create();
        $activo   = Contacto::factory()->create(['cliente_id' => $cliente->id]);
        $eliminado = Contacto::factory()->create(['cliente_id' => $cliente->id]);
        $eliminado->delete();

        $contactos = $this->repo->porCliente($cliente->id);

        $this->assertCount(1, $contactos);
        $this->assertSame($activo->id, $contactos->first()->id);
    }

    public function test_puede_quitar_principal_de_cliente(): void
    {
        $cliente = Cliente::factory()->create();
        Contacto::factory()->create(['cliente_id' => $cliente->id, 'principal' => true]);
        Contacto::factory()->create(['cliente_id' => $cliente->id, 'principal' => true]);

        $this->repo->quitarPrincipalDeCliente($cliente->id);

        $this->assertDatabaseMissing('contactos', ['cliente_id' => $cliente->id, 'principal' => true]);
    }

    public function test_quitar_principal_puede_excluir_un_id(): void
    {
        $cliente    = Cliente::factory()->create();
        $conservar  = Contacto::factory()->create(['cliente_id' => $cliente->id, 'principal' => true]);
        $otro       = Contacto::factory()->create(['cliente_id' => $cliente->id, 'principal' => true]);

        $this->repo->quitarPrincipalDeCliente($cliente->id, $conservar->id);

        $this->assertDatabaseHas('contactos', ['id' => $conservar->id, 'principal' => true]);
        $this->assertDatabaseHas('contactos', ['id' => $otro->id, 'principal' => false]);
    }
}

