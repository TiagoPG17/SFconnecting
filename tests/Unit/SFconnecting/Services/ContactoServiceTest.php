<?php

declare(strict_types=1);

namespace Tests\Unit\SFconnecting\Services;

use App\Domain\Clientes\DTOs\ActualizarContactoDTO;
use App\Domain\Clientes\DTOs\CrearContactoDTO;
use App\Domain\Clientes\Models\Cliente;
use App\Domain\Clientes\Models\Contacto;
use App\Domain\Clientes\Repositories\ContactoRepositoryInterface;
use App\Domain\Clientes\Services\ContactoService;
use Illuminate\Database\Eloquent\Collection;
use PHPUnit\Framework\MockObject\MockObject;
use Tests\TestCase;

class ContactoServiceTest extends TestCase
{
    private ContactoService $service;

    /** @var ContactoRepositoryInterface&MockObject */
    private ContactoRepositoryInterface $repo;

    protected function setUp(): void
    {
        parent::setUp();

        $this->repo    = $this->createMock(ContactoRepositoryInterface::class);
        $this->service = new ContactoService($this->repo);
    }

    public function test_crear_contacto_no_principal_no_afecta_otros(): void
    {
        $dto = new CrearContactoDTO(
            clienteId: 1,
            nombre: 'MarÃ­a LÃ³pez',
            cargo: 'Analista',
            email: null,
            telefono: null,
            principal: false,
        );

        $this->repo->expects($this->never())->method('quitarPrincipalDeCliente');
        $this->repo->expects($this->once())->method('crear')->willReturn(new Contacto());

        $this->service->crear($dto);
    }

    public function test_crear_contacto_principal_desmarca_anterior(): void
    {
        $dto = new CrearContactoDTO(
            clienteId: 5,
            nombre: 'Pedro Ruiz',
            cargo: null,
            email: null,
            telefono: null,
            principal: true,
        );

        $nuevoContacto      = new Contacto();
        $nuevoContacto->id  = 99;

        $this->repo->expects($this->once())->method('crear')->willReturn($nuevoContacto);

        $this->repo->expects($this->once())
            ->method('quitarPrincipalDeCliente')
            ->with(5, 99);

        $this->service->crear($dto);
    }

    public function test_actualizar_a_principal_desmarca_anterior(): void
    {
        $contacto             = new Contacto();
        $contacto->id         = 10;
        $contacto->cliente_id = 3;

        $dto = ActualizarContactoDTO::fromArray(['nombre' => 'Nuevo', 'principal' => true]);

        $this->repo->expects($this->once())->method('actualizar')
            ->willReturn($contacto);

        $this->repo->expects($this->once())
            ->method('quitarPrincipalDeCliente')
            ->with(3, 10);

        $this->service->actualizar($contacto, $dto);
    }

    public function test_actualizar_sin_principal_no_desmarca(): void
    {
        $contacto             = new Contacto();
        $contacto->id         = 10;
        $contacto->cliente_id = 3;

        $dto = ActualizarContactoDTO::fromArray(['nombre' => 'Cambiado', 'principal' => false]);

        $this->repo->expects($this->never())->method('quitarPrincipalDeCliente');
        $this->repo->expects($this->once())->method('actualizar')->willReturn($contacto);

        $this->service->actualizar($contacto, $dto);
    }

    public function test_eliminar_delega_al_repositorio(): void
    {
        $contacto = new Contacto();

        $this->repo->expects($this->once())->method('eliminar')->with($contacto);

        $this->service->eliminar($contacto);
    }

    public function test_restaurar_delega_al_repositorio(): void
    {
        $this->repo->expects($this->once())->method('restaurar')->with(7)->willReturn(new Contacto());

        $this->service->restaurar(7);
    }

    public function test_porCliente_delega_al_repositorio(): void
    {
        $coleccion = new Collection();
        $this->repo->expects($this->once())->method('porCliente')->with(2)->willReturn($coleccion);

        $resultado = $this->service->porCliente(2);

        $this->assertSame($coleccion, $resultado);
    }
}

