<?php

declare(strict_types=1);

namespace App\Domain\Dashboard\Services;

use App\Domain\Dashboard\Repositories\DashboardRepositoryInterface;
use App\Domain\ERP\Contracts\ERPRepositoryInterface;
use Throwable;

class DashboardService
{
    public function __construct(
        private readonly DashboardRepositoryInterface $repo,
        private readonly ERPRepositoryInterface $erp,
    ) {}

    public function kpis(int $userId, bool $esAsesor, ?string $filtroVendedor = null): array
    {
        $filtroUserId = $esAsesor ? $userId : null;

        return [
            'clientes'    => $this->repo->estadisticasClientes($filtroUserId),
            'seguimientos' => [
                'ultimo_mes'      => $this->repo->totalSeguimientosUltimoDias(30, $filtroUserId),
                'proximos_7_dias' => $this->repo->totalProximosSeguimientos(7, $filtroUserId),
            ],
            'alertas' => [
                'clientes_sin_seguimiento_reciente' => $this->repo->totalClientesSinSeguimientoReciente(30, $filtroUserId),
            ],
            'pipeline'         => $this->repo->estadisticasPipeline($filtroUserId),
            'forecast'         => $this->repo->forecastResumen($filtroUserId),
            'proximos_cierres' => $this->repo->negociosProximosCierre(30, $filtroUserId),
            'top_asesores'     => $esAsesor ? [] : $this->repo->topAsesores(),
            'conversion'       => $this->repo->tasaConversion($filtroUserId),
            'aging'            => $this->repo->agingNegocios($filtroUserId),
            'inteligencia'     => $this->inteligenciaComercial($esAsesor, $filtroVendedor),
        ];
    }

    private function inteligenciaComercial(bool $esAsesor, ?string $filtroVendedor = null): array
    {
        $disponible = false;
        try {
            $disponible = $this->erp->isAvailable();
        } catch (Throwable) {}

        if (! $disponible) {
            return $this->inteligenciaVacia();
        }

        // Cada llamada es independiente: un fallo en una no bloquea las demás.
        $safe = function (callable $fn): array {
            try {
                return $fn();
            } catch (Throwable) {
                return [];
            }
        };

        $v = $filtroVendedor;

        return [
            'atencion_inmediata'    => $safe(fn () => $this->erp->clientesAtencionInmediata(15, $v)),
            'rescate'               => $safe(fn () => $this->erp->clientesRescate(15, $v)),
            'en_fuga'               => $safe(fn () => $this->erp->clientesEnFuga(20, $v)),
            'expansion'             => $safe(fn () => $this->erp->clientesExpansion(20, $v)),
            'panorama_gerencial'    => $esAsesor ? [] : $safe(fn () => $this->erp->panoramaGerencial()),
            'integrales'            => $esAsesor ? [] : $safe(fn () => $this->erp->clientesIntegrales(30)),
            'presupuesto_activo'    => $safe(fn () => $this->erp->clientesPresupuestoActivo(30, $v)),
            'presupuesto_en_riesgo' => $safe(fn () => $this->erp->clientesPresupuestoEnRiesgo(30, $v)),
            'presupuesto_recuperar' => $safe(fn () => $this->erp->clientesPresupuestoRecuperar(30, $v)),
            'largo_plazo'           => $safe(fn () => $this->erp->clientesLargoPlazo(30, $v)),
            'panorama_presupuestal' => $esAsesor ? [] : $safe(fn () => $this->erp->panoramaPresupuestal()),
            'disponible'            => true,
        ];
    }

    private function inteligenciaVacia(): array
    {
        return [
            'atencion_inmediata'    => [],
            'rescate'               => [],
            'en_fuga'               => [],
            'expansion'             => [],
            'panorama_gerencial'    => [],
            'integrales'            => [],
            'presupuesto_activo'    => [],
            'presupuesto_en_riesgo' => [],
            'presupuesto_recuperar' => [],
            'largo_plazo'           => [],
            'panorama_presupuestal' => [],
            'disponible'            => false,
        ];
    }
}
