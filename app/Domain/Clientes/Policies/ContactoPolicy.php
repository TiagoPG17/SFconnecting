<?php

declare(strict_types=1);

namespace App\Domain\Clientes\Policies;

use App\Domain\Clientes\Models\Cliente;
use App\Domain\Clientes\Models\Contacto;
use App\Models\User;

class ContactoPolicy
{
    public function viewAny(User $user, Cliente $cliente): bool
    {
        return $user->hasRole(['admin', 'gerente'])
            || ($user->hasRole('comercial') && $user->id === $cliente->user_id);
    }

    public function view(User $user, Contacto $contacto): bool
    {
        return $user->hasRole(['admin', 'gerente'])
            || ($user->hasRole('comercial') && $user->id === $contacto->cliente->user_id);
    }

    public function create(User $user, Cliente $cliente): bool
    {
        return $user->hasRole('admin')
            || ($user->hasRole('comercial') && $user->id === $cliente->user_id);
    }

    public function update(User $user, Contacto $contacto): bool
    {
        return $user->hasRole('admin')
            || ($user->hasRole('comercial') && $user->id === $contacto->cliente->user_id);
    }

    public function delete(User $user, Contacto $contacto): bool
    {
        return $user->hasRole('admin')
            || ($user->hasRole('comercial') && $user->id === $contacto->cliente->user_id);
    }

    public function restore(User $user, Contacto $contacto): bool
    {
        return $user->hasRole('admin');
    }
}
