<?php

declare(strict_types=1);

namespace App\Domain\Clientes\Repositories;

use App\Domain\Clientes\DTOs\ActualizarClienteDTO;
use App\Domain\Clientes\DTOs\CrearClienteDTO;
use App\Domain\Clientes\Models\Cliente;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

interface ClienteRepositoryInterface
{
    public function crear(CrearClienteDTO $dto): Cliente;

    public function actualizar(Cliente $cliente, ActualizarClienteDTO $dto): Cliente;

    public function eliminar(Cliente $cliente): void;

    public function restaurar(int $id): Cliente;

    public function buscarPorId(int $id): ?Cliente;

    public function buscarPorNit(string $nit): ?Cliente;

    public function buscarPorEmail(string $email): ?Cliente;

    public function paginar(array $filtros = [], int $porPagina = 15): LengthAwarePaginator;

    public function buscar(string $termino, int $limite = 20): Collection;

    public function porAsesor(int $userId): Collection;

    public function existeNit(string $nit, ?int $exceptoId = null): bool;

    public function existeEmail(string $email, ?int $exceptoId = null): bool;
}
