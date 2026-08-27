<?php

declare(strict_types=1);

namespace App\Domain\Negocios\Repositories;

use App\Domain\Negocios\DTOs\ActualizarNegocioDTO;
use App\Domain\Negocios\DTOs\CrearNegocioDTO;
use App\Domain\Negocios\Models\Negocio;
use App\Domain\Pipeline\Models\PipelineEstado;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class NegocioRepository implements NegocioRepositoryInterface
{
    public function crear(CrearNegocioDTO $dto): Negocio
    {
        return Negocio::create($dto->toArray());
    }

    public function actualizar(Negocio $negocio, ActualizarNegocioDTO $dto): Negocio
    {
        $negocio->update($dto->toArray());

        return $negocio->fresh();
    }

    public function eliminar(Negocio $negocio): void
    {
        $negocio->delete();
    }

    public function buscarPorId(int $id): ?Negocio
    {
        return Negocio::with(['pipelineEstado', 'tipoNegocio', 'sector', 'motivoPerdida', 'asesor', 'prospecto', 'cliente'])
            ->find($id);
    }

    public function paginar(array $filtros = [], int $porPagina = 15): LengthAwarePaginator
    {
        $query = Negocio::with(['pipelineEstado', 'asesor', 'cliente', 'prospecto'])->where('activo', true);

        if (! empty($filtros['pipeline_estado_id'])) {
            $query->where('pipeline_estado_id', $filtros['pipeline_estado_id']);
        }

        if (! empty($filtros['asesor_id'])) {
            $query->where('asesor_id', $filtros['asesor_id']);
        }

        if (! empty($filtros['tipo_negocio_id'])) {
            $query->where('tipo_negocio_id', $filtros['tipo_negocio_id']);
        }

        if (! empty($filtros['buscar'])) {
            $t = $filtros['buscar'];
            $query->where('nombre_negocio', 'like', "%{$t}%");
        }

        if (! empty($filtros['fecha_desde'])) {
            $query->whereDate('fecha_estimada_cierre', '>=', $filtros['fecha_desde']);
        }

        if (! empty($filtros['fecha_hasta'])) {
            $query->whereDate('fecha_estimada_cierre', '<=', $filtros['fecha_hasta']);
        }

        return $query->latest()->paginate($porPagina);
    }

    public function porAsesor(int $asesorId): Collection
    {
        return Negocio::where('asesor_id', $asesorId)
            ->where('activo', true)
            ->with('pipelineEstado')
            ->get();
    }

    public function kanban(?int $asesorId = null): array
    {
        $estados = PipelineEstado::where('tipo', 'negocio')
            ->where('activo', true)
            ->orderBy('orden')
            ->get();

        return $estados->map(function (PipelineEstado $estado) use ($asesorId) {
            $negocios = Negocio::where('pipeline_estado_id', $estado->id)
                ->where('activo', true)
                ->when($asesorId, fn ($q) => $q->where('asesor_id', $asesorId))
                ->with(['asesor', 'cliente', 'prospecto'])
                ->orderBy('fecha_estimada_cierre')
                ->get();

            return [
                'estado'        => $estado,
                'negocios'      => $negocios,
                'total'         => $negocios->count(),
                'valor_total'   => $negocios->sum('valor_estimado'),
                'valor_forecast' => $negocios->sum(fn ($n) => $n->valorForecast()),
            ];
        })->toArray();
    }

    public function forecast(array $filtros = []): array
    {
        $query = Negocio::where('activo', true)
            ->whereHas('pipelineEstado', fn ($q) => $q->where('es_final', false));

        if (! empty($filtros['asesor_id'])) {
            $query->where('asesor_id', $filtros['asesor_id']);
        }

        if (! empty($filtros['mes'])) {
            $query->whereMonth('fecha_estimada_cierre', $filtros['mes']);
        }

        if (! empty($filtros['anio'])) {
            $query->whereYear('fecha_estimada_cierre', $filtros['anio']);
        }

        $negocios = $query->with(['pipelineEstado', 'asesor'])->get();

        $porEstado = PipelineEstado::where('tipo', 'negocio')
            ->where('activo', true)
            ->where('es_final', false)
            ->orderBy('orden')
            ->get()
            ->map(function (PipelineEstado $estado) use ($negocios) {
                $grupo = $negocios->where('pipeline_estado_id', $estado->id);

                return (object) [
                    'nombre'        => $estado->nombre,
                    'color'         => $estado->color,
                    'cantidad'      => $grupo->count(),
                    'valor'         => $grupo->sum('valor_estimado'),
                    'valor_forecast' => $grupo->sum(fn ($n) => $n->valorForecast()),
                ];
            })
            ->filter(fn ($e) => $e->cantidad > 0)
            ->values();

        $porAsesor = $negocios
            ->groupBy(fn ($n) => $n->asesor_id)
            ->map(function ($grupo) {
                return (object) [
                    'asesor'         => $grupo->first()->asesor?->name ?? 'Sin asignar',
                    'cantidad'       => $grupo->count(),
                    'valor_pipeline' => $grupo->sum('valor_estimado'),
                    'valor_forecast' => $grupo->sum(fn ($n) => $n->valorForecast()),
                ];
            })
            ->sortByDesc('valor_forecast')
            ->values();

        return [
            'total_pipeline'  => $negocios->sum('valor_estimado'),
            'total_forecast'  => $negocios->sum(fn ($n) => $n->valorForecast()),
            'cantidad'        => $negocios->count(),
            'por_estado'      => $porEstado,
            'por_asesor'      => $porAsesor,
        ];
    }

    public function porProspecto(int $prospectoId): Collection
    {
        return Negocio::where('prospecto_id', $prospectoId)
            ->with('pipelineEstado')
            ->get();
    }
}
