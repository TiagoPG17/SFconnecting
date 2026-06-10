<?php

declare(strict_types=1);

namespace Tests\Unit\SFconnecting\Services;

use App\Domain\Clientes\Models\Cliente;
use App\Domain\Clientes\Repositories\ClienteRepository;
use App\Domain\Seguimientos\DTOs\CrearSeguimientoDTO;
use App\Domain\Seguimientos\Exceptions\SeguimientoException;
use App\Domain\Seguimientos\Models\Seguimiento;
use App\Domain\Seguimientos\Repositories\SeguimientoRepository;
use App\Domain\Seguimientos\Services\SeguimientoService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SeguimientoServiceTest extends TestCase
{
    use RefreshDatabase;

    private SeguimientoService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new SeguimientoService(
            new SeguimientoRepository(),
            new ClienteRepository(),
        );
    }

    private function dto(int $clienteId, int $userId, ?Carbon $proximaFecha = null): CrearSeguimientoDTO
    {
        return new CrearSeguimientoDTO(
            clienteId:        $clienteId,
            userId:           $userId,
            tipo:             'llamada',
            resultado:        'exitoso',
            descripcion:      'Test de seguimiento',
            fechaSeguimiento: Carbon::now()->subHour(),
            proximaFecha:     $proximaFecha,
        );
    }

    public function test_crea_seguimiento_exitosamente(): void
    {
        $cliente     = Cliente::factory()->create();
        $seguimiento = $this->service->crear($this->dto($cliente->id, $cliente->user_id));

        $this->assertInstanceOf(Seguimiento::class, $seguimiento);
        $this->assertDatabaseHas('seguimientos', ['cliente_id' => $cliente->id]);
    }

    public function test_lanza_excepcion_si_cliente_no_existe(): void
    {
        $this->expectException(SeguimientoException::class);
        $this->expectExceptionMessageMatches('/99999/');

        $this->service->crear($this->dto(99999, 1));
    }

    public function test_lanza_excepcion_si_proxima_fecha_es_pasada(): void
    {
        $cliente = Cliente::factory()->create();

        $this->expectException(SeguimientoException::class);

        $this->service->crear($this->dto($cliente->id, $cliente->user_id, Carbon::now()->subDay()));
    }

    public function test_permite_crear_sin_proxima_fecha(): void
    {
        $cliente     = Cliente::factory()->create();
        $seguimiento = $this->service->crear($this->dto($cliente->id, $cliente->user_id));

        $this->assertNull($seguimiento->proxima_fecha);
    }

    public function test_permite_crear_con_proxima_fecha_futura(): void
    {
        $cliente     = Cliente::factory()->create();
        $seguimiento = $this->service->crear(
            $this->dto($cliente->id, $cliente->user_id, Carbon::now()->addDays(3))
        );

        $this->assertNotNull($seguimiento->proxima_fecha);
    }

    public function test_elimina_seguimiento(): void
    {
        $cliente     = Cliente::factory()->create();
        $seguimiento = Seguimiento::factory()->create(['cliente_id' => $cliente->id]);

        $this->service->eliminar($seguimiento);

        $this->assertDatabaseMissing('seguimientos', ['id' => $seguimiento->id]);
    }

    public function test_retorna_timeline_del_cliente(): void
    {
        $cliente = Cliente::factory()->create();
        Seguimiento::factory(4)->create(['cliente_id' => $cliente->id]);

        $timeline = $this->service->timelineCliente($cliente->id);

        $this->assertCount(4, $timeline);
    }
}

