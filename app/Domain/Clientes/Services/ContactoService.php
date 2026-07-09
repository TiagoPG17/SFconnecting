<?php

declare(strict_types=1);

namespace App\Domain\Clientes\Services;

use App\Domain\Auditoria\Models\ActividadLog;
use App\Domain\Clientes\DTOs\ActualizarContactoDTO;
use App\Domain\Clientes\DTOs\CrearContactoDTO;
use App\Domain\Clientes\Models\Contacto;
use App\Domain\Clientes\Repositories\ContactoRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Log;
use Throwable;

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

        $this->registrarActividad('crear', 'contactos', "Contacto '{$contacto->nombre}' creado para cliente #{$dto->clienteId}");

        return $contacto;
    }

    public function actualizar(Contacto $contacto, ActualizarContactoDTO $dto): Contacto
    {
        $actualizado = $this->repo->actualizar($contacto, $dto->toArray());

        if ($dto->principal === true) {
            $this->repo->quitarPrincipalDeCliente($contacto->cliente_id, $contacto->id);
        }

        $this->registrarActividad('actualizar', 'contactos', "Contacto '{$contacto->nombre}' actualizado (cliente #{$contacto->cliente_id})");

        return $actualizado;
    }

    public function eliminar(Contacto $contacto): void
    {
        $this->registrarActividad('eliminar', 'contactos', "Contacto '{$contacto->nombre}' eliminado (cliente #{$contacto->cliente_id})");

        $this->repo->eliminar($contacto);
    }

    public function restaurar(int $id): Contacto
    {
        $contacto = $this->repo->restaurar($id);

        $this->registrarActividad('restaurar', 'contactos', "Contacto '{$contacto->nombre}' restaurado (cliente #{$contacto->cliente_id})");

        return $contacto;
    }

    /**
     * El log de auditoría es informativo: si falla (ej. tabla actividad_log
     * no migrada en el servidor), no debe tumbar la acción real del usuario.
     */
    private function registrarActividad(string $accion, string $modulo, string $descripcion): void
    {
        try {
            ActividadLog::registrar($accion, $modulo, $descripcion);
        } catch (Throwable $e) {
            Log::error("[ActividadLog] No se pudo registrar '{$accion}' en '{$modulo}': {$e->getMessage()}", [
                'descripcion' => $descripcion,
                'exception'   => $e,
            ]);
        }
    }

    public function porCliente(int $clienteId): Collection
    {
        return $this->repo->porCliente($clienteId);
    }
}
