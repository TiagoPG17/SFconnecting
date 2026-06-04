<?php

declare(strict_types=1);

namespace App\Domain\Clientes\Policies;

use App\Domain\Clientes\Models\Cliente;
use App\Models\User;

class ClientePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasAnyRole(['admin', 'gerente', 'comercial']);
    }

    public function view(User $user, Cliente $cliente): bool
    {
        if ($user->hasAnyRole(['admin', 'gerente'])) {
            return true;
        }

        return $user->hasRole('comercial') && $cliente->user_id === $user->id;
    }

    public function create(User $user): bool
    {
        return $user->hasAnyRole(['admin', 'comercial']);
    }

    public function update(User $user, Cliente $cliente): bool
    {
        if ($user->hasRole('admin')) {
            return true;
        }

        return $user->hasRole('comercial') && $cliente->user_id === $user->id;
    }

    public function delete(User $user, Cliente $cliente): bool
    {
        return $user->hasRole('admin');
    }

    public function restore(User $user, Cliente $cliente): bool
    {
        return $user->hasRole('admin');
    }

    public function forceDelete(User $user, Cliente $cliente): bool
    {
        return $user->hasRole('admin');
    }
}
