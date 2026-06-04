<?php

declare(strict_types=1);

namespace App\Domain\Seguimientos\Repositories;

use App\Domain\Seguimientos\DTOs\CrearSeguimientoDTO;
use App\Domain\Seguimientos\Models\Seguimiento;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

interface SeguimientoRepositoryInterface
{
    public function crear(CrearSeguimientoDTO $dto): Seguimiento;

    public function actualizar(Seguimiento $seguimiento, array $data): Seguimiento;

    public function buscarPorId(int $id): ?Seguimiento;

    public function timelineCliente(int $clienteId, int $limite = 20): Collection;

    public function proximosPorAsesor(int $userId, int $dias = 7): Collection;

    public function paginarPorCliente(int $clienteId, int $porPagina = 15): LengthAwarePaginator;

    public function paginar(array $filtros = [], int $porPagina = 15): LengthAwarePaginator;

    public function porProspecto(int $prospectoId, int $limite = 20): Collection;

    public function migrarACliente(int $prospectoId, int $clienteId): int;

    public function eliminar(Seguimiento $seguimiento): void;
}
