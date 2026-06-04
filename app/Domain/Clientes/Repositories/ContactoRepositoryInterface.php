<?php

declare(strict_types=1);

namespace App\Domain\Clientes\Repositories;

use App\Domain\Clientes\Models\Contacto;
use Illuminate\Database\Eloquent\Collection;

interface ContactoRepositoryInterface
{
    public function crear(array $data): Contacto;

    public function actualizar(Contacto $contacto, array $data): Contacto;

    public function eliminar(Contacto $contacto): void;

    public function restaurar(int $id): Contacto;

    public function buscarPorId(int $id): ?Contacto;

    public function porCliente(int $clienteId): Collection;

    public function quitarPrincipalDeCliente(int $clienteId, ?int $exceptoId = null): void;
}
