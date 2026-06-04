<?php

declare(strict_types=1);

namespace App\Domain\Negocios\Policies;

use App\Domain\Negocios\Models\Negocio;
use App\Models\User;

class NegocioPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermissionTo('negocios.ver');
    }

    public function view(User $user, Negocio $negocio): bool
    {
        if ($user->hasAnyRole(['admin', 'gerente'])) {
            return true;
        }

        return $user->hasRole('comercial') && $negocio->asesor_id === $user->id;
    }

    public function create(User $user): bool
    {
        return $user->hasPermissionTo('negocios.crear');
    }

    public function update(User $user, Negocio $negocio): bool
    {
        if ($user->hasRole('admin')) {
            return true;
        }

        return $user->hasRole('comercial') && $negocio->asesor_id === $user->id;
    }

    public function delete(User $user, Negocio $negocio): bool
    {
        return $user->hasRole('admin');
    }

    public function cerrar(User $user, Negocio $negocio): bool
    {
        return $user->hasPermissionTo('negocios.cerrar');
    }
}
