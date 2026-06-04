<?php

declare(strict_types=1);

namespace App\Domain\Reportes\Repositories;

use App\Domain\Clientes\Models\Cliente;
use App\Domain\Negocios\Models\Negocio;
use App\Domain\Prospectos\Models\Prospecto;
use App\Domain\Seguimientos\Models\Seguimiento;
use Illuminate\Support\Carbon;

class ReporteRepository implements ReporteRepositoryInterface
{
    public function reporteClientes(?string $desde = null, ?string $hasta = null): array
    {
        $base = Cliente::query()
            ->when($desde, fn ($q) => $q->where('created_at', '>=', Carbon::parse($desde)->startOfDay()))
            ->when($hasta, fn ($q) => $q->where('created_at', '<=', Carbon::parse($hasta)->endOfDay()));

        $porEstado = (clone $base)
            ->selectRaw('estado, count(*) as total')
            ->groupBy('estado')
            ->pluck('total', 'estado')
            ->toArray();

        $porCiudad = (clone $base)
            ->whereNotNull('ciudad')
            ->selectRaw('ciudad, count(*) as total')
            ->groupBy('ciudad')
            ->orderByDesc('total')
            ->limit(10)
            ->pluck('total', 'ciudad')
            ->toArray();

        return [
            'total'      => (clone $base)->count(),
            'por_estado' => $porEstado,
            'por_ciudad' => $porCiudad,
        ];
    }

    public function reporteSeguimientos(?string $desde = null, ?string $hasta = null): array
    {
        $base = Seguimiento::query()
            ->when($desde, fn ($q) => $q->where('fecha_seguimiento', '>=', Carbon::parse($desde)->startOfDay()))
            ->when($hasta, fn ($q) => $q->where('fecha_seguimiento', '<=', Carbon::parse($hasta)->endOfDay()));

        $porTipo = (clone $base)
            ->selectRaw('tipo, count(*) as total')
            ->groupBy('tipo')
            ->pluck('total', 'tipo')
            ->toArray();

        $porResultado = (clone $base)
            ->selectRaw('resultado, count(*) as total')
            ->groupBy('resultado')
            ->pluck('total', 'resultado')
            ->toArray();

        return [
            'total'         => (clone $base)->count(),
            'por_tipo'      => $porTipo,
            'por_resultado' => $porResultado,
        ];
    }

    public function reporteProspectos(array $filtros = []): array
    {
        $base = Prospecto::query()
            ->when($filtros['asesor_id'] ?? null, fn ($q, $v) => $q->where('asesor_id', $v))
            ->when($filtros['desde'] ?? null, fn ($q, $v) => $q->whereDate('created_at', '>=', $v))
            ->when($filtros['hasta'] ?? null, fn ($q, $v) => $q->whereDate('created_at', '<=', $v));

        $porEstado = (clone $base)
            ->join('sf_pipeline_estados', 'sf_prospectos.estado_pipeline_id', '=', 'sf_pipeline_estados.id')
            ->selectRaw('sf_pipeline_estados.nombre as estado, count(*) as total')
            ->groupBy('sf_pipeline_estados.nombre')
            ->pluck('total', 'estado')
            ->toArray();

        $porOrigen = (clone $base)
            ->join('sf_maestros_comerciales', 'sf_prospectos.origen_id', '=', 'sf_maestros_comerciales.id')
            ->selectRaw('sf_maestros_comerciales.nombre as origen, count(*) as total')
            ->groupBy('sf_maestros_comerciales.nombre')
            ->pluck('total', 'origen')
            ->toArray();

        return [
            'total'      => (clone $base)->count(),
            'convertidos' => (clone $base)->whereNotNull('convertido_cliente_id')->count(),
            'por_estado' => $porEstado,
            'por_origen' => $porOrigen,
        ];
    }

