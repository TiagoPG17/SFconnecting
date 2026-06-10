<?php

declare(strict_types=1);

namespace Tests\Unit\SFconnecting\Repositories;

use App\Domain\Clientes\DTOs\ActualizarClienteDTO;
use App\Domain\Clientes\DTOs\CrearClienteDTO;
use App\Domain\Clientes\Models\Cliente;
use App\Domain\Clientes\Repositories\ClienteRepository;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ClienteRepositoryTest extends TestCase
{
    use RefreshDatabase;

    private ClienteRepository $repo;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repo = new ClienteRepository();
    }

    // â€” CREAR â€”

    public function test_crea_cliente_correctamente(): void
    {
        $user = User::factory()->create();
        $dto  = new CrearClienteDTO(
            razonSocial: 'Empresa Test S.A.S',
            nit:         '900111222',
            userId:      $user->id,
            email:       'test@empresa.co',
            ciudad:      'BogotÃ¡',
        );

        $cliente = $this->repo->crear($dto);

        $this->assertInstanceOf(Cliente::class, $cliente);
        $this->assertDatabaseHas('clientes', ['nit' => '900111222', 'razon_social' => 'Empresa Test S.A.S']);
        $this->assertSame('prospecto', $cliente->estado);
    }

    public function test_crea_cliente_sin_email(): void
    {
        $user    = User::factory()->create();
        $dto     = new CrearClienteDTO(razonSocial: 'Sin Email Co', nit: '800222333', userId: $user->id);
        $cliente = $this->repo->crear($dto);

        $this->assertNull($cliente->email);
        $this->assertDatabaseHas('clientes', ['nit' => '800222333']);
    }

    // â€” ACTUALIZAR â€”

    public function test_actualiza_campos_del_cliente(): void
    {
        $cliente = Cliente::factory()->create(['ciudad' => 'Cali']);
        $dto     = ActualizarClienteDTO::fromArray(['ciudad' => 'MedellÃ­n', 'estado' => 'activo']);

        $actualizado = $this->repo->actualizar($cliente, $dto);

        $this->assertSame('MedellÃ­n', $actualizado->ciudad);
        $this->assertSame('activo', $actualizado->estado);
    }

    public function test_actualizar_solo_modifica_campos_enviados(): void
    {
        $cliente = Cliente::factory()->create(['razon_social' => 'Original', 'ciudad' => 'BogotÃ¡']);
        $dto     = ActualizarClienteDTO::fromArray(['ciudad' => 'Cali']);

        $actualizado = $this->repo->actualizar($cliente, $dto);

        $this->assertSame('Original', $actualizado->razon_social);
        $this->assertSame('Cali', $actualizado->ciudad);
    }

    // â€” ELIMINAR (soft delete) â€”

    public function test_elimina_cliente_con_soft_delete(): void
    {
        $cliente = Cliente::factory()->create();

        $this->repo->eliminar($cliente);

        $this->assertSoftDeleted('clientes', ['id' => $cliente->id]);
    }

    public function test_cliente_eliminado_no_aparece_en_listado_normal(): void
    {
        $cliente = Cliente::factory()->create(['nit' => '999888777']);
        $this->repo->eliminar($cliente);

        $encontrado = $this->repo->buscarPorNit('999888777');

        $this->assertNull($encontrado);
    }

    // â€” RESTAURAR â€”

    public function test_restaura_cliente_eliminado(): void
    {
        $cliente = Cliente::factory()->create();
        $this->repo->eliminar($cliente);

        $restaurado = $this->repo->restaurar($cliente->id);

        $this->assertNotNull($restaurado);
        $this->assertNull($restaurado->deleted_at);
        $this->assertDatabaseHas('clientes', ['id' => $cliente->id, 'deleted_at' => null]);
    }

    // â€” BUSCAR â€”

    public function test_busca_cliente_por_id(): void
    {
        $cliente    = Cliente::factory()->create();
        $encontrado = $this->repo->buscarPorId($cliente->id);

        $this->assertNotNull($encontrado);
        $this->assertSame($cliente->id, $encontrado->id);
    }

    public function test_retorna_null_para_id_inexistente(): void
    {
        $encontrado = $this->repo->buscarPorId(99999);

        $this->assertNull($encontrado);
    }

    public function test_busca_por_nit(): void
    {
        Cliente::factory()->create(['nit' => '900500600']);
        $encontrado = $this->repo->buscarPorNit('900500600');

        $this->assertNotNull($encontrado);
        $this->assertSame('900500600', $encontrado->nit);
    }

    public function test_busca_por_email(): void
    {
        Cliente::factory()->create(['email' => 'contacto@empresa.co']);
        $encontrado = $this->repo->buscarPorEmail('contacto@empresa.co');

        $this->assertNotNull($encontrado);
        $this->assertSame('contacto@empresa.co', $encontrado->email);
    }

    public function test_busqueda_por_termino_encuentra_por_razon_social(): void
    {
        Cliente::factory()->create(['razon_social' => 'Distribuidora Central']);
        Cliente::factory()->create(['razon_social' => 'Otra Empresa']);

        $resultados = $this->repo->buscar('Distribuidora');

        $this->assertCount(1, $resultados);
        $this->assertSame('Distribuidora Central', $resultados->first()->razon_social);
    }

    public function test_busqueda_por_termino_encuentra_por_nit(): void
    {
        Cliente::factory()->create(['nit' => '811222333']);

        $resultados = $this->repo->buscar('811222333');

        $this->assertCount(1, $resultados);
    }

    // â€” PAGINACIÃ“N â€”

    public function test_paginar_retorna_clientes(): void
    {
        Cliente::factory(5)->create();

        $pagina = $this->repo->paginar(porPagina: 3);

        $this->assertSame(3, $pagina->perPage());
        $this->assertSame(5, $pagina->total());
    }

    public function test_paginar_filtra_por_estado(): void
    {
        Cliente::factory(3)->create(['estado' => 'activo']);
        Cliente::factory(2)->create(['estado' => 'inactivo']);

        $pagina = $this->repo->paginar(filtros: ['estado' => 'activo']);

        $this->assertSame(3, $pagina->total());
    }

    public function test_paginar_filtra_por_ciudad(): void
    {
        Cliente::factory(2)->create(['ciudad' => 'BogotÃ¡']);
        Cliente::factory(1)->create(['ciudad' => 'Cali']);

        $pagina = $this->repo->paginar(filtros: ['ciudad' => 'BogotÃ¡']);

        $this->assertSame(2, $pagina->total());
    }

    // â€” POR ASESOR â€”

    public function test_retorna_clientes_del_asesor(): void
    {
        $asesor = User::factory()->create();
        $otro   = User::factory()->create();

        Cliente::factory(3)->create(['user_id' => $asesor->id]);
        Cliente::factory(2)->create(['user_id' => $otro->id]);

        $clientes = $this->repo->porAsesor($asesor->id);

        $this->assertCount(3, $clientes);
    }

    // â€” DUPLICADOS â€”

    public function test_detecta_nit_existente(): void
    {
        Cliente::factory()->create(['nit' => '900111222']);

        $this->assertTrue($this->repo->existeNit('900111222'));
    }

    public function test_no_detecta_nit_inexistente(): void
    {
        $this->assertFalse($this->repo->existeNit('000000000'));
    }

    public function test_excluye_id_propio_al_verificar_nit(): void
    {
        $cliente = Cliente::factory()->create(['nit' => '900111222']);

        $this->assertFalse($this->repo->existeNit('900111222', $cliente->id));
    }

    public function test_detecta_email_existente(): void
    {
        Cliente::factory()->create(['email' => 'unico@empresa.co']);

        $this->assertTrue($this->repo->existeEmail('unico@empresa.co'));
    }

    public function test_excluye_id_propio_al_verificar_email(): void
    {
        $cliente = Cliente::factory()->create(['email' => 'unico@empresa.co']);

        $this->assertFalse($this->repo->existeEmail('unico@empresa.co', $cliente->id));
    }
}

