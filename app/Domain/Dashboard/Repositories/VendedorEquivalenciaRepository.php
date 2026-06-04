<?php

declare(strict_types=1);

namespace App\Domain\Dashboard\Repositories;

use App\Domain\Dashboard\Models\VendedorEquivalencia;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class VendedorEquivalenciaRepository implements VendedorEquivalenciaRepositoryInterface
{
    public function todos(int $compania): Collection
    {
        return VendedorEquivalencia::with('asesor')
            ->where('compania', $compania)
            ->orderBy('activo', 'desc')
            ->orderBy('nombre_vendedor')
            ->get();
    }

    public function crear(array $datos): VendedorEquivalencia
    {
        return VendedorEquivalencia::create($datos);
    }

    public function actualizar(VendedorEquivalencia $mapeo, array $datos): VendedorEquivalencia
    {
        $mapeo->update($datos);
        return $mapeo->fresh();
    }

    public function eliminar(VendedorEquivalencia $mapeo): void
    {
        $mapeo->delete();
    }

    public function buscarPorId(int $id): ?VendedorEquivalencia
    {
        return VendedorEquivalencia::with('asesor')->find($id);
    }

    public function existe(int $asesorId, int $compania, ?int $exceptoId = null): bool
    {
        return VendedorEquivalencia::where('asesor_id', $asesorId)
            ->where('compania', $compania)
            ->when($exceptoId, fn ($q) => $q->where('id', '!=', $exceptoId))
            ->exists();
    }

    public function vendedoresSiesa(int $compania): array
    {
        try {
            return DB::connection('erp_contiflex')
                ->select("
                    SELECT DISTINCT
                        LTRIM(RTRIM(ID_VENDEDOR))   AS cod,
                        LTRIM(RTRIM(NOMBRE_VENDEDOR)) AS nombre
                    FROM clientes
                    WHERE NOMBRE_VENDEDOR IS NOT NULL
                      AND LTRIM(RTRIM(NOMBRE_VENDEDOR)) <> ''
                      AND LTRIM(RTRIM(ID_VENDEDOR)) <> ''
                    ORDER BY NOMBRE_VENDEDOR
                ");
        } catch (\Throwable) {
            return [];
        }
    }
}
