<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web;

use App\Domain\Dashboard\Repositories\DashboardGerencialRepositoryInterface;
use App\Domain\Dashboard\Repositories\DashboardVendedorRepositoryInterface;
use App\Domain\Dashboard\Services\DashboardGerencialService;
use App\Domain\Dashboard\Services\DashboardService;
use App\Domain\Dashboard\Services\DashboardVendedorService;
use App\Domain\ERP\Contracts\ERPRepositoryInterface;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardWebController extends Controller
{
    public function __construct(
        private readonly DashboardService $service,
        private readonly DashboardVendedorRepositoryInterface $vendedorRepo,
        private readonly DashboardGerencialRepositoryInterface $gerencialRepo,
        private readonly ERPRepositoryInterface $erp,
    ) {}

    public function index(Request $request): View
    {
        $user     = $request->user();
        $esAsesor = $user->hasRole('comercial');
        $filtroVendedor = $esAsesor
            ? $this->vendedorRepo->nombreVendedorSiesa($user->id)
            : null;
        $kpis = $this->service->kpis($user->id, $esAsesor, $filtroVendedor);

        return view('dashboard.index', compact('kpis'));
    }

    public function vendedor(Request $request): View
    {
        $user      = $request->user();
        $anio      = (int) $request->input('anio', now()->year);
        $periodo   = $request->input('periodo', 'anio');
        $meses     = $this->mesesDelPeriodo($periodo, $anio);

        $companias = $this->vendedorRepo->companiasDelAsesor($user->id);
        $compania  = in_array((int) $request->input('compania'), $companias)
            ? (int) $request->input('compania')
            : ($companias[0] ?? (int) config('crm.compania', 1));

        $svc = new DashboardVendedorService($this->vendedorRepo, $user->id, $compania, $anio, $meses);

        return view('dashboards.vendedor', [
            'kpi'               => $svc->presupuestoVsLogrado(),
            'actividades'       => $svc->proximasActividades(),
            'pipeline'          => $svc->pipelinePersonal(),
            'sinContacto'       => $svc->clientesSinContacto(),
            'ranking'           => $svc->posicionEnEquipo(),
            'anio'              => $anio,
            'periodo'           => $periodo,
            'compania'          => $compania,
            'companias'         => $companias,
        ]);
    }

    private function mesesDelPeriodo(string $periodo, int $anio): array
    {
        $hoy = now();

        return match ($periodo) {
            'mes' => [$hoy->format('Y-m')],

            'trimestre' => collect(range(
                (int) ceil($hoy->month / 3) * 3 - 2,
                (int) ceil($hoy->month / 3) * 3
            ))->map(fn ($m) => sprintf('%d-%02d', $anio, $m))->toArray(),

            default => collect(range(1, 12))
                ->map(fn ($m) => sprintf('%d-%02d', $anio, $m))
                ->toArray(),
        };
    }

    public function clientesPanorama(Request $request): JsonResponse
    {
        $vendedor  = $request->input('vendedor', '');
        $horizonte = strtoupper($request->input('horizonte', ''));
        $compania  = in_array((int) $request->input('cia'), [0, 1, 2]) ? (int) $request->input('cia') : 0;

        if (!$vendedor || !in_array($horizonte, ['P1', 'P2', 'P3', 'P4'])) {
            return response()->json(['error' => 'Parámetros inválidos'], 422);
        }

        try {
            $clientes = $this->erp->clientesPorVendedorYHorizonte($vendedor, $horizonte, $compania);
            return response()->json($clientes);
        } catch (\Throwable) {
            return response()->json(['error' => 'No se pudo consultar el ERP'], 500);
        }
    }

    public function gerencial(Request $request): View
    {
        $anio        = (int) $request->input('anio', now()->year);
        $periodo     = $request->input('periodo', 'anio');
        $companiaErp = in_array((int) $request->input('cia'), [0, 1, 2]) ? (int) $request->input('cia') : 0;
        $compania    = $companiaErp;
        $meses       = $this->mesesDelPeriodo($periodo, $anio);

        $svc = new DashboardGerencialService($this->gerencialRepo, $compania, $anio, $meses);

        $safe = fn (callable $fn) => rescue($fn, [], false);

        return view('dashboards.gerencial', [
            'vendedores'           => $svc->presupuestoPorVendedor(),
            'ciclo'                => $svc->cicloDeVenta(),
            'motivos'              => $svc->motivosDePerdida(),
            'churn'                => $svc->retencionChurn(),
            'actividad'            => $svc->actividadEquipo(),
            'topAsesores'          => $this->service->topAsesores(),
            'integrales'           => $safe(fn () => $this->erp->clientesIntegrales(50)),
            'panoramaGerencial'    => $safe(fn () => $this->erp->panoramaGerencial($companiaErp)),
            'panoramaPresupuestal' => $safe(fn () => $this->erp->panoramaPresupuestal($companiaErp)),
            'informeComercial'     => $svc->informeComercial(),
            'anio'                 => $anio,
            'periodo'              => $periodo,
            'cia'                  => $companiaErp,
        ]);
    }

    /**
     * Refresca la sección "Informe Comercial" según su propio filtro local
     * (Año/Mes/Compañía), independiente del filtro de período del resto del dashboard.
     */
    public function informeComercial(Request $request): JsonResponse
    {
        [$compania, $anio, $mes] = $this->parametrosInformeComercial($request);

        $svc = new DashboardGerencialService($this->gerencialRepo, $compania, $anio, [sprintf('%d-%02d', $anio, $mes)]);

        return response()->json($svc->informeComercial($compania, $anio, $mes));
    }

    /** Drill-down: líneas de pedidos pendientes de un cliente puntual. */
    public function informeComercialDetalleCliente(Request $request): JsonResponse
    {
        [$compania, $anio, $mes] = $this->parametrosInformeComercial($request);
        $cliente = (string) $request->input('cliente', '');

        if ($cliente === '') {
            return response()->json(['error' => 'Cliente requerido'], 422);
        }

        $svc = new DashboardGerencialService($this->gerencialRepo, $compania, $anio, [sprintf('%d-%02d', $anio, $mes)]);

        return response()->json($svc->pedidosPendientesDetalleCliente($compania, $anio, $mes, $cliente));
    }

    /** @return array{0: int, 1: int, 2: int} [compania, anio, mes] */
    private function parametrosInformeComercial(Request $request): array
    {
        $compania = in_array((int) $request->input('cia'), [0, 1, 2]) ? (int) $request->input('cia') : 0;
        $anio     = (int) $request->input('anio', now()->year);
        $mes      = (int) $request->input('mes', now()->month);
        $mes      = ($mes >= 1 && $mes <= 12) ? $mes : now()->month;

        return [$compania, $anio, $mes];
    }
}