    public function reporteNegocios(array $filtros = []): array
    {
        $base = Negocio::query()
            ->when($filtros['asesor_id'] ?? null, fn ($q, $v) => $q->where('asesor_id', $v))
            ->when($filtros['desde'] ?? null, fn ($q, $v) => $q->whereDate('created_at', '>=', $v))
            ->when($filtros['hasta'] ?? null, fn ($q, $v) => $q->whereDate('created_at', '<=', $v));

        $porEstado = (clone $base)
            ->join('sf_pipeline_estados', 'sf_negocios.pipeline_estado_id', '=', 'sf_pipeline_estados.id')
            ->selectRaw('sf_pipeline_estados.nombre as estado, count(*) as total, sum(sf_negocios.valor_estimado) as valor')
            ->groupBy('sf_pipeline_estados.nombre')
            ->get()
            ->map(fn ($r) => ['estado' => $r->estado, 'total' => $r->total, 'valor' => round((float)$r->valor, 2)])
            ->toArray();

        return [
            'total'       => (clone $base)->count(),
            'valor_total' => round((float)(clone $base)->sum('valor_estimado'), 2),
            'ganados'     => (clone $base)->whereHas('pipelineEstado', fn ($q) => $q->where('es_ganado', true))->count(),
            'perdidos'    => (clone $base)->whereHas('pipelineEstado', fn ($q) => $q->where('es_perdido', true))->count(),
            'por_estado'  => $porEstado,
        ];
    }

    public function reporteForecast(array $filtros = []): array
    {
        $mes  = $filtros['mes'] ?? Carbon::now()->month;
        $anio = $filtros['anio'] ?? Carbon::now()->year;

        $negocios = Negocio::query()
            ->where('activo', true)
            ->when($filtros['asesor_id'] ?? null, fn ($q, $v) => $q->where('asesor_id', $v))
            ->whereHas('pipelineEstado', fn ($q) => $q->where('es_final', false))
            ->when($mes, fn ($q) => $q->whereMonth('fecha_estimada_cierre', $mes))
            ->when($anio, fn ($q) => $q->whereYear('fecha_estimada_cierre', $anio))
            ->with(['pipelineEstado', 'asesor'])
            ->get();

        $porAsesor = $negocios->groupBy('asesor_id')->map(fn ($g) => [
            'asesor'          => $g->first()->asesor?->name ?? 'Sin asignar',
            'cantidad'        => $g->count(),
            'valor_pipeline'  => round((float) $g->sum('valor_estimado'), 2),
            'valor_forecast'  => round($g->sum(fn ($n) => $n->valorForecast()), 2),
        ])->values()->toArray();

        return [
            'mes'             => $mes,
            'anio'            => $anio,
            'total_pipeline'  => round((float) $negocios->sum('valor_estimado'), 2),
            'total_forecast'  => round($negocios->sum(fn ($n) => $n->valorForecast()), 2),
            'cantidad'        => $negocios->count(),
            'por_asesor'      => $porAsesor,
        ];
    }

    public function reporteConversion(array $filtros = []): array
    {
        $base = Prospecto::query()
            ->when($filtros['asesor_id'] ?? null, fn ($q, $v) => $q->where('asesor_id', $v))
            ->when($filtros['desde'] ?? null, fn ($q, $v) => $q->whereDate('created_at', '>=', $v))
            ->when($filtros['hasta'] ?? null, fn ($q, $v) => $q->whereDate('created_at', '<=', $v));

        $total      = (clone $base)->count();
        $convertidos = (clone $base)->whereNotNull('convertido_cliente_id')->count();

        $negociosGanados = Negocio::query()
            ->when($filtros['asesor_id'] ?? null, fn ($q, $v) => $q->where('asesor_id', $v))
            ->whereHas('pipelineEstado', fn ($q) => $q->where('es_ganado', true))
            ->count();

        return [
            'prospectos_total'    => $total,
            'prospectos_convertidos' => $convertidos,
            'tasa_conversion'     => $total > 0 ? round(($convertidos / $total) * 100, 1) : 0,
            'negocios_ganados'    => $negociosGanados,
        ];
    }
}
