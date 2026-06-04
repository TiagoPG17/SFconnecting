<?php

declare(strict_types=1);

namespace App\Domain\Dashboard\Repositories;

use App\Domain\Dashboard\Models\Presupuesto;
use Illuminate\Database\Eloquent\Collection;

interface PresupuestoRepositoryInterface
{
    public function todos(int $anio): Collection;

    public function crear(array $datos): Presupuesto;

    public function actualizar(Presupuesto $presupuesto, array $datos): Presupuesto;

    public function eliminar(Presupuesto $presupuesto): void;

    public function buscarPorId(int $id): ?Presupuesto;

    public function existe(int $asesorId, int $compania, int $anio, ?int $exceptoId = null): bool;
}
