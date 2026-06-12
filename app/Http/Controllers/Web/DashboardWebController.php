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
            'anio'                 => $anio,
            'periodo'              => $periodo,
            'cia'                  => $companiaErp,
        ]);
    }
}
