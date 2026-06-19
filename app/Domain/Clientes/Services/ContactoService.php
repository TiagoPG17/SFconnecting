<?php

declare(strict_types=1);

namespace App\Domain\Clientes\Services;

use App\Domain\Auditoria\Models\ActividadLog;
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

        ActividadLog::registrar('crear', 'contactos', "Contacto '{$contacto->nombre}' creado para cliente #{$dto->clienteId}");

        return $contacto;
    }

    public function actualizar(Contacto $contacto, ActualizarContactoDTO $dto): Contacto
    {
        $actualizado = $this->repo->actualizar($contacto, $dto->toArray());

        if ($dto->principal === true) {
            $this->repo->quitarPrincipalDeCliente($contacto->cliente_id, $contacto->id);
        }

        ActividadLog::registrar('actualizar', 'contactos', "Contacto '{$contacto->nombre}' actualizado (cliente #{$contacto->cliente_id})");

        return $actualizado;
    }

    public function eliminar(Contacto $contacto): void
    {
        ActividadLog::registrar('eliminar', 'contactos', "Contacto '{$contacto->nombre}' eliminado (cliente #{$contacto->cliente_id})");

        $this->repo->eliminar($contacto);
    }

    public function restaurar(int $id): Contacto
    {
        $contacto = $this->repo->restaurar($id);

        ActividadLog::registrar('restaurar', 'contactos', "Contacto '{$contacto->nombre}' restaurado (cliente #{$contacto->cliente_id})");

        return $contacto;
    }

    public function porCliente(int $clienteId): Collection
    {
        return $this->repo->porCliente($clienteId);
    }
}
