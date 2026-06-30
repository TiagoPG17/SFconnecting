<?php

declare(strict_types=1);

namespace App\Domain\Dashboard\Repositories;

use App\Domain\Dashboard\Models\Presupuesto;
use App\Domain\Dashboard\Models\PresupuestoMensual;
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

    public function upsert(int $asesorId, int $compania, int $anio, float $presupuesto): Presupuesto
    {
        return Presupuesto::updateOrCreate(
            ['asesor_id' => $asesorId, 'compania' => $compania, 'anio' => $anio],
            ['presupuesto' => $presupuesto]
        );
    }

    public function guardarMeses(int $presupuestoId, array $meses): void
    {
        foreach ($meses as $mes => $valor) {
            PresupuestoMensual::updateOrCreate(
                ['presupuesto_id' => $presupuestoId, 'mes' => (int) $mes],
                ['valor' => (float) $valor]
            );
        }
    }

    public function getMeses(int $presupuestoId): Collection
    {
        return PresupuestoMensual::where('presupuesto_id', $presupuestoId)->orderBy('mes')->get();
    }
}
