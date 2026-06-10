<?php

declare(strict_types=1);

namespace App\Domain\Clientes\Repositories;

use App\Domain\Clientes\DTOs\ActualizarClienteDTO;
use App\Domain\Clientes\DTOs\CrearClienteDTO;
use App\Domain\Clientes\Models\Cliente;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class ClienteRepository implements ClienteRepositoryInterface
{
    public function crear(CrearClienteDTO $dto): Cliente
    {
        return Cliente::create($dto->toArray());
    }

    public function actualizar(Cliente $cliente, ActualizarClienteDTO $dto): Cliente
    {
        $cliente->update($dto->toArray());

        return $cliente->fresh();
    }

    public function eliminar(Cliente $cliente): void
    {
        $cliente->delete();
    }

    public function restaurar(int $id): Cliente
    {
        /** @var Cliente $cliente */
        $cliente = Cliente::withTrashed()->findOrFail($id);
        $cliente->restore();

        return $cliente;
    }

    public function buscarPorId(int $id): ?Cliente
    {
        return Cliente::find($id);
    }

    public function buscarPorNit(string $nit): ?Cliente
    {
        return Cliente::where('nit', $nit)->first();
    }

    public function buscarPorEmail(string $email): ?Cliente
    {
        return Cliente::where('email', $email)->first();
    }

    public function paginar(array $filtros = [], int $porPagina = 15): LengthAwarePaginator
    {
        $query = Cliente::query()->with('asesor');

        if (! empty($filtros['estado'])) {
            $query->where('estado', $filtros['estado']);
        }

        if (! empty($filtros['ciudad'])) {
            $query->where('ciudad', $filtros['ciudad']);
        }

        if (! empty($filtros['user_id'])) {
            $query->where('user_id', $filtros['user_id']);
        }

        if (! empty($filtros['buscar'])) {
            $termino = $filtros['buscar'];
            $query->where(function ($q) use ($termino) {
                $q->where('razon_social', 'like', "%{$termino}%")
                    ->orWhere('nit', 'like', "%{$termino}%")
                    ->orWhere('email', 'like', "%{$termino}%");
            });
        }

        return $query->latest()->paginate($porPagina);
    }

    public function buscar(string $termino, int $limite = 20): Collection
    {
        return Cliente::where('razon_social', 'like', "%{$termino}%")
            ->orWhere('nit', 'like', "%{$termino}%")
            ->orWhere('email', 'like', "%{$termino}%")
            ->limit($limite)
            ->get();
    }

    public function porAsesor(int $userId): Collection
    {
        return Cliente::where('user_id', $userId)->get();
    }

    public function existeNit(string $nit, ?int $exceptoId = null): bool
    {
        $query = Cliente::where('nit', $nit);

        if ($exceptoId !== null) {
            $query->where('id', '!=', $exceptoId);
        }

        return $query->exists();
    }

    public function existeEmail(string $email, ?int $exceptoId = null): bool
    {
        $query = Cliente::where('email', $email);

        if ($exceptoId !== null) {
            $query->where('id', '!=', $exceptoId);
        }

        return $query->exists();
    }

    public function sincronizarDesdeErp(array $clientesErp, int $userId): array
    {
        $creados     = 0;
        $actualizados = 0;

        $nits = array_filter(array_column($clientesErp, 'NIT'), fn ($n) => $n !== null && $n !== '');

        $existentes = Cliente::whereIn('nit', $nits)
            ->pluck('nit')
            ->flip()
            ->toArray();

        foreach ($clientesErp as $datos) {
            $nit = trim((string) ($datos['NIT'] ?? ''));

            if ($nit === '') {
                continue;
            }

            if (isset($existentes[$nit])) {
                Cliente::where('nit', $nit)->update([
                    'razon_social' => $datos['RAZON_SOCIAL'] ?? '',
                    'ciudad'       => $datos['CIUDAD'] ?? null,
                ]);
                $actualizados++;
            } else {
                Cliente::create([
                    'nit'          => $nit,
                    'razon_social' => $datos['RAZON_SOCIAL'] ?? $nit,
                    'ciudad'       => $datos['CIUDAD'] ?? null,
                    'user_id'      => $userId,
                    'estado'       => 'activo',
                ]);
                $existentes[$nit] = true;
                $creados++;
            }
        }

        return compact('creados', 'actualizados');
    }
}
