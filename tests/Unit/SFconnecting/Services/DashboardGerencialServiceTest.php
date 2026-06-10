<?php

declare(strict_types=1);

namespace Tests\Unit\SFconnecting\Services;

use App\Domain\Dashboard\Repositories\DashboardGerencialRepositoryInterface;
use App\Domain\Dashboard\Services\DashboardGerencialService;
use Illuminate\Support\Collection;
use PHPUnit\Framework\MockObject\MockObject;
use Tests\TestCase;

class DashboardGerencialServiceTest extends TestCase
{
    /** @var DashboardGerencialRepositoryInterface&MockObject */
    private DashboardGerencialRepositoryInterface $repo;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repo = $this->createMock(DashboardGerencialRepositoryInterface::class);
    }

    private function servicio(array $meses = ['2026-01'], int $anio = 2026): DashboardGerencialService
    {
        return new DashboardGerencialService($this->repo, 2, $anio, $meses);
    }

    private function presupuestoObj(int $asesorId, string $nombre, float $presupuesto): object
    {
        return (object) [
            'asesor_id'  => $asesorId,
            'asesor'     => (object) ['name' => $nombre],
            'presupuesto' => (string) $presupuesto,
        ];
    }

    // ─── presupuestoPorVendedor ───────────────────────────────────────────────

    public function test_presupuesto_por_vendedor_calcula_cumplimiento_correctamente(): void
    {
        $this->repo->method('presupuestoPorAsesor')->willReturn(collect([
            $this->presupuestoObj(1, 'Juan', 1_000_000),
        ]));
        $this->repo->method('codsPorAsesor')->willReturn(collect([
            (object) ['asesor_id' => 1, 'cod_vendedor_siesa' => 'V001'],
        ]));
        $this->repo->method('logradoVendedoresYtd')->willReturn(collect([
            (object) ['COD_VENDEDOR' => 'V001', 'logrado' => '500000.00'],
        ]));
        $this->repo->method('forecastPipelineAsesor')->willReturn(collect([
            (object) ['asesor_id' => 1, 'forecast' => 200_000],
        ]));

        $resultado = $this->servicio()->presupuestoPorVendedor();

        $this->assertSame(50, $resultado->first()['cumpl']);
    }

    public function test_presupuesto_por_vendedor_usa_nombre_del_asesor(): void
    {
        $this->repo->method('presupuestoPorAsesor')->willReturn(collect([
            $this->presupuestoObj(1, 'María López', 500_000),
        ]));
        $this->repo->method('codsPorAsesor')->willReturn(collect());
        $this->repo->method('logradoVendedoresYtd')->willReturn(collect());
        $this->repo->method('forecastPipelineAsesor')->willReturn(collect());

        $resultado = $this->servicio()->presupuestoPorVendedor();

        $this->assertSame('María López', $resultado->first()['vendedor']);
    }

    public function test_presupuesto_por_vendedor_cumpl_es_cero_cuando_erp_falla(): void
    {
        $this->repo->method('presupuestoPorAsesor')->willReturn(collect([
            $this->presupuestoObj(1, 'Juan', 1_000_000),
        ]));
        $this->repo->method('codsPorAsesor')->willReturn(collect([
            (object) ['asesor_id' => 1, 'cod_vendedor_siesa' => 'V001'],
        ]));
        $this->repo->method('logradoVendedoresYtd')->willThrowException(new \RuntimeException('ERP caído'));
        $this->repo->method('forecastPipelineAsesor')->willReturn(collect());

        $resultado = $this->servicio()->presupuestoPorVendedor();

        $this->assertSame(0, $resultado->first()['cumpl']);
    }

    public function test_presupuesto_por_vendedor_retorna_coleccion_vacia_sin_presupuestos(): void
    {
        $this->repo->method('presupuestoPorAsesor')->willReturn(collect());
        $this->repo->method('codsPorAsesor')->willReturn(collect());
        $this->repo->method('logradoVendedoresYtd')->willReturn(collect());
        $this->repo->method('forecastPipelineAsesor')->willReturn(collect());

        $resultado = $this->servicio()->presupuestoPorVendedor();

        $this->assertTrue($resultado->isEmpty());
    }

    public function test_presupuesto_por_vendedor_estructura_correcta(): void
    {
        $this->repo->method('presupuestoPorAsesor')->willReturn(collect([
            $this->presupuestoObj(1, 'Ana', 800_000),
        ]));
        $this->repo->method('codsPorAsesor')->willReturn(collect());
        $this->repo->method('logradoVendedoresYtd')->willReturn(collect());
        $this->repo->method('forecastPipelineAsesor')->willReturn(collect());

        $resultado = $this->servicio()->presupuestoPorVendedor();

        foreach (['asesor_id', 'vendedor', 'presupuesto_anual', 'logrado_ytd', 'forecast_pipeline', 'cumpl'] as $clave) {
            $this->assertArrayHasKey($clave, $resultado->first());
        }
    }

    // ─── actividadEquipo ─────────────────────────────────────────────────────

    public function test_actividad_equipo_pivota_tipos_por_vendedor(): void
    {
        $rows = collect([
            (object) ['vendedor' => 'Juan', 'tipo' => 'llamada', 'num_actividades' => 5, 'exitosas' => 3],
            (object) ['vendedor' => 'Juan', 'tipo' => 'visita',  'num_actividades' => 2, 'exitosas' => 1],
            (object) ['vendedor' => 'Juan', 'tipo' => 'email',   'num_actividades' => 8, 'exitosas' => 4],
        ]);

        $this->repo->method('actividadEquipo')->willReturn($rows);

        $resultado = $this->servicio()->actividadEquipo();

        $juan = $resultado->first();
        $this->assertSame(5, $juan['llamada']);
        $this->assertSame(2, $juan['visita']);
        $this->assertSame(8, $juan['email']);
    }

    public function test_actividad_equipo_inicializa_tipos_faltantes_en_cero(): void
    {
        $rows = collect([
            (object) ['vendedor' => 'María', 'tipo' => 'llamada', 'num_actividades' => 3, 'exitosas' => 2],
        ]);

        $this->repo->method('actividadEquipo')->willReturn($rows);

        $resultado = $this->servicio()->actividadEquipo();

        $maria = $resultado->first();
        $this->assertSame(0, $maria['visita']);
        $this->assertSame(0, $maria['email']);
    }

    public function test_actividad_equipo_separa_multiples_vendedores(): void
    {
        $rows = collect([
            (object) ['vendedor' => 'Juan',  'tipo' => 'llamada', 'num_actividades' => 3, 'exitosas' => 1],
            (object) ['vendedor' => 'María', 'tipo' => 'visita',  'num_actividades' => 4, 'exitosas' => 2],
        ]);

        $this->repo->method('actividadEquipo')->willReturn($rows);

        $resultado = $this->servicio()->actividadEquipo();

        $this->assertCount(2, $resultado);
    }

    // ─── delegación a repo ───────────────────────────────────────────────────

    public function test_ciclo_de_venta_delega_al_repo_con_meses(): void
    {
        $meses = ['2026-04', '2026-05', '2026-06'];
        $esperado = collect([(object) ['vendedor' => 'Juan', 'dias' => 30]]);

        $this->repo->expects($this->once())->method('cicloDeVenta')->with($meses)->willReturn($esperado);

        $resultado = $this->servicio($meses)->cicloDeVenta();

        $this->assertSame($esperado, $resultado);
    }

    public function test_motivos_de_perdida_delega_al_repo_con_meses(): void
    {
        $meses = ['2026-01', '2026-02', '2026-03'];
        $esperado = collect([(object) ['motivo' => 'Precio', 'valor' => 2]]);

        $this->repo->expects($this->once())->method('motivosDePerdida')->with($meses)->willReturn($esperado);

        $resultado = $this->servicio($meses)->motivosDePerdida();

        $this->assertSame($esperado, $resultado);
    }

    // ─── retencionChurn ──────────────────────────────────────────────────────

    public function test_retencion_churn_retorna_coleccion_vacia_cuando_erp_falla(): void
    {
        $this->repo->method('retencionChurn')->willThrowException(new \RuntimeException('ERP caído'));

        $resultado = $this->servicio()->retencionChurn();

        $this->assertInstanceOf(Collection::class, $resultado);
        $this->assertTrue($resultado->isEmpty());
    }

    public function test_retencion_churn_retorna_datos_del_repo_cuando_erp_responde(): void
    {
        $bandas = collect([
            (object) ['banda' => '1-Activo', 'num_clientes' => 10, 'valor_en_banda' => 500_000],
        ]);

        $this->repo->method('retencionChurn')->willReturn($bandas);

        $resultado = $this->servicio()->retencionChurn();

        $this->assertCount(1, $resultado);
    }
}
