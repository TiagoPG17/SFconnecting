<?php

declare(strict_types=1);

namespace Tests\Unit\SFconnecting\Repositories;

use App\Domain\Clientes\Models\Cliente;
use App\Domain\Dashboard\Repositories\DashboardRepository;
use App\Domain\Negocios\Models\AuditoriaPipeline;
use App\Domain\Negocios\Models\Negocio;
use App\Domain\Pipeline\Models\PipelineEstado;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class DashboardRepositoryTest extends TestCase
{
    use RefreshDatabase;

    private DashboardRepository $repo;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repo = new DashboardRepository();
    }

    private function estadoPipeline(string $nombre, string $slug, bool $esFinal = false): PipelineEstado
    {
        return PipelineEstado::create([
            'nombre' => $nombre, 'slug' => $slug, 'tipo' => 'negocio',
            'color' => '#6366f1', 'orden' => 1, 'porcentaje_cierre' => 30,
            'es_final' => $esFinal, 'es_ganado' => false, 'es_perdido' => false, 'activo' => true,
        ]);
    }

    private function negocio(PipelineEstado $estado, User $asesor, Cliente $cliente): Negocio
    {
        return Negocio::create([
            'nombre_negocio'    => 'Negocio test',
            'pipeline_estado_id' => $estado->id,
            'asesor_id'         => $asesor->id,
            'cliente_id'        => $cliente->id,
            'valor_estimado'    => 10000,
            'activo'            => true,
        ]);
    }

    // â”€â”€â”€ agingNegocios â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€

    public function test_aging_retorna_estructura_correcta(): void
    {
        $asesor  = User::factory()->create();
        $cliente = Cliente::factory()->create();
        $estado  = $this->estadoPipeline('Contacto', 'contacto');

        $this->negocio($estado, $asesor, $cliente);

        $aging = $this->repo->agingNegocios();

        $this->assertIsArray($aging);
        $this->assertCount(1, $aging);

        $grupo = $aging[0];
        $this->assertArrayHasKey('estado', $grupo);
        $this->assertArrayHasKey('color', $grupo);
        $this->assertArrayHasKey('cantidad', $grupo);
        $this->assertArrayHasKey('avg_dias', $grupo);
        $this->assertArrayHasKey('max_dias', $grupo);
    }

    public function test_aging_agrupa_por_estado_pipeline(): void
    {
        $asesor  = User::factory()->create();
        $cliente = Cliente::factory()->create();
        $e1      = $this->estadoPipeline('Contacto', 'contacto-ag');
        $e2      = $this->estadoPipeline('Propuesta', 'propuesta-ag');

        $this->negocio($e1, $asesor, $cliente);
        $this->negocio($e1, $asesor, $cliente);
        $this->negocio($e2, $asesor, $cliente);

        $aging = $this->repo->agingNegocios();

        $this->assertCount(2, $aging);
        $cantidades = array_column($aging, 'cantidad');
        sort($cantidades);
        $this->assertSame([1, 2], $cantidades);
    }

    public function test_aging_excluye_negocios_en_estados_finales(): void
    {
        $asesor  = User::factory()->create();
        $cliente = Cliente::factory()->create();
        $abierto = $this->estadoPipeline('NegociaciÃ³n', 'negociacion-ag');
        $cerrado = $this->estadoPipeline('Ganado', 'ganado-ag', esFinal: true);

        $this->negocio($abierto, $asesor, $cliente);
        $this->negocio($cerrado, $asesor, $cliente);

        $aging = $this->repo->agingNegocios();

        $this->assertCount(1, $aging);
        $this->assertSame('NegociaciÃ³n', $aging[0]['estado']);
    }

    public function test_aging_dias_basado_en_ultimo_cambio_estado(): void
    {
        $asesor  = User::factory()->create();
        $cliente = Cliente::factory()->create();
        $estado  = $this->estadoPipeline('En seguimiento', 'en-seguimiento-ag');

        // Crear el negocio hace 10 dÃ­as y registrar el cambio de estado entonces
        Carbon::setTestNow(now()->subDays(10));
        $negocio = $this->negocio($estado, $asesor, $cliente);
        AuditoriaPipeline::create([
            'auditable_type'  => Negocio::class,
            'auditable_id'    => $negocio->id,
            'evento'          => 'cambio_estado',
            'estado_anterior' => 'Contacto',
            'estado_nuevo'    => 'En seguimiento',
            'datos_nuevos'    => [],
            'usuario_id'      => $asesor->id,
        ]);
        Carbon::setTestNow(null);

        $aging = $this->repo->agingNegocios();

        $this->assertSame(10, $aging[0]['avg_dias']);
    }

    public function test_aging_filtra_por_asesor(): void
    {
        $asesor1 = User::factory()->create();
        $asesor2 = User::factory()->create();
        $cliente = Cliente::factory()->create();
        $estado  = $this->estadoPipeline('Calificado', 'calificado-ag');

        $this->negocio($estado, $asesor1, $cliente);
        $this->negocio($estado, $asesor2, $cliente);

        $aging = $this->repo->agingNegocios(userId: $asesor1->id);

        $this->assertCount(1, $aging);
        $this->assertSame(1, $aging[0]['cantidad']);
    }
}

