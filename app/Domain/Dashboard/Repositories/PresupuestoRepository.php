<?php

declare(strict_types=1);

namespace App\Domain\Dashboard\Repositories;

use App\Domain\Dashboard\Models\Presupuesto;
use Illuminate\Database\Eloquent\Collection;

class PresupuestoRepository implements PresupuestoRepositoryInterface
{
    public function todos(int $anio): Collection
    {
        return Presupuesto::with('asesor')
            ->where('anio', $anio)
            ->whereHas('asesor', fn ($q) => $q->role('comercial'))
            ->orderBy('presupuesto', 'desc')
            ->get();
    }

    public function crear(array $datos): Presupuesto
    {
        return Presupuesto::create($datos);
    }

    public function actualizar(Presupuesto $presupuesto, array $datos): Presupuesto
    {
        $presupuesto->update($datos);
        return $presupuesto->fresh();
    }

    public function eliminar(Presupuesto $presupuesto): void
    {
        $presupuesto->delete();
    }

    public function buscarPorId(int $id): ?Presupuesto
    {
        return Presupuesto::with('asesor')->find($id);
    }

    public function existe(int $asesorId, int $compania, int $anio, ?int $exceptoId = null): bool
    {
        return Presupuesto::where('asesor_id', $asesorId)
            ->where('compania', $compania)
            ->where('anio', $anio)
            ->when($exceptoId, fn ($q) => $q->where('id', '!=', $exceptoId))
            ->exists();
    }
}
