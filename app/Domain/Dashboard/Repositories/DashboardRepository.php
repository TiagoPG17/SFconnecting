<?php

declare(strict_types=1);

namespace App\Domain\Dashboard\Repositories;

use App\Domain\Clientes\Models\Cliente;
use App\Domain\Negocios\Models\Negocio;
use App\Domain\Pipeline\Models\PipelineEstado;
use App\Domain\Prospectos\Models\Prospecto;
use App\Domain\Seguimientos\Models\Seguimiento;
use App\Models\User;
use Illuminate\Support\Carbon;

class DashboardRepository implements DashboardRepositoryInterface
{
    public function estadisticasClientes(?int $userId = null): array
    {
        $base = Cliente::query()->when($userId, fn ($q) => $q->where('user_id', $userId));

        return [
            'total'      => (clone $base)->count(),
            'activos'    => (clone $base)->where('estado', 'activo')->count(),
            'inactivos'  => (clone $base)->where('estado', 'inactivo')->count(),
            'prospectos' => (clone $base)->where('estado', 'prospecto')->count(),
        ];
    }

    public function totalSeguimientosUltimoDias(int $dias = 30, ?int $userId = null): int
    {
        return Seguimiento::query()
            ->when($userId, fn ($q) => $q->where('user_id', $userId))
            ->where('fecha_seguimiento', '>=', Carbon::now()->subDays($dias))
            ->count();
    }

    public function totalProximosSeguimientos(int $dias = 7, ?int $userId = null): int
    {
        return Seguimiento::query()
            ->when($userId, fn ($q) => $q->where('user_id', $userId))
            ->whereBetween('proxima_fecha', [Carbon::now(), Carbon::now()->addDays($dias)])
            ->count();
    }

    public function totalClientesSinSeguimientoReciente(int $dias = 30, ?int $userId = null): int
    {
        $desde = Carbon::now()->subDays($dias);

        return Cliente::query()
            ->when($userId, fn ($q) => $q->where('user_id', $userId))
            ->whereDoesntHave('seguimientos', fn ($q) => $q->where('fecha_seguimiento', '>=', $desde))
            ->count();
    }

    public function estadisticasPipeline(?int $userId = null): array
    {
        $baseProspectos = Prospecto::query()
            ->where('activo', true)
            ->when($userId, fn ($q) => $q->where('asesor_id', $userId));

        $baseNegocios = Negocio::query()
            ->where('activo', true)
            ->when($userId, fn ($q) => $q->where('asesor_id', $userId));

        return [
            'prospectos' => [
                'total'      => (clone $baseProspectos)->count(),
                'convertidos' => (clone $baseProspectos)->whereNotNull('convertido_cliente_id')->count(),
            ],
            'negocios' => [
                'total'   => (clone $baseNegocios)->count(),
                'abiertos' => (clone $baseNegocios)
                    ->whereHas('pipelineEstado', fn ($q) => $q->where('es_final', false))
                    ->count(),
                'ganados' => (clone $baseNegocios)
                    ->whereHas('pipelineEstado', fn ($q) => $q->where('es_ganado', true))
                    ->count(),
                'perdidos' => (clone $baseNegocios)
                    ->whereHas('pipelineEstado', fn ($q) => $q->where('es_perdido', true))
                    ->count(),
            ],
        ];
    }

    public function forecastResumen(?int $userId = null): array
    {
        $negocios = Negocio::query()
            ->where('activo', true)
            ->when($userId, fn ($q) => $q->where('asesor_id', $userId))
            ->whereHas('pipelineEstado', fn ($q) => $q->where('es_final', false))
            ->with('pipelineEstado')
            ->get();

        $totalPipeline = $negocios->sum('valor_estimado');
        $totalForecast = $negocios->sum(fn ($n) => $n->valorForecast());

        return [
            'total_pipeline'  => round((float) $totalPipeline, 2),
            'total_forecast'  => round($totalForecast, 2),
            'cantidad_abiertos' => $negocios->count(),
        ];
    }

