<?php

declare(strict_types=1);

namespace App\Domain\Clientes\Services;

use App\Domain\Clientes\DTOs\ActualizarContactoDTO;
use App\Domain\Clientes\DTOs\CrearContactoDTO;
use App\Domain\Clientes\Models\Contacto;
use App\Domain\Clientes\Repositories\ContactoRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

class ContactoService
{
    public function __construct(
        private readonly ContactoRepositoryInterface $repo,
    ) {}

    public function crear(CrearContactoDTO $dto): Contacto
    {
        $contacto = $this->repo->crear($dto->toArray());

        if ($dto->principal) {
            $this->repo->quitarPrincipalDeCliente($dto->clienteId, $contacto->id);
        }

        return $contacto;
    }

    public function actualizar(Contacto $contacto, ActualizarContactoDTO $dto): Contacto
    {
        $actualizado = $this->repo->actualizar($contacto, $dto->toArray());

        if ($dto->principal === true) {
            $this->repo->quitarPrincipalDeCliente($contacto->cliente_id, $contacto->id);
        }

        return $actualizado;
    }

    public function eliminar(Contacto $contacto): void
    {
        $this->repo->eliminar($contacto);
    }

    public function restaurar(int $id): Contacto
    {
        return $this->repo->restaurar($id);
    }

    public function porCliente(int $clienteId): Collection
    {
        return $this->repo->porCliente($clienteId);
    }
}
