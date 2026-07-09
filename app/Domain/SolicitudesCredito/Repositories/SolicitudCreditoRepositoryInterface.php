<?php

declare(strict_types=1);

namespace App\Domain\SolicitudesCredito\Repositories;

use App\Domain\SolicitudesCredito\DTOs\CrearSolicitudCreditoDTO;
use App\Domain\SolicitudesCredito\DTOs\DecidirSolicitudCreditoDTO;
use App\Domain\SolicitudesCredito\Models\SolicitudCredito;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

interface SolicitudCreditoRepositoryInterface
{
    public function crear(
        CrearSolicitudCreditoDTO $dto,
        int $clienteId,
        int $pipelineEstadoId,
        array $dossierErp,
    ): SolicitudCredito;

    public function decidir(
        SolicitudCredito $solicitud,
        DecidirSolicitudCreditoDTO $dto,
        int $pipelineEstadoId,
    ): SolicitudCredito;

    public function buscarPorId(int $id): ?SolicitudCredito;

    public function paginar(array $filtros = [], int $porPagina = 15): LengthAwarePaginator;

    public function porNegocio(int $negocioId): Collection;

    public function tieneSolicitudActiva(int $negocioId): bool;
}
