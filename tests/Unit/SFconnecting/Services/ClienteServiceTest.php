<?php

declare(strict_types=1);

namespace Tests\Unit\SFconnecting\Services;

use App\Domain\Clientes\DTOs\ActualizarClienteDTO;
use App\Domain\Clientes\DTOs\CrearClienteDTO;
use App\Domain\Clientes\Exceptions\ClienteDuplicadoException;
use App\Domain\Clientes\Models\Cliente;
use App\Domain\Clientes\Repositories\ClienteRepository;
use App\Domain\Clientes\Services\ClienteService;
use App\Domain\ERP\Fakes\FakeERPRepository;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ClienteServiceTest extends TestCase
{
    use RefreshDatabase;

    private ClienteService $service;
    private FakeERPRepository $erp;

    protected function setUp(): void
    {
        parent::setUp();
        $this->erp     = new FakeERPRepository();
        $this->service = new ClienteService(new ClienteRepository(), $this->erp);
    }

    // â€” CREAR â€”

    public function test_crea_cliente_exitosamente(): void
    {
        $user    = User::factory()->create();
        $dto     = new CrearClienteDTO('Empresa TDD', '900300400', $user->id, compania: 1, email: 'tdd@empresa.co');
        $cliente = $this->service->crear($dto);

        $this->assertInstanceOf(Cliente::class, $cliente);
        $this->assertSame('900300400', $cliente->nit);
        $this->assertDatabaseHas('clientes', ['nit' => '900300400']);
    }

    public function test_lanza_excepcion_si_nit_ya_existe(): void
    {
        $user = User::factory()->create();
        Cliente::factory()->create(['nit' => '900300400', 'compania' => 1]);

        $this->expectException(ClienteDuplicadoException::class);
        $this->expectExceptionMessageMatches('/900300400/');

        $this->service->crear(new CrearClienteDTO('Otra', '900300400', $user->id, compania: 1));
    }

    public function test_permite_mismo_nit_en_compania_distinta(): void
    {
        $user = User::factory()->create();
        Cliente::factory()->create(['nit' => '900300400', 'compania' => 1]);

        $cliente = $this->service->crear(new CrearClienteDTO('Otra', '900300400', $user->id, compania: 2));

        $this->assertSame(2, $cliente->compania);
        $this->assertDatabaseHas('clientes', ['nit' => '900300400', 'compania' => 1]);
        $this->assertDatabaseHas('clientes', ['nit' => '900300400', 'compania' => 2]);
    }

    public function test_lanza_excepcion_si_email_ya_existe(): void
    {
        $user = User::factory()->create();
        Cliente::factory()->create(['email' => 'dup@empresa.co']);

        $this->expectException(ClienteDuplicadoException::class);
        $this->expectExceptionMessageMatches('/dup@empresa.co/');

        $this->service->crear(new CrearClienteDTO('Otra', '111222333', $user->id, compania: 1, email: 'dup@empresa.co'));
    }

    public function test_permite_crear_cliente_sin_email(): void
    {
        $user    = User::factory()->create();
        $cliente = $this->service->crear(new CrearClienteDTO('Sin Email', '700800900', $user->id, compania: 1));

        $this->assertNull($cliente->email);
    }

    // â€” ACTUALIZAR â€”

    public function test_actualiza_cliente_correctamente(): void
    {
        $cliente     = Cliente::factory()->create();
        $dto         = ActualizarClienteDTO::fromArray(['estado' => 'activo', 'ciudad' => 'Cali']);
        $actualizado = $this->service->actualizar($cliente, $dto);

        $this->assertSame('activo', $actualizado->estado);
        $this->assertSame('Cali', $actualizado->ciudad);
    }

    public function test_lanza_excepcion_si_nuevo_email_ya_pertenece_a_otro(): void
    {
        Cliente::factory()->create(['email' => 'ocupado@empresa.co']);
        $cliente = Cliente::factory()->create(['email' => 'libre@empresa.co']);

        $this->expectException(ClienteDuplicadoException::class);

        $this->service->actualizar($cliente, ActualizarClienteDTO::fromArray(['email' => 'ocupado@empresa.co']));
    }

    public function test_permite_actualizar_con_mismo_email_propio(): void
    {
        $cliente     = Cliente::factory()->create(['email' => 'propio@empresa.co']);
        $dto         = ActualizarClienteDTO::fromArray(['email' => 'propio@empresa.co', 'ciudad' => 'BogotÃ¡']);
        $actualizado = $this->service->actualizar($cliente, $dto);

        $this->assertSame('propio@empresa.co', $actualizado->email);
    }

    // â€” ELIMINAR â€”

    public function test_elimina_cliente_correctamente(): void
    {
        $cliente = Cliente::factory()->create();
        $this->service->eliminar($cliente);

        $this->assertSoftDeleted('clientes', ['id' => $cliente->id]);
    }

    // â€” RESTAURAR â€”

    public function test_restaura_cliente_eliminado(): void
    {
        $cliente = Cliente::factory()->create();
        $this->service->eliminar($cliente);

        $restaurado = $this->service->restaurar($cliente->id);

        $this->assertNull($restaurado->deleted_at);
    }

    // â€” BUSCAR â€”

    public function test_busca_cliente_por_id(): void
    {
        $cliente    = Cliente::factory()->create();
        $encontrado = $this->service->buscarPorId($cliente->id);

        $this->assertNotNull($encontrado);
        $this->assertSame($cliente->id, $encontrado->id);
    }

    public function test_retorna_null_si_cliente_no_existe(): void
    {
        $this->assertNull($this->service->buscarPorId(99999));
    }

    public function test_busqueda_por_termino(): void
    {
        Cliente::factory()->create(['razon_social' => 'Comercio Norte']);
        Cliente::factory()->create(['razon_social' => 'Sur Ltda']);

        $resultados = $this->service->buscar('Norte');

        $this->assertCount(1, $resultados);
    }

    // â€” ERP LOOKUP â€”

    public function test_enriquece_cliente_con_datos_del_erp(): void
    {
        $user = User::factory()->create();
        $this->erp->agregarCliente('900123456', [
            'razon_social' => 'Empresa ERP SA',
            'ciudad'       => 'MedellÃ­n',
            'telefono'     => '6042223344',
        ]);

        $dto     = new CrearClienteDTO('Empresa ERP SA', '900123456', $user->id, compania: 1);
        $cliente = $this->service->crear($dto);

        $this->assertSame('MedellÃ­n', $cliente->ciudad);
        $this->assertSame('6042223344', $cliente->telefono);
    }

    public function test_crea_cliente_cuando_erp_no_disponible(): void
    {
        $user = User::factory()->create();
        $this->erp->simularDesconexion();

        $dto     = new CrearClienteDTO('Empresa Sin ERP', '800111222', $user->id, compania: 1);
        $cliente = $this->service->crear($dto);

        $this->assertInstanceOf(Cliente::class, $cliente);
        $this->assertDatabaseHas('clientes', ['nit' => '800111222']);
    }

    public function test_datos_usuario_tienen_prioridad_sobre_erp(): void
    {
        $user = User::factory()->create();
        $this->erp->agregarCliente('700444555', [
            'ciudad'  => 'Ciudad ERP',
            'telefono' => '0001111111',
        ]);

        $dto     = new CrearClienteDTO('Mi Empresa', '700444555', $user->id, compania: 1, ciudad: 'BogotÃ¡', telefono: '3001234567');
        $cliente = $this->service->crear($dto);

        $this->assertSame('BogotÃ¡', $cliente->ciudad);
        $this->assertSame('3001234567', $cliente->telefono);
    }

    // â€” SINCRONIZAR CARTERA â€”

    public function test_sincronizar_cartera_reasigna_cliente_al_nuevo_asesor(): void
    {
        $asesorAnterior = User::factory()->create();
        $asesorNuevo    = User::factory()->create();

        Cliente::factory()->create([
            'nit'      => '900700800',
            'compania' => 1,
            'user_id'  => $asesorAnterior->id,
        ]);

        $this->erp->agregarClientesDeVendedor('Vendedor Nuevo', [
            ['NIT' => '900700800', 'RAZON_SOCIAL' => 'Empresa Reasignada', 'CIUDAD' => 'Cali'],
        ]);

        $resultado = $this->service->sincronizarCarteraDesdeErp('Vendedor Nuevo', $asesorNuevo->id, 1);

        $this->assertSame(1, $resultado['actualizados']);
        $this->assertDatabaseHas('clientes', [
            'nit'      => '900700800',
            'compania' => 1,
            'user_id'  => $asesorNuevo->id,
        ]);
    }

    // â€” CAMBIO DE ESTADO â€”

    public function test_activa_cliente(): void
    {
        $cliente   = Cliente::factory()->create(['estado' => 'inactivo']);
        $activado  = $this->service->cambiarEstado($cliente, 'activo');

        $this->assertSame('activo', $activado->estado);
    }

    public function test_inactiva_cliente(): void
    {
        $cliente    = Cliente::factory()->create(['estado' => 'activo']);
        $inactivado = $this->service->cambiarEstado($cliente, 'inactivo');

        $this->assertSame('inactivo', $inactivado->estado);
    }

    public function test_lanza_excepcion_para_estado_invalido(): void
    {
        $cliente = Cliente::factory()->create();

        $this->expectException(\App\Domain\Shared\Exceptions\ValidationBusinessException::class);

        $this->service->cambiarEstado($cliente, 'inexistente');
    }
}

