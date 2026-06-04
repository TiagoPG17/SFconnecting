<?php

declare(strict_types=1);

namespace App\Domain\Seguimientos\Services;

use App\Domain\Clientes\Repositories\ClienteRepositoryInterface;
use App\Domain\Seguimientos\DTOs\CrearSeguimientoDTO;
use App\Domain\Seguimientos\Exceptions\SeguimientoException;
use App\Domain\Seguimientos\Models\Seguimiento;
use App\Domain\Seguimientos\Repositories\SeguimientoRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

class SeguimientoService
{
    public function __construct(
        private readonly SeguimientoRepositoryInterface $repo,
        private readonly ClienteRepositoryInterface $clienteRepo,
    ) {}

    public function crear(CrearSeguimientoDTO $dto): Seguimiento
    {
        if ($dto->clienteId !== null) {
            $cliente = $this->clienteRepo->buscarPorId($dto->clienteId);

            if ($cliente === null) {
                throw SeguimientoException::clienteInexistente($dto->clienteId);
            }
        }

        if ($dto->proximaFecha !== null && $dto->proximaFecha->isPast()) {
            throw SeguimientoException::fechaInvalida($dto->proximaFecha->toDateTimeString());
        }

        return $this->repo->crear($dto);
    }

    public function actualizar(Seguimiento $seguimiento, array $data): Seguimiento
    {
        if (isset($data['proxima_fecha']) && $data['proxima_fecha'] !== null) {
            $fecha = \Carbon\Carbon::parse($data['proxima_fecha']);
            if ($fecha->isPast()) {
                throw SeguimientoException::fechaInvalida($fecha->toDateTimeString());
            }
        }

        return $this->repo->actualizar($seguimiento, $data);
    }

    public function eliminar(Seguimiento $seguimiento): void
    {
        $this->repo->eliminar($seguimiento);
    }

    public function timelineCliente(int $clienteId, int $limite = 20): Collection
    {
        return $this->repo->timelineCliente($clienteId, $limite);
    }

    public function proximosPorAsesor(int $userId, int $dias = 7): Collection
    {
        return $this->repo->proximosPorAsesor($userId, $dias);
    }
}
