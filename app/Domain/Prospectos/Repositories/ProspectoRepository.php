<?php

declare(strict_types=1);

namespace App\Domain\Prospectos\Repositories;

use App\Domain\Pipeline\Models\PipelineEstado;
use App\Domain\Prospectos\DTOs\ActualizarProspectoDTO;
use App\Domain\Prospectos\DTOs\CrearProspectoDTO;
use App\Domain\Prospectos\Models\Prospecto;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class ProspectoRepository implements ProspectoRepositoryInterface
{
    public function crear(CrearProspectoDTO $dto, string $codigo): Prospecto
    {
        return Prospecto::create(array_merge($dto->toArray(), ['codigo' => $codigo]));
    }

    public function actualizar(Prospecto $prospecto, ActualizarProspectoDTO $dto): Prospecto
    {
        $prospecto->update($dto->toArray());

        return $prospecto->fresh();
    }

    public function eliminar(Prospecto $prospecto): void
    {
        $prospecto->delete();
    }

    public function buscarPorId(int $id): ?Prospecto
    {
        return Prospecto::with(['estadoPipeline', 'origen', 'prioridad', 'asesor'])->find($id);
    }

    public function paginar(array $filtros = [], int $porPagina = 15): LengthAwarePaginator
    {
        $query = Prospecto::with(['estadoPipeline', 'prioridad', 'asesor'])->where('activo', true);

        if (! empty($filtros['estado_pipeline_id'])) {
            $query->where('estado_pipeline_id', $filtros['estado_pipeline_id']);
        }

        if (! empty($filtros['asesor_id'])) {
            $query->where('asesor_id', $filtros['asesor_id']);
        }

        if (! empty($filtros['prioridad_id'])) {
            $query->where('prioridad_id', $filtros['prioridad_id']);
        }

        if (! empty($filtros['origen_id'])) {
            $query->where('origen_id', $filtros['origen_id']);
        }

        if (! empty($filtros['buscar'])) {
            $t = $filtros['buscar'];
            $query->where(fn ($q) => $q
                ->where('empresa', 'like', "%{$t}%")
                ->orWhere('contacto', 'like', "%{$t}%")
                ->orWhere('email', 'like', "%{$t}%")
                ->orWhere('codigo', 'like', "%{$t}%")
            );
        }

        if (! empty($filtros['fecha_desde'])) {
            $query->whereDate('created_at', '>=', $filtros['fecha_desde']);
        }

        if (! empty($filtros['fecha_hasta'])) {
            $query->whereDate('created_at', '<=', $filtros['fecha_hasta']);
        }

        return $query->latest()->paginate($porPagina);
    }

    public function porEstado(int $estadoPipelineId): Collection
    {
        return Prospecto::where('estado_pipeline_id', $estadoPipelineId)
            ->where('activo', true)
            ->with(['prioridad', 'asesor'])
            ->orderBy('created_at')
            ->get();
    }

    public function porAsesor(int $asesorId): Collection
    {
        return Prospecto::where('asesor_id', $asesorId)
            ->where('activo', true)
            ->with('estadoPipeline')
            ->get();
    }

    public function kanbanPorTipo(string $tipo, ?int $asesorId = null): array
    {
        $estados = PipelineEstado::where('tipo', $tipo)
            ->where('activo', true)
            ->orderBy('orden')
            ->get();

        return $estados->map(function (PipelineEstado $estado) use ($asesorId) {
            $prospectos = Prospecto::where('estado_pipeline_id', $estado->id)
                ->where('activo', true)
                ->when($asesorId, fn ($q) => $q->where('asesor_id', $asesorId))
                ->with(['prioridad', 'asesor'])
                ->orderBy('created_at')
                ->get();

            return [
                'estado'     => $estado,
                'prospectos' => $prospectos,
                'total'      => $prospectos->count(),
                'valor'      => $prospectos->sum('valor_estimado'),
            ];
        })->toArray();
    }

    public function existeEmail(string $email, ?int $exceptoId = null): bool
    {
        $query = Prospecto::where('email', $email)->where('activo', true);

        if ($exceptoId !== null) {
            $query->where('id', '!=', $exceptoId);
        }

        return $query->exists();
    }

    public function marcarConvertido(Prospecto $prospecto, int $clienteId, int $usuarioId): Prospecto
    {
        $prospecto->update([
            'convertido_cliente_id' => $clienteId,
            'convertido_por'        => $usuarioId,
            'fecha_conversion'      => now(),
        ]);

        return $prospecto->fresh();
    }

    public function proximosCodigo(): string
    {
        $ultimo = Prospecto::withTrashed()->max('id') ?? 0;

        return 'PROS-' . str_pad((string) ($ultimo + 1), 5, '0', STR_PAD_LEFT);
    }
}
