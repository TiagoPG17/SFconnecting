<?php

declare(strict_types=1);

namespace App\Domain\Clientes\Services;

use App\Domain\Auditoria\Models\ActividadLog;
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

        $cliente = $this->repo->crear($dto);

        ActividadLog::registrar('crear', 'clientes', "Cliente '{$cliente->razon_social}' (NIT: {$cliente->nit}) creado");

        return $cliente;
    }

    public function actualizar(Cliente $cliente, ActualizarClienteDTO $dto): Cliente
    {
        if ($dto->email !== null && $this->repo->existeEmail($dto->email, $cliente->id)) {
            throw ClienteDuplicadoException::porEmail($dto->email);
        }

        $actualizado = $this->repo->actualizar($cliente, $dto);

        ActividadLog::registrar('actualizar', 'clientes', "Cliente '{$cliente->razon_social}' (NIT: {$cliente->nit}) actualizado");

        return $actualizado;
    }

    public function eliminar(Cliente $cliente): void
    {
        ActividadLog::registrar('eliminar', 'clientes', "Cliente '{$cliente->razon_social}' (NIT: {$cliente->nit}) eliminado");

        $this->repo->eliminar($cliente);
    }

    public function restaurar(int $id): Cliente
    {
        $cliente = $this->repo->restaurar($id);

        ActividadLog::registrar('restaurar', 'clientes', "Cliente '{$cliente->razon_social}' restaurado");

        return $cliente;
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

        $actualizado = $this->repo->actualizar($cliente, ActualizarClienteDTO::fromArray(['estado' => $estado]));

        ActividadLog::registrar('cambiar_estado', 'clientes', "Cliente '{$cliente->razon_social}' cambió estado a '{$estado}'");

        return $actualizado;
    }

    /** @return array{creados: int, actualizados: int} */
    public function sincronizarCarteraDesdeErp(string $nombreVendedor, int $userId): array
    {
        $resultado = $this->repo->sincronizarDesdeErp(
            $this->erp->todosClientesPorVendedor($nombreVendedor),
            $userId
        );

        ActividadLog::registrar(
            'sincronizar',
            'clientes',
            "Cartera sincronizada — {$resultado['creados']} creados, {$resultado['actualizados']} actualizados",
            $userId
        );

        return $resultado;
    }
}
