<?php

declare(strict_types=1);

namespace App\Domain\Seguimientos\Repositories;

use App\Domain\Seguimientos\DTOs\CrearSeguimientoDTO;
use App\Domain\Seguimientos\Models\Seguimiento;
use Carbon\Carbon;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class SeguimientoRepository implements SeguimientoRepositoryInterface
{
    public function crear(CrearSeguimientoDTO $dto): Seguimiento
    {
        return Seguimiento::create($dto->toArray());
    }

    public function actualizar(Seguimiento $seguimiento, array $data): Seguimiento
    {
        $seguimiento->update($data);

        return $seguimiento->fresh();
    }

    public function buscarPorId(int $id): ?Seguimiento
    {
        return Seguimiento::find($id);
    }

    public function timelineCliente(int $clienteId, int $limite = 20): Collection
    {
        return Seguimiento::where('cliente_id', $clienteId)
            ->with(['asesor', 'contacto'])
            ->orderByDesc('fecha_seguimiento')
            ->limit($limite)
            ->get();
    }

    public function proximosPorAsesor(int $userId, int $dias = 7): Collection
    {
        return Seguimiento::where('user_id', $userId)
            ->whereNotNull('proxima_fecha')
            ->whereBetween('proxima_fecha', [Carbon::now(), Carbon::now()->addDays($dias)])
            ->orderBy('proxima_fecha')
            ->get();
    }

    public function paginarPorCliente(int $clienteId, int $porPagina = 15): LengthAwarePaginator
    {
        return Seguimiento::where('cliente_id', $clienteId)
            ->with(['asesor', 'contacto'])
            ->orderByDesc('fecha_seguimiento')
            ->paginate($porPagina);
    }

    public function paginar(array $filtros = [], int $porPagina = 15): LengthAwarePaginator
    {
        $query = Seguimiento::with(['cliente', 'asesor', 'contacto']);

        if (! empty($filtros['cliente_id'])) {
            $query->where('cliente_id', $filtros['cliente_id']);
        }

        if (! empty($filtros['user_id'])) {
            $query->where('user_id', $filtros['user_id']);
        }

        if (! empty($filtros['tipo'])) {
            $query->where('tipo', $filtros['tipo']);
        }

        if (! empty($filtros['resultado'])) {
            $query->where('resultado', $filtros['resultado']);
        }

        if (! empty($filtros['fecha_desde'])) {
            $query->whereDate('fecha_seguimiento', '>=', $filtros['fecha_desde']);
        }

        if (! empty($filtros['fecha_hasta'])) {
            $query->whereDate('fecha_seguimiento', '<=', $filtros['fecha_hasta']);
        }

        return $query->orderByDesc('fecha_seguimiento')->paginate($porPagina);
    }

    public function porProspecto(int $prospectoId, int $limite = 20): Collection
    {
        return Seguimiento::where('prospecto_id', $prospectoId)
            ->with(['asesor', 'contacto'])
            ->orderByDesc('fecha_seguimiento')
            ->limit($limite)
            ->get();
    }

    public function migrarACliente(int $prospectoId, int $clienteId): int
    {
        return Seguimiento::where('prospecto_id', $prospectoId)
            ->whereNull('cliente_id')
            ->update(['cliente_id' => $clienteId]);
    }

    public function eliminar(Seguimiento $seguimiento): void
    {
        $seguimiento->delete();
    }
}
