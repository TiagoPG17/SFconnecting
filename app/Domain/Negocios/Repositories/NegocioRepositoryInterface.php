<?php

declare(strict_types=1);

namespace App\Domain\Negocios\Repositories;

use App\Domain\Negocios\DTOs\ActualizarNegocioDTO;
use App\Domain\Negocios\DTOs\CrearNegocioDTO;
use App\Domain\Negocios\Models\Negocio;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

interface NegocioRepositoryInterface
{
    public function crear(CrearNegocioDTO $dto): Negocio;

    public function actualizar(Negocio $negocio, ActualizarNegocioDTO $dto): Negocio;

    public function eliminar(Negocio $negocio): void;

    public function buscarPorId(int $id): ?Negocio;

    public function paginar(array $filtros = [], int $porPagina = 15): LengthAwarePaginator;

    public function porAsesor(int $asesorId): Collection;

    public function kanban(?int $asesorId = null): array;

    public function forecast(array $filtros = []): array;

    public function porProspecto(int $prospectoId): Collection;

    /** Reasigna a otro asesor los negocios de esos clientes que hoy son de $deUserId. */
    public function reasignarPorClientes(array $clienteIds, int $deUserId, int $aUserId): Collection;

    /**
     * Al convertir un Prospecto en Cliente, vincula también sus negocios existentes
     * al cliente nuevo (sin quitarles el prospecto_id, para no perder de dónde vinieron).
     * Devuelve cuántos se actualizaron.
     */
    public function vincularClientePorProspecto(int $prospectoId, int $clienteId): int;
}
