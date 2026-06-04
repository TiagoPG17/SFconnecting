<?php

declare(strict_types=1);

namespace App\Domain\Dashboard\Services;

use App\Domain\Dashboard\Repositories\DashboardGerencialRepositoryInterface;
use Illuminate\Support\Collection;
use Throwable;

class DashboardGerencialService
{
    public function __construct(
        private readonly DashboardGerencialRepositoryInterface $repo,
        private readonly int $compania,
        private readonly int $anio,
    ) {}

    public function presupuestoPorVendedor(): Collection
    {
        $presupuestos  = $this->repo->presupuestoPorAsesor($this->compania, $this->anio);
        $codsPorAsesor = $this->repo->codsPorAsesor($this->compania)->keyBy('asesor_id');

        try {
            $logrado = $this->repo->logradoVendedoresYtd($this->compania, $this->anio)->keyBy('COD_VENDEDOR');
        } catch (Throwable) {
            $logrado = collect();
        }

        $forecast = $this->repo->forecastPipelineAsesor()->pluck('forecast', 'asesor_id');

        return $presupuestos->map(function ($p) use ($codsPorAsesor, $logrado, $forecast) {
            $cod = trim($codsPorAsesor[$p->asesor_id]?->cod_vendedor_siesa ?? '');

            $log = (float) ($logrado[$cod]?->logrado ?? 0);
            $prsup          = (float) $p->presupuesto;
            $cumpl          = $prsup > 0 ? (int) round($log * 100 / $prsup) : 0;

            return [
                'asesor_id'         => $p->asesor_id,
                'vendedor'          => $p->asesor?->name ?? '—',
                'presupuesto_anual' => $prsup,
                'logrado_ytd'       => $log,
                'forecast_pipeline' => (float) ($forecast[$p->asesor_id] ?? 0),
                'cumpl'             => $cumpl,
            ];
        })->values();
    }

    public function cicloDeVenta(): Collection
    {
        return $this->repo->cicloDeVenta($this->anio);
    }

    public function motivosDePerdida(): Collection
    {
        return $this->repo->motivosDePerdida($this->anio);
    }

    public function retencionChurn(): Collection
    {
        try {
            return $this->repo->retencionChurn($this->compania);
        } catch (Throwable) {
            return collect();
        }
    }

    public function actividadEquipo(): Collection
    {
        $rows = $this->repo->actividadEquipo($this->anio);

        // Pivota tipo de actividad por vendedor para la vista
        return $rows
            ->groupBy('vendedor')
            ->map(function (Collection $actividades, string $vendedor) {
                $row = ['vendedor' => $vendedor, 'llamada' => 0, 'visita' => 0, 'email' => 0];
                foreach ($actividades as $a) {
                    $row[$a->tipo] = (int) $a->num_actividades;
                }
                return $row;
            })
            ->values();
    }
}
