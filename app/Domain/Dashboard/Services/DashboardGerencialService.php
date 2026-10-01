<?php

declare(strict_types=1);

namespace App\Domain\Dashboard\Services;

use App\Domain\Dashboard\Repositories\DashboardGerencialRepositoryInterface;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Throwable;

class DashboardGerencialService
{
    public function __construct(
        private readonly DashboardGerencialRepositoryInterface $repo,
        private readonly int $compania,
        private readonly int $anio,
        private readonly array $meses,
    ) {}

    public function presupuestoPorVendedor(): Collection
    {
        $presupuestos = $this->repo->presupuestoPorAsesor($this->compania, $this->anio);

        // Un asesor puede tener equivalencias en varias compañías; agrupamos todos sus códigos
        $codsPorAsesor = $this->repo->codsPorAsesor($this->compania)
            ->groupBy('asesor_id')
            ->map(fn ($rows) => $rows->pluck('cod_vendedor_siesa')
                ->map(fn ($c) => trim($c))
                ->filter()
                ->values()
            );

        try {
            $logrado = $this->repo->logradoVendedoresYtd($this->compania, $this->meses)->keyBy('COD_VENDEDOR');
        } catch (Throwable) {
            $logrado = collect();
        }

        $forecast = $this->repo->forecastPipelineAsesor()->pluck('forecast', 'asesor_id');

        return $presupuestos->map(function ($p) use ($codsPorAsesor, $logrado, $forecast) {
            $codes = $codsPorAsesor[$p->asesor_id] ?? collect();

            // Suma logrado de todos los códigos del asesor (puede tener uno por compañía)
            $log   = $codes->sum(fn ($cod) => (float) ($logrado[$cod]?->logrado ?? 0));
            $prsup = (float) $p->presupuesto;
            $cumpl = $prsup > 0 ? (int) round($log * 100 / $prsup) : 0;

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

    public function logradoTotal(): float
    {
        try {
            return Cache::remember(
                "gerencial:logrado:{$this->compania}:{$this->anio}",
                now()->addMinutes(30),
                fn () => $this->repo->logradoTotal($this->compania, $this->anio)
            );
        } catch (Throwable) {
            return 0.0;
        }
    }

    public function cicloDeVenta(): Collection
    {
        return $this->repo->cicloDeVenta($this->meses);
    }

    public function motivosDePerdida(): Collection
    {
        return $this->repo->motivosDePerdida($this->meses);
    }

    public function retencionChurn(): Collection
    {
        try {
            return Cache::remember(
                "gerencial:churn:{$this->compania}",
                now()->addHours(1),
                fn () => $this->repo->retencionChurn($this->compania)
            );
        } catch (Throwable) {
            return collect();
        }
    }

    /**
     * Informe Comercial: agrupa todas las métricas de pedidos pendientes/facturación
     * para un año, mes y compañía dados. Independiente del período (mes/trimestre/año)
     * del resto del dashboard, porque este bloque tiene su propio filtro local (Año/Mes/Compañía).
     */
    public function informeComercial(?int $compania = null, ?int $anio = null, ?int $mes = null, bool $incluirFuturos = false): array
    {
        $compania ??= $this->compania;
        $anio     ??= $this->anio;
        $mes      ??= now()->month;

        $cacheKey = "gerencial:informe:{$compania}:{$anio}:{$mes}:" . ($incluirFuturos ? '1' : '0');

        return Cache::remember($cacheKey, now()->addMinutes(15), function () use ($compania, $anio, $mes, $incluirFuturos) {
            $safe = fn (callable $fn) => rescue($fn, collect(), false);

            $pendientes = rescue(
                fn () => $this->repo->pendientesDesglosados($compania, $anio, $mes),
                ['atrasados' => collect(), 'mes' => collect(), 'total' => collect()],
                false
            );

            return [
                'compania'             => $compania,
                'anio'                 => $anio,
                'mes'                  => $mes,
                'incluirFuturos'       => $incluirFuturos,
                'facturadoMes'         => $safe(fn () => $this->repo->facturadoDelMes($compania, $anio, $mes)),
                'cierresProximos'      => $safe(fn () => $this->repo->cierresProximos($compania)),
                'pedidosPorCerrar'     => $safe(fn () => $this->repo->pedidosPorCerrarDetalle($compania)),
                'facturacionTendencia' => $safe(fn () => $this->repo->facturacionMensualTendencia($compania, $anio, $mes)),
                'facturacionCliente'   => $safe(fn () => $this->repo->facturacionPorCliente($compania, $anio, $mes)),
                'facturacionVendedor'  => $safe(fn () => $this->repo->facturacionPorVendedor($compania, $anio, $mes)),
                'canastaResumen'       => $safe(fn () => $this->repo->canastaResumen($compania, $mes, $incluirFuturos)),
                'canastaDetalle'       => $safe(fn () => $this->repo->canastaDetalle($compania, $mes, $incluirFuturos)),
                'periodoPendientes'    => ['anio' => $anio, 'mes' => $mes],
                'pendientesAtrasados'  => $pendientes['atrasados'],
                'pendientesMes'        => $pendientes['mes'],
                'pendientesTotal'      => $pendientes['total'],
            ];
        });
    }

    public function cumplimientoPresupuestal(): array
    {
        return Cache::remember(
            "gerencial:cumpl:{$this->compania}:{$this->anio}",
            now()->addMinutes(30),
            function () {
                $presupuestos = $this->repo->presupuestoMensualConsolidado($this->compania, $this->anio)
                    ->pluck('presupuesto', 'mes');

                try {
                    $facturado = $this->repo->facturadoMensualAnio($this->compania, $this->anio)
                        ->pluck('facturado', 'mes');
                } catch (Throwable) {
                    $facturado = collect();
                }

                $nombres = ['Ene','Feb','Mar','Abr','May','Jun','Jul','Ago','Sep','Oct','Nov','Dic'];

                return collect(range(1, 12))->map(function (int $mes) use ($presupuestos, $facturado, $nombres) {
                    $pres = (float) ($presupuestos[$mes] ?? 0);
                    $fact = (float) ($facturado[$mes] ?? 0);

                    return [
                        'mes'          => $mes,
                        'nombre_mes'   => $nombres[$mes - 1],
                        'presupuesto'  => round($pres, 2),
                        'facturado'    => round($fact, 2),
                        'cumplimiento' => $pres > 0 ? round($fact / $pres * 100, 1) : null,
                        'diferencia'   => round($fact - $pres, 2),
                    ];
                })->toArray();
            }
        );
    }

    public function actividadEquipo(): Collection
    {
        $rows = $this->repo->actividadEquipo($this->meses);

        // Pivota tipo de actividad por vendedor para la vista
        return $rows
            ->groupBy('vendedor')
            ->map(function (Collection $actividades, string $vendedor) {
                $row = ['vendedor' => $vendedor, 'llamada' => 0, 'visita' => 0, 'email' => 0, 'reunion' => 0, 'whatsapp' => 0, 'otro' => 0];
                foreach ($actividades as $a) {
                    $row[$a->tipo] = (int) $a->num_actividades;
                }
                return $row;
            })
            ->values();
    }
}
