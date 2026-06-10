<?php

declare(strict_types=1);

namespace Tests\Unit\SFconnecting\Repositories;

use App\Domain\Clientes\Models\Cliente;
use App\Domain\Seguimientos\DTOs\CrearSeguimientoDTO;
use App\Domain\Seguimientos\Models\Seguimiento;
use App\Domain\Seguimientos\Repositories\SeguimientoRepository;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SeguimientoRepositoryTest extends TestCase
{
    use RefreshDatabase;

    private SeguimientoRepository $repo;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repo = new SeguimientoRepository();
    }

    private function dto(int $clienteId, int $userId, array $extra = []): CrearSeguimientoDTO
    {
        return new CrearSeguimientoDTO(
            clienteId:        $clienteId,
            userId:           $userId,
            tipo:             'llamada',
            resultado:        'exitoso',
            descripcion:      'Seguimiento de prueba',
            fechaSeguimiento: Carbon::now()->subDay(),
            proximaFecha:     isset($extra['proxima_fecha']) ? Carbon::parse($extra['proxima_fecha']) : null,
        );
    }

    public function test_crea_seguimiento_correctamente(): void
    {
        $cliente    = Cliente::factory()->create();
        $dto        = $this->dto($cliente->id, $cliente->user_id);
        $seguimiento = $this->repo->crear($dto);

        $this->assertInstanceOf(Seguimiento::class, $seguimiento);
        $this->assertDatabaseHas('seguimientos', ['cliente_id' => $cliente->id, 'tipo' => 'llamada']);
    }

    public function test_busca_por_id(): void
    {
        $seguimiento = Seguimiento::factory()->create();
        $encontrado  = $this->repo->buscarPorId($seguimiento->id);

        $this->assertNotNull($encontrado);
        $this->assertSame($seguimiento->id, $encontrado->id);
    }

    public function test_retorna_null_para_id_inexistente(): void
    {
        $this->assertNull($this->repo->buscarPorId(99999));
    }

    public function test_timeline_retorna_seguimientos_del_cliente(): void
    {
        $cliente = Cliente::factory()->create();
        $otro    = Cliente::factory()->create();

        Seguimiento::factory(3)->create(['cliente_id' => $cliente->id]);
        Seguimiento::factory(2)->create(['cliente_id' => $otro->id]);

        $timeline = $this->repo->timelineCliente($cliente->id);

        $this->assertCount(3, $timeline);
    }

    public function test_timeline_ordenado_por_fecha_descendente(): void
    {
        $cliente = Cliente::factory()->create();

        Seguimiento::factory()->create(['cliente_id' => $cliente->id, 'fecha_seguimiento' => now()->subDays(5)]);
        Seguimiento::factory()->create(['cliente_id' => $cliente->id, 'fecha_seguimiento' => now()->subDay()]);
        Seguimiento::factory()->create(['cliente_id' => $cliente->id, 'fecha_seguimiento' => now()->subDays(10)]);

        $timeline = $this->repo->timelineCliente($cliente->id);

        $this->assertTrue($timeline->first()->fecha_seguimiento->greaterThan($timeline->last()->fecha_seguimiento));
    }

    public function test_proximos_retorna_seguimientos_en_rango(): void
    {
        $user = User::factory()->create();

        Seguimiento::factory()->create(['user_id' => $user->id, 'proxima_fecha' => now()->addDays(3)]);
        Seguimiento::factory()->create(['user_id' => $user->id, 'proxima_fecha' => now()->addDays(10)]);
        Seguimiento::factory()->create(['user_id' => $user->id, 'proxima_fecha' => null]);

        $proximos = $this->repo->proximosPorAsesor($user->id, 7);

        $this->assertCount(1, $proximos);
    }

    public function test_elimina_seguimiento(): void
    {
        $seguimiento = Seguimiento::factory()->create();

        $this->repo->eliminar($seguimiento);

        $this->assertDatabaseMissing('seguimientos', ['id' => $seguimiento->id]);
    }

    public function test_pagina_seguimientos_por_cliente(): void
    {
        $cliente = Cliente::factory()->create();
        Seguimiento::factory(5)->create(['cliente_id' => $cliente->id]);

        $pagina = $this->repo->paginarPorCliente($cliente->id, 3);

        $this->assertSame(3, $pagina->perPage());
        $this->assertSame(5, $pagina->total());
    }

    // â”€â”€â”€ paginar() â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€

    public function test_paginar_sin_filtros_retorna_todos(): void
    {
        Seguimiento::factory(4)->create();

        $pagina = $this->repo->paginar();

        $this->assertSame(4, $pagina->total());
    }

    public function test_paginar_filtra_por_cliente_id(): void
    {
        $cliente = Cliente::factory()->create();
        $otro    = Cliente::factory()->create();

        Seguimiento::factory(3)->create(['cliente_id' => $cliente->id]);
        Seguimiento::factory(2)->create(['cliente_id' => $otro->id]);

        $pagina = $this->repo->paginar(['cliente_id' => $cliente->id]);

        $this->assertSame(3, $pagina->total());
    }

    public function test_paginar_filtra_por_user_id(): void
    {
        $user  = User::factory()->create();
        $otro  = User::factory()->create();
        $cliente = Cliente::factory()->create();

        Seguimiento::factory(2)->create(['user_id' => $user->id, 'cliente_id' => $cliente->id]);
        Seguimiento::factory(3)->create(['user_id' => $otro->id, 'cliente_id' => $cliente->id]);

        $pagina = $this->repo->paginar(['user_id' => $user->id]);

        $this->assertSame(2, $pagina->total());
    }

    public function test_paginar_filtra_por_tipo(): void
    {
        $cliente = Cliente::factory()->create();

        Seguimiento::factory(2)->create(['cliente_id' => $cliente->id, 'tipo' => 'llamada']);
        Seguimiento::factory(3)->create(['cliente_id' => $cliente->id, 'tipo' => 'visita']);

        $pagina = $this->repo->paginar(['tipo' => 'llamada']);

        $this->assertSame(2, $pagina->total());
    }

    public function test_paginar_filtra_por_resultado(): void
    {
        $cliente = Cliente::factory()->create();

        Seguimiento::factory(2)->create(['cliente_id' => $cliente->id, 'resultado' => 'exitoso']);
        Seguimiento::factory(1)->create(['cliente_id' => $cliente->id, 'resultado' => 'pendiente']);

        $pagina = $this->repo->paginar(['resultado' => 'exitoso']);

        $this->assertSame(2, $pagina->total());
    }

    public function test_paginar_filtra_por_rango_fechas(): void
    {
        $cliente = Cliente::factory()->create();

        Seguimiento::factory()->create(['cliente_id' => $cliente->id, 'fecha_seguimiento' => now()->subDays(10)]);
        Seguimiento::factory()->create(['cliente_id' => $cliente->id, 'fecha_seguimiento' => now()->subDays(3)]);
        Seguimiento::factory()->create(['cliente_id' => $cliente->id, 'fecha_seguimiento' => now()->subDays(1)]);

        $pagina = $this->repo->paginar([
            'fecha_desde' => now()->subDays(5)->toDateString(),
            'fecha_hasta' => now()->toDateString(),
        ]);

        $this->assertSame(2, $pagina->total());
    }

    public function test_paginar_respeta_porPagina(): void
    {
        Seguimiento::factory(10)->create();

        $pagina = $this->repo->paginar([], 4);

        $this->assertSame(4, $pagina->perPage());
        $this->assertSame(10, $pagina->total());
    }

    // â”€â”€â”€ Seguimientos de prospectos â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€

    public function test_crea_seguimiento_para_prospecto_sin_cliente(): void
    {
        $user      = User::factory()->create();
        $prospecto = \App\Domain\Prospectos\Models\Prospecto::create([
            'codigo' => 'PROS-00001', 'empresa' => 'Acme', 'contacto' => 'Juan',
            'estado_pipeline_id' => \App\Domain\Pipeline\Models\PipelineEstado::create([
                'nombre' => 'Lead', 'slug' => 'lead', 'tipo' => 'prospecto',
                'color' => '#aaa', 'orden' => 1, 'porcentaje_cierre' => 5,
                'es_final' => false, 'es_ganado' => false, 'es_perdido' => false, 'activo' => true,
            ])->id,
            'asesor_id' => $user->id, 'activo' => true,
        ]);

        $dto = new CrearSeguimientoDTO(
            clienteId:        null,
            userId:           $user->id,
            tipo:             'llamada',
            resultado:        'pendiente',
            descripcion:      'Primer contacto con el lead',
            fechaSeguimiento: Carbon::now(),
            prospectoId:      $prospecto->id,
        );

        $seguimiento = $this->repo->crear($dto);

        $this->assertNull($seguimiento->cliente_id);
        $this->assertSame($prospecto->id, $seguimiento->prospecto_id);
        $this->assertDatabaseHas('seguimientos', ['prospecto_id' => $prospecto->id, 'cliente_id' => null]);
    }

    public function test_timeline_por_prospecto(): void
    {
        $user      = User::factory()->create();
        $estado    = \App\Domain\Pipeline\Models\PipelineEstado::create([
            'nombre' => 'Lead2', 'slug' => 'lead2', 'tipo' => 'prospecto',
            'color' => '#bbb', 'orden' => 2, 'porcentaje_cierre' => 5,
            'es_final' => false, 'es_ganado' => false, 'es_perdido' => false, 'activo' => true,
        ]);
        $prospecto = \App\Domain\Prospectos\Models\Prospecto::create([
            'codigo' => 'PROS-00002', 'empresa' => 'Beta', 'contacto' => 'Ana',
            'estado_pipeline_id' => $estado->id, 'asesor_id' => $user->id, 'activo' => true,
        ]);

        Seguimiento::create([
            'prospecto_id' => $prospecto->id, 'cliente_id' => null,
            'user_id' => $user->id, 'tipo' => 'llamada', 'resultado' => 'exitoso',
            'descripcion' => 'Contacto inicial', 'fecha_seguimiento' => now()->subDay(),
        ]);
        Seguimiento::create([
            'prospecto_id' => $prospecto->id, 'cliente_id' => null,
            'user_id' => $user->id, 'tipo' => 'email', 'resultado' => 'pendiente',
            'descripcion' => 'EnvÃ­o de propuesta', 'fecha_seguimiento' => now(),
        ]);

        $timeline = $this->repo->porProspecto($prospecto->id);

        $this->assertCount(2, $timeline);
    }

    public function test_migrar_seguimientos_a_cliente(): void
    {
        $user    = User::factory()->create();
        $cliente = \App\Domain\Clientes\Models\Cliente::factory()->create();
        $estado  = \App\Domain\Pipeline\Models\PipelineEstado::create([
            'nombre' => 'Lead3', 'slug' => 'lead3', 'tipo' => 'prospecto',
            'color' => '#ccc', 'orden' => 3, 'porcentaje_cierre' => 5,
            'es_final' => false, 'es_ganado' => false, 'es_perdido' => false, 'activo' => true,
        ]);
        $prospecto = \App\Domain\Prospectos\Models\Prospecto::create([
            'codigo' => 'PROS-00003', 'empresa' => 'Gamma', 'contacto' => 'Pedro',
            'estado_pipeline_id' => $estado->id, 'asesor_id' => $user->id, 'activo' => true,
        ]);

        Seguimiento::create([
            'prospecto_id' => $prospecto->id, 'cliente_id' => null,
            'user_id' => $user->id, 'tipo' => 'llamada', 'resultado' => 'exitoso',
            'descripcion' => 'Contacto pre-conversiÃ³n', 'fecha_seguimiento' => now()->subDays(2),
        ]);

        $migrados = $this->repo->migrarACliente($prospecto->id, $cliente->id);

        $this->assertSame(1, $migrados);
        $this->assertDatabaseHas('seguimientos', [
            'prospecto_id' => $prospecto->id,
            'cliente_id'   => $cliente->id,
        ]);
    }
}

