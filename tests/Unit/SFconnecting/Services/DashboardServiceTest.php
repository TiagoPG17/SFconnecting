<?php

declare(strict_types=1);

namespace Tests\Unit\SFconnecting\Services;

use App\Domain\Dashboard\Repositories\DashboardRepositoryInterface;
use App\Domain\Dashboard\Services\DashboardService;
use App\Domain\ERP\Contracts\ERPRepositoryInterface;
use PHPUnit\Framework\MockObject\MockObject;
use Tests\TestCase;

class DashboardServiceTest extends TestCase
{
    private DashboardService $service;

    /** @var DashboardRepositoryInterface&MockObject */
    private DashboardRepositoryInterface $repo;

    /** @var ERPRepositoryInterface&MockObject */
    private ERPRepositoryInterface $erp;

    protected function setUp(): void
    {
        parent::setUp();

        $this->repo    = $this->createMock(DashboardRepositoryInterface::class);
        $this->erp     = $this->createMock(ERPRepositoryInterface::class);
        $this->service = new DashboardService($this->repo, $this->erp);
    }

    public function test_kpis_asesor_filtra_por_user_id(): void
    {
        $userId = 7;

        $this->repo->expects($this->once())->method('estadisticasClientes')->with($userId)
            ->willReturn(['total' => 5, 'activos' => 3, 'inactivos' => 1, 'prospectos' => 1]);

        $this->repo->expects($this->once())->method('totalSeguimientosUltimoDias')->with(30, $userId)
            ->willReturn(10);

        $this->repo->expects($this->once())->method('totalProximosSeguimientos')->with(7, $userId)
            ->willReturn(2);

        $this->repo->expects($this->once())->method('totalClientesSinSeguimientoReciente')->with(30, $userId)
            ->willReturn(3);

        $resultado = $this->service->kpis($userId, esAsesor: true);

        $this->assertArrayHasKey('clientes', $resultado);
        $this->assertArrayHasKey('seguimientos', $resultado);
        $this->assertArrayHasKey('alertas', $resultado);
    }

    public function test_kpis_admin_no_filtra_por_user_id(): void
    {
        $this->repo->expects($this->once())->method('estadisticasClientes')->with(null)
            ->willReturn(['total' => 100, 'activos' => 60, 'inactivos' => 20, 'prospectos' => 20]);

        $this->repo->expects($this->once())->method('totalSeguimientosUltimoDias')->with(30, null)
            ->willReturn(50);

        $this->repo->expects($this->once())->method('totalProximosSeguimientos')->with(7, null)
            ->willReturn(15);

        $this->repo->expects($this->once())->method('totalClientesSinSeguimientoReciente')->with(30, null)
            ->willReturn(20);

        $resultado = $this->service->kpis(1, esAsesor: false);

        $this->assertSame(100, $resultado['clientes']['total']);
    }

    public function test_estructura_kpis_es_correcta(): void
    {
        $this->repo->method('estadisticasClientes')
            ->willReturn(['total' => 10, 'activos' => 6, 'inactivos' => 2, 'prospectos' => 2]);
        $this->repo->method('totalSeguimientosUltimoDias')->willReturn(8);
        $this->repo->method('totalProximosSeguimientos')->willReturn(3);
        $this->repo->method('totalClientesSinSeguimientoReciente')->willReturn(1);

        $kpis = $this->service->kpis(1, esAsesor: true);

        $this->assertArrayHasKey('total', $kpis['clientes']);
        $this->assertArrayHasKey('activos', $kpis['clientes']);
        $this->assertArrayHasKey('inactivos', $kpis['clientes']);
        $this->assertArrayHasKey('prospectos', $kpis['clientes']);
        $this->assertArrayHasKey('ultimo_mes', $kpis['seguimientos']);
        $this->assertArrayHasKey('proximos_7_dias', $kpis['seguimientos']);
        $this->assertArrayHasKey('clientes_sin_seguimiento_reciente', $kpis['alertas']);
    }
}

