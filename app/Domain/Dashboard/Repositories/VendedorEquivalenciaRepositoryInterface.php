<?php

declare(strict_types=1);

namespace App\Domain\Dashboard\Repositories;

use App\Domain\Dashboard\Models\VendedorEquivalencia;
use Illuminate\Database\Eloquent\Collection;

interface VendedorEquivalenciaRepositoryInterface
{
    public function todos(int $compania): Collection;

    public function crear(array $datos): VendedorEquivalencia;

    public function actualizar(VendedorEquivalencia $mapeo, array $datos): VendedorEquivalencia;

    public function eliminar(VendedorEquivalencia $mapeo): void;

    public function buscarPorId(int $id): ?VendedorEquivalencia;

    public function existe(int $asesorId, int $compania, ?int $exceptoId = null): bool;

    public function vendedoresSiesa(int $compania): array;
}
