<?php

declare(strict_types=1);

namespace Tests\Unit\SFconnecting\Services;

use App\Domain\Clientes\Models\Cliente;
use App\Domain\Maestros\Models\MaestroComercial;
use App\Domain\Negocios\DTOs\ActualizarNegocioDTO;
use App\Domain\Negocios\DTOs\CrearNegocioDTO;
use App\Domain\Negocios\Exceptions\NegocioException;
use App\Domain\Negocios\Models\Negocio;
use App\Domain\Negocios\Repositories\NegocioRepository;
use App\Domain\Negocios\Services\NegocioService;
use App\Domain\Pipeline\Models\PipelineEstado;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NegocioServiceTest extends TestCase
{
    use RefreshDatabase;

    private NegocioService $service;
    private PipelineEstado $estadoInicial;
    private PipelineEstado $estadoGanado;
    private PipelineEstado $estadoPerdido;
    private MaestroComercial $motivoPerdida;

    protected function setUp(): void
    {
        parent::setUp();

        $this->estadoInicial = PipelineEstado::create([
            'nombre' => 'Prospectando', 'slug' => 'prospectando', 'tipo' => 'negocio',
            'color' => '#6B7280', 'orden' => 1, 'porcentaje_cierre' => 10,
            'es_final' => false, 'es_ganado' => false, 'es_perdido' => false, 'activo' => true,
        ]);
        $this->estadoGanado = PipelineEstado::create([
            'nombre' => 'Ganado', 'slug' => 'ganado', 'tipo' => 'negocio',
            'color' => '#10B981', 'orden' => 5, 'porcentaje_cierre' => 100,
            'es_final' => true, 'es_ganado' => true, 'es_perdido' => false, 'activo' => true,
        ]);
        $this->estadoPerdido = PipelineEstado::create([
            'nombre' => 'Perdido', 'slug' => 'perdido', 'tipo' => 'negocio',
            'color' => '#EF4444', 'orden' => 6, 'porcentaje_cierre' => 0,
            'es_final' => true, 'es_ganado' => false, 'es_perdido' => true, 'activo' => true,
        ]);
        $this->motivoPerdida = MaestroComercial::create([
            'tipo' => 'motivo_perdida', 'nombre' => 'Precio', 'slug' => 'precio', 'orden' => 1, 'activo' => true,
        ]);

        $this->service = new NegocioService(new NegocioRepository());
    }

    // â€” CREAR â€”

    public function test_crea_negocio_exitosamente(): void
    {
        $user    = User::factory()->create();
        $cliente = Cliente::factory()->create();
        $dto     = $this->dtoBase($user->id, clienteId: $cliente->id);

        $negocio = $this->service->crear($dto);

        $this->assertInstanceOf(Negocio::class, $negocio);
        $this->assertSame('Negocio Test', $negocio->nombre_negocio);
        $this->assertDatabaseHas('sf_negocios', ['nombre_negocio' => 'Negocio Test']);
    }

    public function test_crear_registra_auditoria_de_estado_inicial(): void
    {
        $user    = User::factory()->create();
        $cliente = Cliente::factory()->create();

        $negocio = $this->service->crear($this->dtoBase($user->id, clienteId: $cliente->id));

        $this->assertDatabaseHas('sf_auditoria_pipeline', [
            'auditable_id' => $negocio->id,
            'evento'       => 'cambio_estado',
        ]);
    }

    public function test_lanza_excepcion_si_no_tiene_vinculo(): void
    {
        $user = User::factory()->create();

        $this->expectException(NegocioException::class);
        $this->expectExceptionMessageMatches('/prospecto|cliente/i');

        $this->service->crear($this->dtoBase($user->id));
    }

    // â€” FORECAST â€”

    public function test_calcula_valor_forecast_correctamente(): void
    {
        $user    = User::factory()->create();
        $cliente = Cliente::factory()->create();

        $negocio = $this->service->crear($this->dtoBase(
            $user->id, clienteId: $cliente->id, valor: 100000, probabilidad: 75
        ));

        $negocio->load('pipelineEstado');

        $this->assertSame(75000.0, $negocio->valorForecast());
    }

    public function test_forecast_hereda_probabilidad_del_estado(): void
    {
        $user    = User::factory()->create();
        $cliente = Cliente::factory()->create();

        $negocio = $this->service->crear($this->dtoBase($user->id, clienteId: $cliente->id, valor: 200000));
        $negocio->load('pipelineEstado');

        // El estado inicial tiene 10% de probabilidad
        $this->assertSame(20000.0, $negocio->valorForecast());
    }

    // â€” ACTUALIZAR / MOVER PIPELINE â€”

    public function test_mover_a_ganado_registra_fecha_cierre_real(): void
    {
        $user    = User::factory()->create();
        $cliente = Cliente::factory()->create();
        $negocio = $this->service->crear($this->dtoBase($user->id, clienteId: $cliente->id));

        $actualizado = $this->service->actualizar($negocio, ActualizarNegocioDTO::fromArray([
            'pipeline_estado_id' => $this->estadoGanado->id,
        ]));

        $this->assertNotNull($actualizado->fecha_cierre_real);
        $this->assertDatabaseHas('sf_auditoria_pipeline', [
            'auditable_id' => $negocio->id,
            'evento'       => 'negocio_ganado',
        ]);
    }

    public function test_mover_a_perdido_sin_motivo_lanza_excepcion(): void
    {
        $user    = User::factory()->create();
        $cliente = Cliente::factory()->create();
        $negocio = $this->service->crear($this->dtoBase($user->id, clienteId: $cliente->id));

        $this->expectException(NegocioException::class);

        $this->service->actualizar($negocio, ActualizarNegocioDTO::fromArray([
            'pipeline_estado_id' => $this->estadoPerdido->id,
        ]));
    }

    public function test_mover_a_perdido_con_motivo_exitosamente(): void
    {
        $user    = User::factory()->create();
        $cliente = Cliente::factory()->create();
        $negocio = $this->service->crear($this->dtoBase($user->id, clienteId: $cliente->id));

        $actualizado = $this->service->actualizar($negocio, ActualizarNegocioDTO::fromArray([
            'pipeline_estado_id' => $this->estadoPerdido->id,
            'motivo_perdida_id'  => $this->motivoPerdida->id,
        ]));

        $this->assertDatabaseHas('sf_auditoria_pipeline', [
            'auditable_id' => $negocio->id,
            'evento'       => 'negocio_perdido',
        ]);
    }

    // â€” ELIMINAR â€”

    public function test_elimina_negocio_con_soft_delete(): void
    {
        $user    = User::factory()->create();
        $cliente = Cliente::factory()->create();
        $negocio = $this->service->crear($this->dtoBase($user->id, clienteId: $cliente->id));

        $this->service->eliminar($negocio);

        $this->assertSoftDeleted('sf_negocios', ['id' => $negocio->id]);
    }

    // â€” HELPER â€”

    private function dtoBase(
        int $asesorId,
        ?int $clienteId = null,
        float $valor = 50000,
        ?int $probabilidad = null,
    ): CrearNegocioDTO {
        return CrearNegocioDTO::fromArray([
            'nombre_negocio'     => 'Negocio Test',
            'pipeline_estado_id' => $this->estadoInicial->id,
            'asesor_id'          => $asesorId,
            'cliente_id'         => $clienteId,
            'valor_estimado'     => $valor,
            'probabilidad_cierre' => $probabilidad,
        ]);
    }
}

