<?php

declare(strict_types=1);

namespace App\Domain\Dashboard\Repositories;

use App\Domain\Dashboard\Models\VendedorEquivalencia;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

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
        $mapeo->fill($datos);

        Log::info('[MapeoVendedor][Repo] antes de save()', [
            'mapeo_id'    => $mapeo->id,
            'datos_input' => $datos,
            'dirty'       => $mapeo->getDirty(),
            'original'    => $mapeo->getOriginal(),
        ]);

        $result = $mapeo->save();

        Log::info('[MapeoVendedor][Repo] resultado save()', [
            'mapeo_id' => $mapeo->id,
            'result'   => $result,
        ]);

        return $mapeo->refresh();
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
        $ls = '[siesa-m4-sqlsw-db8.ceqnrhbwqaoo.us-east-1.rds.amazonaws.com].[unoee_formacol_real].[dbo]';

        try {
            return DB::connection('erp_contiflex')
                ->select("
                    SELECT
                        LTRIM(RTRIM(v.f210_id))             AS cod,
                        LTRIM(RTRIM(t.f200_razon_social))   AS nombre
                    FROM {$ls}.[t210_mm_vendedores] AS v
                    LEFT JOIN {$ls}.[t200_mm_terceros] AS t
                           ON t.f200_rowid = v.f210_rowid_tercero
                    WHERE v.f210_id_cia        = ?
                      AND v.f210_ind_vendedor  = 1
                      AND t.f200_razon_social IS NOT NULL
                      AND LTRIM(RTRIM(t.f200_razon_social)) <> ''
                    ORDER BY t.f200_razon_social
                ", [$compania]);
        } catch (\Throwable) {
            return [];
        }
    }
}