    public function negociosProximosCierre(int $dias = 30, ?int $userId = null): array
    {
        return Negocio::query()
            ->where('activo', true)
            ->when($userId, fn ($q) => $q->where('asesor_id', $userId))
            ->whereHas('pipelineEstado', fn ($q) => $q->where('es_final', false))
            ->whereBetween('fecha_estimada_cierre', [Carbon::today(), Carbon::today()->addDays($dias)])
            ->with(['pipelineEstado', 'asesor', 'cliente'])
            ->orderBy('fecha_estimada_cierre')
            ->limit(10)
            ->get()
            ->map(fn ($n) => [
                'id'                    => $n->id,
                'nombre_negocio'        => $n->nombre_negocio,
                'valor_estimado'        => $n->valor_estimado,
                'probabilidad_efectiva' => $n->probabilidadEfectiva(),
                'fecha_estimada_cierre' => $n->fecha_estimada_cierre?->format('Y-m-d'),
                'estado'                => $n->pipelineEstado?->nombre,
                'asesor'                => $n->asesor?->name,
                'cliente'               => $n->cliente?->razon_social ?? $n->prospecto?->empresa,
            ])
            ->toArray();
    }

    public function topAsesores(int $limite = 5): array
    {
        return User::query()
            ->whereHas('roles', fn ($q) => $q->where('name', 'comercial'))
            ->withCount([
                'negocios as negocios_ganados' => fn ($q) => $q
                    ->whereHas('pipelineEstado', fn ($q2) => $q2->where('es_ganado', true)),
            ])
            ->withSum(
                ['negocios as valor_ganado' => fn ($q) => $q
                    ->whereHas('pipelineEstado', fn ($q2) => $q2->where('es_ganado', true))],
                'valor_estimado'
            )
            ->orderByDesc('negocios_ganados')
            ->limit($limite)
            ->get()
            ->map(fn ($u) => [
                'id'              => $u->id,
                'nombre'          => $u->name,
                'negocios_ganados' => $u->negocios_ganados,
                'valor_ganado'    => round((float) $u->valor_ganado, 2),
            ])
            ->toArray();
    }

    public function tasaConversion(?int $userId = null): array
    {
        $base = Prospecto::query()
            ->when($userId, fn ($q) => $q->where('asesor_id', $userId));

        $total      = (clone $base)->count();
        $convertidos = (clone $base)->whereNotNull('convertido_cliente_id')->count();

        $negociosGanados = Negocio::query()
            ->where('activo', true)
            ->when($userId, fn ($q) => $q->where('asesor_id', $userId))
            ->whereHas('pipelineEstado', fn ($q) => $q->where('es_ganado', true))
            ->count();

        return [
            'total_prospectos' => $total,
            'convertidos'      => $convertidos,
            'negocios_ganados' => $negociosGanados,
            'tasa'             => $total > 0 ? round(($convertidos / $total) * 100, 1) : 0,
        ];
    }

    public function agingNegocios(?int $userId = null): array
    {
        $negocios = Negocio::query()
            ->where('activo', true)
            ->when($userId, fn ($q) => $q->where('asesor_id', $userId))
            ->whereHas('pipelineEstado', fn ($q) => $q->where('es_final', false))
            ->with([
                'pipelineEstado',
                'auditoria' => fn ($q) => $q
                    ->where('evento', 'cambio_estado')
                    ->orderByDesc('created_at'),
            ])
            ->get();

        return $negocios
            ->groupBy('pipeline_estado_id')
            ->map(function ($grupo) {
                $estado = $grupo->first()->pipelineEstado;

                $diasPorNegocio = $grupo->map(function (Negocio $n) {
                    $ultimoCambio = $n->auditoria->first();
                    $desde        = $ultimoCambio ? $ultimoCambio->created_at : $n->created_at;
                    return (int) $desde->diffInDays(now());
                });

                return [
                    'estado'   => $estado?->nombre,
                    'color'    => $estado?->color ?? '#94a3b8',
                    'cantidad' => $grupo->count(),
                    'avg_dias' => (int) round($diasPorNegocio->avg()),
                    'max_dias' => (int) $diasPorNegocio->max(),
                ];
            })
            ->sortByDesc('avg_dias')
            ->values()
            ->toArray();
    }
}
