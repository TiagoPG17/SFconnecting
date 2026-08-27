<?php

declare(strict_types=1);

namespace App\Domain\SolicitudesCredito\Repositories;

use App\Domain\SolicitudesCredito\DTOs\CrearSolicitudCreditoDTO;
use App\Domain\SolicitudesCredito\DTOs\DecidirSolicitudCreditoDTO;
use App\Domain\SolicitudesCredito\Models\SolicitudCredito;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class SolicitudCreditoRepository implements SolicitudCreditoRepositoryInterface
{
    public function crear(
        CrearSolicitudCreditoDTO $dto,
        int $clienteId,
        int $pipelineEstadoId,
        array $dossierErp,
    ): SolicitudCredito {
        return SolicitudCredito::create(array_merge($dto->toArray(), [
            'cliente_id'         => $clienteId,
            'pipeline_estado_id' => $pipelineEstadoId,
            'dossier_erp'        => $dossierErp,
        ]));
    }

    public function decidir(
        SolicitudCredito $solicitud,
        DecidirSolicitudCreditoDTO $dto,
        int $pipelineEstadoId,
    ): SolicitudCredito {
        $solicitud->update([
            'pipeline_estado_id'   => $pipelineEstadoId,
            'revisado_por'         => $dto->revisadoPor,
            'comentario_revision'  => $dto->comentarioRevision,
            'condiciones'          => $dto->condiciones,
            'revisado_en'          => now(),
        ]);

        return $solicitud->fresh();
    }

    public function buscarPorId(int $id): ?SolicitudCredito
    {
        return SolicitudCredito::with(['negocio', 'cliente', 'pipelineEstado', 'asesor', 'revisor'])
            ->find($id);
    }

    public function paginar(array $filtros = [], int $porPagina = 15): LengthAwarePaginator
    {
        $query = SolicitudCredito::with(['negocio', 'cliente', 'pipelineEstado', 'asesor']);

        if (! empty($filtros['pipeline_estado_id'])) {
            $query->where('pipeline_estado_id', $filtros['pipeline_estado_id']);
        }

        if (! empty($filtros['asesor_id'])) {
            $query->where('asesor_id', $filtros['asesor_id']);
        }

        if (! empty($filtros['cliente_id'])) {
            $query->where('cliente_id', $filtros['cliente_id']);
        }

        if (! empty($filtros['buscar'])) {
            $t = $filtros['buscar'];
            $query->whereHas('cliente', fn ($q) => $q->where('razon_social', 'like', "%{$t}%"));
        }

        return $query->latest()->paginate($porPagina);
    }

    public function porNegocio(int $negocioId): Collection
    {
        return SolicitudCredito::where('negocio_id', $negocioId)
            ->with('pipelineEstado')
            ->get();
    }

    public function tieneSolicitudActiva(int $negocioId): bool
    {
        return SolicitudCredito::where('negocio_id', $negocioId)
            ->whereHas('pipelineEstado', fn ($q) => $q->where('es_final', false))
            ->exists();
    }

    public function tieneSolicitudActivaParaCliente(int $clienteId): bool
    {
        return SolicitudCredito::where('cliente_id', $clienteId)
            ->whereNull('negocio_id')
            ->whereHas('pipelineEstado', fn ($q) => $q->where('es_final', false))
            ->exists();
    }
}
