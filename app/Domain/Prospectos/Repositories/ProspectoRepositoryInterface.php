<?php

declare(strict_types=1);

namespace App\Domain\Prospectos\Repositories;

use App\Domain\Prospectos\DTOs\ActualizarProspectoDTO;
use App\Domain\Prospectos\DTOs\CrearProspectoDTO;
use App\Domain\Prospectos\Models\Prospecto;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

interface ProspectoRepositoryInterface
{
    public function crear(CrearProspectoDTO $dto, string $codigo): Prospecto;

    public function actualizar(Prospecto $prospecto, ActualizarProspectoDTO $dto): Prospecto;

    public function eliminar(Prospecto $prospecto): void;

    public function buscarPorId(int $id): ?Prospecto;

    public function paginar(array $filtros = [], int $porPagina = 15): LengthAwarePaginator;

    public function porEstado(int $estadoPipelineId): Collection;

    public function porAsesor(int $asesorId): Collection;

    public function kanbanPorTipo(string $tipo): array;

    public function existeEmail(string $email, ?int $exceptoId = null): bool;

    public function marcarConvertido(Prospecto $prospecto, int $clienteId, int $usuarioId): Prospecto;

    public function proximosCodigo(): string;
}
