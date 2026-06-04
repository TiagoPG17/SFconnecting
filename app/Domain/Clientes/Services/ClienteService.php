<?php

declare(strict_types=1);

namespace App\Domain\Clientes\Services;

use App\Domain\Clientes\DTOs\ActualizarClienteDTO;
use App\Domain\Clientes\DTOs\CrearClienteDTO;
use App\Domain\Clientes\Exceptions\ClienteDuplicadoException;
use App\Domain\Clientes\Models\Cliente;
use App\Domain\Clientes\Repositories\ClienteRepositoryInterface;
use App\Domain\ERP\Contracts\ERPRepositoryInterface;
use App\Domain\Shared\Exceptions\ValidationBusinessException;
use Illuminate\Database\Eloquent\Collection;

class ClienteService
{
    private const ESTADOS_VALIDOS = ['activo', 'inactivo', 'prospecto'];

    public function __construct(
        private readonly ClienteRepositoryInterface $repo,
        private readonly ERPRepositoryInterface $erp,
    ) {}

    public function crear(CrearClienteDTO $dto): Cliente
    {
        if ($this->repo->existeNit($dto->nit)) {
            throw ClienteDuplicadoException::porNit($dto->nit);
        }

        if ($dto->email !== null && $this->repo->existeEmail($dto->email)) {
            throw ClienteDuplicadoException::porEmail($dto->email);
        }

        // Enriquecimiento silencioso desde ERP — nunca bloquea la creación
        try {
            if ($this->erp->isAvailable()) {
                $erpData = $this->erp->clientePorNit($dto->nit);
                if ($erpData !== null) {
                    $dto = $dto->enriquecerConERP($erpData);
                }
            }
        } catch (\Throwable) {
            // ERP caído: continuar sin enriquecer
        }

        return $this->repo->crear($dto);
    }

    public function actualizar(Cliente $cliente, ActualizarClienteDTO $dto): Cliente
    {
        if ($dto->email !== null && $this->repo->existeEmail($dto->email, $cliente->id)) {
            throw ClienteDuplicadoException::porEmail($dto->email);
        }

        return $this->repo->actualizar($cliente, $dto);
    }

    public function eliminar(Cliente $cliente): void
    {
        $this->repo->eliminar($cliente);
    }

    public function restaurar(int $id): Cliente
    {
        return $this->repo->restaurar($id);
    }

    public function buscarPorId(int $id): ?Cliente
    {
        return $this->repo->buscarPorId($id);
    }

    public function buscar(string $termino, int $limite = 20): Collection
    {
        return $this->repo->buscar($termino, $limite);
    }

    public function cambiarEstado(Cliente $cliente, string $estado): Cliente
    {
        if (! in_array($estado, self::ESTADOS_VALIDOS, true)) {
            throw new ValidationBusinessException(
                "Estado '{$estado}' no es válido. Use: " . implode(', ', self::ESTADOS_VALIDOS)
            );
        }

        return $this->repo->actualizar($cliente, ActualizarClienteDTO::fromArray(['estado' => $estado]));
    }
}
