<?php

declare(strict_types=1);

namespace Tests\Unit\SFconnecting\Services;

use App\Domain\Dashboard\Repositories\DashboardVendedorRepositoryInterface;
use App\Domain\Dashboard\Services\DashboardVendedorService;
use Illuminate\Support\Collection;
use PHPUnit\Framework\MockObject\MockObject;
use Tests\TestCase;

class DashboardVendedorServiceTest extends TestCase
{
    /** @var DashboardVendedorRepositoryInterface&MockObject */
    private DashboardVendedorRepositoryInterface $repo;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repo = $this->createMock(DashboardVendedorRepositoryInterface::class);
    }

    /** Año completo pasado: diasTrx = diasTotal = 365, cálculo exacto sin setTestNow */
    private function meses2025(): array
    {
        return collect(range(1, 12))->map(fn ($m) => sprintf('2025-%02d', $m))->toArray();
    }

    private function servicio(array $meses = ['2026-01'], int $asesorId = 1, int $anio = 2026): DashboardVendedorService
    {
        return new DashboardVendedorService($this->repo, $asesorId, 2, $anio, $meses);
    }

    // ─── presupuestoVsLogrado ─────────────────────────────────────────────────

    public function test_retorna_error_cuando_vendedor_no_esta_mapeado_en_siesa(): void
    {
        $this->repo->method('presupuestoVendedor')->willReturn(null);
        $this->repo->method('codVendedorSiesa')->willReturn(null);

        $resultado = $this->servicio()->presupuestoVsLogrado();

        $this->assertFalse($resultado['ok']);
    }

    public function test_semaforo_es_verde_cuando_ritmo_mayor_o_igual_100(): void
    {
        // Año 2025 completo: diasTrx = diasTotal = 365, presupuestoPer = presupuesto_anual
        $this->repo->method('presupuestoVendedor')->willReturn((object) ['presupuesto' => '1000000.00']);
        $this->repo->method('codVendedorSiesa')->willReturn('V001');
        $this->repo->method('logradoYtd')->willReturn(1_000_000.0); // 100% del presupuesto

        $resultado = $this->servicio($this->meses2025(), anio: 2025)->presupuestoVsLogrado();

        $this->assertSame('verde', $resultado['semaforo']);
    }

    public function test_semaforo_es_amarillo_entre_80_y_99_pct(): void
    {
        $this->repo->method('presupuestoVendedor')->willReturn((object) ['presupuesto' => '1000000.00']);
        $this->repo->method('codVendedorSiesa')->willReturn('V001');
        $this->repo->method('logradoYtd')->willReturn(850_000.0); // 85% del presupuesto

        $resultado = $this->servicio($this->meses2025(), anio: 2025)->presupuestoVsLogrado();

        $this->assertSame('amarillo', $resultado['semaforo']);
    }

    public function test_semaforo_es_rojo_cuando_ritmo_menor_80_pct(): void
    {
        $this->repo->method('presupuestoVendedor')->willReturn((object) ['presupuesto' => '1000000.00']);
        $this->repo->method('codVendedorSiesa')->willReturn('V001');
        $this->repo->method('logradoYtd')->willReturn(500_000.0); // 50% del presupuesto

        $resultado = $this->servicio($this->meses2025(), anio: 2025)->presupuestoVsLogrado();

        $this->assertSame('rojo', $resultado['semaforo']);
    }

    public function test_logrado_es_cero_cuando_erp_lanza_excepcion(): void
    {
        $this->repo->method('presupuestoVendedor')->willReturn((object) ['presupuesto' => '1000000.00']);
        $this->repo->method('codVendedorSiesa')->willReturn('V001');
        $this->repo->method('logradoYtd')->willThrowException(new \RuntimeException('ERP caído'));

        $resultado = $this->servicio($this->meses2025(), anio: 2025)->presupuestoVsLogrado();

        $this->assertSame(0.0, $resultado['logrado_ytd']);
    }

    public function test_estructura_completa_cuando_vendedor_esta_mapeado(): void
    {
        $this->repo->method('presupuestoVendedor')->willReturn((object) ['presupuesto' => '1000000.00']);
        $this->repo->method('codVendedorSiesa')->willReturn('V001');
        $this->repo->method('logradoYtd')->willReturn(700_000.0);

        $resultado = $this->servicio($this->meses2025(), anio: 2025)->presupuestoVsLogrado();

        foreach (['ok', 'presupuesto_anual', 'logrado_ytd', 'avance_pct', 'esperado_pct', 'ritmo_pct', 'semaforo'] as $clave) {
            $this->assertArrayHasKey($clave, $resultado);
        }
    }

    // ─── proximasActividades ─────────────────────────────────────────────────

    public function test_proximas_actividades_usa_razon_social_del_cliente(): void
    {
        $seguimiento = (object) [
            'id'           => 1,
            'tipo'         => 'llamada',
            'descripcion'  => 'Llamar cliente',
            'proxima_fecha' => now()->addDays(3),
            'cliente'      => (object) ['razon_social' => 'Empresa SA'],
            'prospecto'    => null,
        ];

        $this->repo->method('proximasActividades')->willReturn(collect([$seguimiento]));

        $resultado = $this->servicio()->proximasActividades();

        $this->assertSame('Empresa SA', $resultado->first()['entidad']);
    }

    public function test_proximas_actividades_usa_empresa_del_prospecto_cuando_no_hay_cliente(): void
    {
        $seguimiento = (object) [
            'id'           => 2,
            'tipo'         => 'visita',
            'descripcion'  => 'Visitar prospecto',
            'proxima_fecha' => now()->addDays(1),
            'cliente'      => null,
            'prospecto'    => (object) ['empresa' => 'Prospecto Ltda'],
        ];

        $this->repo->method('proximasActividades')->willReturn(collect([$seguimiento]));

        $resultado = $this->servicio()->proximasActividades();

        $this->assertSame('Prospecto Ltda', $resultado->first()['entidad']);
    }

    public function test_proximas_actividades_marca_como_vencida_si_fecha_es_pasada(): void
    {
        $seguimiento = (object) [
            'id'           => 3,
            'tipo'         => 'email',
            'descripcion'  => 'Email pendiente',
            'proxima_fecha' => now()->subDays(2),
            'cliente'      => null,
            'prospecto'    => null,
        ];

        $this->repo->method('proximasActividades')->willReturn(collect([$seguimiento]));

        $resultado = $this->servicio()->proximasActividades();

        $this->assertTrue($resultado->first()['vencida']);
    }

    // ─── pipelinePersonal ────────────────────────────────────────────────────

    public function test_pipeline_mapea_campos_de_etapa_correctamente(): void
    {
        $etapa = (object) [
            'id'          => 5,
            'nombre'      => 'Propuesta',
            'color'       => '#3b82f6',
            'num_negocios' => 3,
            'valor_etapa' => '500000.00',
        ];

        $this->repo->method('etapasPipelinePersonal')->willReturn(collect([$etapa]));
        $this->repo->method('diasPromEtapa')->willReturn(12.5);

        $resultado = $this->servicio()->pipelinePersonal();

        $this->assertSame('Propuesta', $resultado->first()['etapa']);
        $this->assertSame(12.5, $resultado->first()['dias_prom_en_etapa']);
    }

    public function test_pipeline_llama_dias_prom_por_cada_etapa(): void
    {
        $etapas = collect([
            (object) ['id' => 1, 'nombre' => 'Contacto', 'color' => '#aaa', 'num_negocios' => 1, 'valor_etapa' => '0'],
            (object) ['id' => 2, 'nombre' => 'Propuesta', 'color' => '#bbb', 'num_negocios' => 2, 'valor_etapa' => '0'],
        ]);

        $this->repo->method('etapasPipelinePersonal')->willReturn($etapas);
        $this->repo->expects($this->exactly(2))->method('diasPromEtapa')->willReturn(0.0);

        $this->servicio()->pipelinePersonal();
    }

    // ─── clientesSinContacto ─────────────────────────────────────────────────

    public function test_clientes_sin_contacto_retorna_coleccion_vacia_cuando_no_hay_clientes(): void
    {
        $this->repo->method('clientesSinContacto30')->willReturn(collect());

        $resultado = $this->servicio()->clientesSinContacto();

        $this->assertTrue($resultado->isEmpty());
    }

    public function test_clientes_sin_contacto_ordena_por_valor_facturado_descendente(): void
    {
        $clientes = collect([
            (object) ['id' => 1, 'razon_social' => 'Baja facturación', 'nit' => '111', 'dias_sin_contacto' => 5],
            (object) ['id' => 2, 'razon_social' => 'Alta facturación', 'nit' => '222', 'dias_sin_contacto' => 10],
        ]);

        $recencia = collect([
            (object) ['NIT' => '111', 'VLR_NETO_FACTURADO' => 1000],
            (object) ['NIT' => '222', 'VLR_NETO_FACTURADO' => 9000],
        ]);

        $this->repo->method('clientesSinContacto30')->willReturn($clientes);
        $this->repo->method('recenciaClientes')->willReturn($recencia);

        $resultado = $this->servicio()->clientesSinContacto();

        $this->assertSame('Alta facturación', $resultado->first()['razon_social']);
    }

    public function test_clientes_sin_contacto_usa_cero_cuando_erp_falla(): void
    {
        $clientes = collect([
            (object) ['id' => 1, 'razon_social' => 'Empresa', 'nit' => '111', 'dias_sin_contacto' => 5],
        ]);

        $this->repo->method('clientesSinContacto30')->willReturn($clientes);
        $this->repo->method('recenciaClientes')->willThrowException(new \RuntimeException());

        $resultado = $this->servicio()->clientesSinContacto();

        $this->assertSame(0.0, $resultado->first()['vlr_neto_facturado']);
    }

    // ─── posicionEnEquipo ─────────────────────────────────────────────────────

    public function test_posicion_retorna_puesto_correcto_en_ranking(): void
    {
        $ranking = collect([
            (object) ['COD_VENDEDOR' => 'V001', 'logrado' => 50000],
            (object) ['COD_VENDEDOR' => 'V002', 'logrado' => 30000],
            (object) ['COD_VENDEDOR' => 'V003', 'logrado' => 10000],
        ]);

        $this->repo->method('codVendedorSiesa')->willReturn('V002');
        $this->repo->method('rankingVendedores')->willReturn($ranking);

        $resultado = $this->servicio()->posicionEnEquipo();

        $this->assertSame(2, $resultado['mi_puesto']);
    }

    public function test_posicion_retorna_null_cuando_vendedor_no_aparece_en_ranking(): void
    {
        $this->repo->method('codVendedorSiesa')->willReturn(null);
        $this->repo->method('rankingVendedores')->willReturn(collect());

        $resultado = $this->servicio()->posicionEnEquipo();

        $this->assertNull($resultado['mi_puesto']);
    }

    public function test_posicion_retorna_total_cero_cuando_erp_falla(): void
    {
        $this->repo->method('codVendedorSiesa')->willReturn('V001');
        $this->repo->method('rankingVendedores')->willThrowException(new \RuntimeException('ERP caído'));

        $resultado = $this->servicio()->posicionEnEquipo();

        $this->assertSame(0, $resultado['total']);
    }
}
