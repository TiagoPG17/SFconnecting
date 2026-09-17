<?php

declare(strict_types=1);

namespace Tests\Unit\SFconnecting\Services;

use App\Domain\Clientes\Models\Cliente;
use App\Domain\Clientes\Repositories\ClienteRepositoryInterface;
use App\Domain\Dashboard\Models\ReemplazoClienteMovido;
use App\Domain\Dashboard\Models\VendedorEquivalencia;
use App\Domain\Dashboard\Services\ReemplazoTemporalService;
use App\Domain\Negocios\Models\Negocio;
use App\Domain\Negocios\Repositories\NegocioRepositoryInterface;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\MockObject\MockObject;
use Tests\TestCase;

class ReemplazoTemporalServiceTest extends TestCase
{
    use RefreshDatabase;

    /** @var ClienteRepositoryInterface&MockObject */
    private ClienteRepositoryInterface $clienteRepo;

    /** @var NegocioRepositoryInterface&MockObject */
    private NegocioRepositoryInterface $negocioRepo;

    private ReemplazoTemporalService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->clienteRepo = $this->createMock(ClienteRepositoryInterface::class);
        $this->negocioRepo = $this->createMock(NegocioRepositoryInterface::class);
        $this->service      = new ReemplazoTemporalService($this->clienteRepo, $this->negocioRepo);
    }

    private function mapeo(User $asesor, string $cod, bool $esReemplazo = false, int $compania = 2): VendedorEquivalencia
    {
        return VendedorEquivalencia::create([
            'asesor_id'          => $asesor->id,
            'compania'           => $compania,
            'cod_vendedor_siesa' => $cod,
            'nombre_vendedor'    => 'Vendedor Test',
            'activo'             => true,
            'es_reemplazo'       => $esReemplazo,
        ]);
    }

    public function test_activar_mueve_los_clientes_del_titular_y_los_registra(): void
    {
        $titular      = User::factory()->create();
        $reemplazante = User::factory()->create();
        $this->mapeo($titular, 'V001');
        $reemplazo = $this->mapeo($reemplazante, 'V001', esReemplazo: true);

        $cliente1 = Cliente::factory()->create(['user_id' => $titular->id, 'compania' => 2]);
        $cliente2 = Cliente::factory()->create(['user_id' => $titular->id, 'compania' => 2]);

        $this->clienteRepo->expects($this->once())
            ->method('reasignarAsesor')
            ->with($titular->id, $reemplazante->id, 2)
            ->willReturn(new Collection([$cliente1, $cliente2]));

        $movidos = $this->service->activar($reemplazo);

        $this->assertSame(2, $movidos);
        $this->assertDatabaseHas('sf_reemplazo_clientes_movidos', [
            'vendedor_equivalencia_id' => $reemplazo->id,
            'cliente_id'               => $cliente1->id,
            'titular_user_id'          => $titular->id,
            'revertido_en'             => null,
        ]);
        $this->assertDatabaseHas('sf_reemplazo_clientes_movidos', [
            'vendedor_equivalencia_id' => $reemplazo->id,
            'cliente_id'               => $cliente2->id,
            'titular_user_id'          => $titular->id,
        ]);
    }

    public function test_activar_no_hace_nada_si_no_existe_titular_para_ese_codigo(): void
    {
        $reemplazante = User::factory()->create();
        $reemplazo    = $this->mapeo($reemplazante, 'V999', esReemplazo: true);

        $this->clienteRepo->expects($this->never())->method('reasignarAsesor');

        $movidos = $this->service->activar($reemplazo);

        $this->assertSame(0, $movidos);
    }

    public function test_revertir_regresa_al_titular_solo_lo_movido_por_ese_reemplazo(): void
    {
        $titular      = User::factory()->create();
        $reemplazante = User::factory()->create();
        $reemplazo    = $this->mapeo($reemplazante, 'V001', esReemplazo: true);

        $cliente = Cliente::factory()->create(['user_id' => $reemplazante->id, 'compania' => 2]);
        ReemplazoClienteMovido::create([
            'vendedor_equivalencia_id' => $reemplazo->id,
            'cliente_id'               => $cliente->id,
            'titular_user_id'          => $titular->id,
        ]);

        $this->negocioRepo->method('reasignarPorClientes')->willReturn(new Collection());

        $resultado = $this->service->revertir($reemplazo);

        $this->assertSame(1, $resultado['clientes']);
        $this->assertSame($titular->id, $cliente->fresh()->user_id);
        $this->assertNotNull(ReemplazoClienteMovido::first()->revertido_en);
    }

    public function test_revertir_no_toca_un_cliente_que_ya_fue_reasignado_manualmente_a_otra_persona(): void
    {
        $titular       = User::factory()->create();
        $reemplazante  = User::factory()->create();
        $otroComercial = User::factory()->create();
        $reemplazo     = $this->mapeo($reemplazante, 'V001', esReemplazo: true);

        $cliente = Cliente::factory()->create(['user_id' => $otroComercial->id, 'compania' => 2]);
        ReemplazoClienteMovido::create([
            'vendedor_equivalencia_id' => $reemplazo->id,
            'cliente_id'               => $cliente->id,
            'titular_user_id'          => $titular->id,
        ]);

        $this->negocioRepo->method('reasignarPorClientes')->willReturn(new Collection());

        $this->service->revertir($reemplazo);

        $this->assertSame($otroComercial->id, $cliente->fresh()->user_id);
    }

    public function test_revertir_tambien_devuelve_al_titular_los_negocios_nuevos_del_reemplazante(): void
    {
        $titular      = User::factory()->create();
        $reemplazante = User::factory()->create();
        $reemplazo    = $this->mapeo($reemplazante, 'V001', esReemplazo: true);

        $cliente = Cliente::factory()->create(['user_id' => $reemplazante->id, 'compania' => 2]);
        ReemplazoClienteMovido::create([
            'vendedor_equivalencia_id' => $reemplazo->id,
            'cliente_id'               => $cliente->id,
            'titular_user_id'          => $titular->id,
        ]);

        $negocioNuevo = (new Negocio())->forceFill(['id' => 99]);

        $this->negocioRepo->expects($this->once())
            ->method('reasignarPorClientes')
            ->with([$cliente->id], $reemplazante->id, $titular->id)
            ->willReturn(new Collection([$negocioNuevo]));

        $resultado = $this->service->revertir($reemplazo);

        $this->assertSame(1, $resultado['negocios']);
    }
}
