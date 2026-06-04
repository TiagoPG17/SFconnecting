<?php

declare(strict_types=1);

namespace App\Domain\Clientes\Repositories;

use App\Domain\Clientes\Models\Contacto;
use Illuminate\Database\Eloquent\Collection;

class ContactoRepository implements ContactoRepositoryInterface
{
    public function crear(array $data): Contacto
    {
        return Contacto::create($data);
    }

    public function actualizar(Contacto $contacto, array $data): Contacto
    {
        $contacto->update($data);
        return $contacto->fresh();
    }

    public function eliminar(Contacto $contacto): void
    {
        $contacto->delete();
    }

    public function restaurar(int $id): Contacto
    {
        /** @var Contacto $contacto */
        $contacto = Contacto::withTrashed()->findOrFail($id);
        $contacto->restore();
        return $contacto;
    }

    public function buscarPorId(int $id): ?Contacto
    {
        return Contacto::find($id);
    }

    public function porCliente(int $clienteId): Collection
    {
        return Contacto::where('cliente_id', $clienteId)->orderBy('principal', 'desc')->orderBy('nombre')->get();
    }

    public function quitarPrincipalDeCliente(int $clienteId, ?int $exceptoId = null): void
    {
        Contacto::where('cliente_id', $clienteId)
            ->where('principal', true)
            ->when($exceptoId !== null, fn ($q) => $q->where('id', '!=', $exceptoId))
            ->update(['principal' => false]);
    }
}
