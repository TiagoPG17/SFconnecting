<?php

declare(strict_types=1);

namespace Tests\Unit\SFconnecting\Repositories;

use App\Domain\Clientes\Models\Cliente;
use App\Domain\Negocios\DTOs\ActualizarNegocioDTO;
use App\Domain\Negocios\DTOs\CrearNegocioDTO;
use App\Domain\Negocios\Models\Negocio;
use App\Domain\Negocios\Repositories\NegocioRepository;
use App\Domain\Pipeline\Models\PipelineEstado;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NegocioRepositoryTest extends TestCase
{
    use RefreshDatabase;

    private NegocioRepository $repo;
    private PipelineEstado $estadoAbierto;
    private PipelineEstado $estadoGanado;

    protected function setUp(): void
    {
        parent::setUp();

        $this->estadoAbierto = PipelineEstado::create([
            'nombre' => 'Prospectando', 'slug' => 'prospectando-rep', 'tipo' => 'negocio',
            'color' => '#6B7280', 'orden' => 1, 'porcentaje_cierre' => 10,
            'es_final' => false, 'es_ganado' => false, 'es_perdido' => false, 'activo' => true,
        ]);
        $this->estadoGanado = PipelineEstado::create([
            'nombre' => 'Ganado', 'slug' => 'ganado-rep', 'tipo' => 'negocio',
            'color' => '#10B981', 'orden' => 5, 'porcentaje_cierre' => 100,
            'es_final' => true, 'es_ganado' => true, 'es_perdido' => false, 'activo' => true,
        ]);

        $this->repo = new NegocioRepository();
    }

    public function test_crea_negocio_correctamente(): void
    {
        $user    = User::factory()->create();
        $cliente = Cliente::factory()->create();

        $negocio = $this->repo->crear($this->dtoBase($user->id, $cliente->id));

        $this->assertInstanceOf(Negocio::class, $negocio);
        $this->assertDatabaseHas('sf_negocios', ['nombre_negocio' => 'Negocio Repo Test']);
    }

    public function test_actualiza_negocio(): void
    {
        $user    = User::factory()->create();
        $cliente = Cliente::factory()->create();
        $negocio = $this->repo->crear($this->dtoBase($user->id, $cliente->id));

        $actualizado = $this->repo->actualizar(
            $negocio,
            ActualizarNegocioDTO::fromArray(['nombre_negocio' => 'Nombre Actualizado', 'valor_estimado' => 99000])
        );

        $this->assertSame('Nombre Actualizado', $actualizado->nombre_negocio);
        $this->assertSame('99000.00', $actualizado->valor_estimado);
    }

    public function test_elimina_con_soft_delete(): void
    {
        $user    = User::factory()->create();
        $cliente = Cliente::factory()->create();
        $negocio = $this->repo->crear($this->dtoBase($user->id, $cliente->id));

        $this->repo->eliminar($negocio);

        $this->assertSoftDeleted('sf_negocios', ['id' => $negocio->id]);
    }

    public function test_busca_por_id_con_relaciones(): void
    {
        $user    = User::factory()->create();
        $cliente = Cliente::factory()->create();
        $negocio = $this->repo->crear($this->dtoBase($user->id, $cliente->id));

        $encontrado = $this->repo->buscarPorId($negocio->id);

        $this->assertNotNull($encontrado);
        $this->assertTrue($encontrado->relationLoaded('pipelineEstado'));
        $this->assertTrue($encontrado->relationLoaded('asesor'));
    }

    public function test_pagina_con_filtro_estado(): void
    {
        $user    = User::factory()->create();
        $cliente = Cliente::factory()->create();
        $this->repo->crear($this->dtoBase($user->id, $cliente->id));

        $resultado = $this->repo->paginar(['pipeline_estado_id' => $this->estadoAbierto->id]);

        $this->assertSame(1, $resultado->total());
    }

    public function test_kanban_agrupa_por_estados_activos(): void
    {
        $user    = User::factory()->create();
        $cliente = Cliente::factory()->create();
        $this->repo->crear($this->dtoBase($user->id, $cliente->id));

        $kanban = $this->repo->kanban();

        $this->assertNotEmpty($kanban);
        $this->assertArrayHasKey('estado', $kanban[0]);
        $this->assertArrayHasKey('negocios', $kanban[0]);
        $this->assertArrayHasKey('valor_total', $kanban[0]);
        $this->assertArrayHasKey('valor_forecast', $kanban[0]);
    }

    public function test_forecast_calcula_valor_ponderado(): void
    {
        $user    = User::factory()->create();
        $cliente = Cliente::factory()->create();

        $this->repo->crear(CrearNegocioDTO::fromArray([
            'nombre_negocio'     => 'A',
            'pipeline_estado_id' => $this->estadoAbierto->id,
            'asesor_id'          => $user->id,
            'cliente_id'         => $cliente->id,
            'valor_estimado'     => 100000,
            'probabilidad_cierre' => 50,
        ]));

        $forecast = $this->repo->forecast();

        $this->assertSame(100000.0, (float) $forecast['total_pipeline']);
        $this->assertSame(50000.0, (float) $forecast['total_forecast']);
    }

    public function test_forecast_excluye_estados_finales(): void
    {
        $user    = User::factory()->create();
        $cliente = Cliente::factory()->create();

        // Negocio abierto
        $this->repo->crear(CrearNegocioDTO::fromArray([
            'nombre_negocio' => 'Abierto', 'pipeline_estado_id' => $this->estadoAbierto->id,
            'asesor_id' => $user->id, 'cliente_id' => $cliente->id,
            'valor_estimado' => 100000, 'probabilidad_cierre' => 50,
        ]));

        // Negocio ganado (estado final — no debe aparecer en forecast)
        $this->repo->crear(CrearNegocioDTO::fromArray([
            'nombre_negocio' => 'Ganado', 'pipeline_estado_id' => $this->estadoGanado->id,
            'asesor_id' => $user->id, 'cliente_id' => $cliente->id,
            'valor_estimado' => 200000, 'probabilidad_cierre' => 100,
        ]));

        $forecast = $this->repo->forecast();

        $this->assertSame(1, $forecast['cantidad']);
        $this->assertSame(100000.0, (float) $forecast['total_pipeline']);
    }

    private function dtoBase(int $asesorId, int $clienteId): CrearNegocioDTO
    {
        return CrearNegocioDTO::fromArray([
            'nombre_negocio'     => 'Negocio Repo Test',
            'pipeline_estado_id' => $this->estadoAbierto->id,
            'asesor_id'          => $asesorId,
            'cliente_id'         => $clienteId,
            'valor_estimado'     => 50000,
        ]);
    }
}
