<?php

declare(strict_types=1);

namespace App\Domain\Prospectos\Policies;

use App\Domain\Prospectos\Models\Prospecto;
use App\Models\User;

class ProspectoPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermissionTo('prospectos.ver');
    }

    public function view(User $user, Prospecto $prospecto): bool
    {
        if ($user->hasAnyRole(['admin', 'gerente'])) {
            return true;
        }

        return $user->hasRole('comercial') && $prospecto->asesor_id === $user->id;
    }

    public function create(User $user): bool
    {
        return $user->hasPermissionTo('prospectos.crear');
    }

    public function update(User $user, Prospecto $prospecto): bool
    {
        if ($user->hasRole('admin')) {
            return true;
        }

        return $user->hasRole('comercial') && $prospecto->asesor_id === $user->id;
    }

    public function delete(User $user, Prospecto $prospecto): bool
    {
        return $user->hasRole('admin');
    }

    public function convertir(User $user, Prospecto $prospecto): bool
    {
        if (! $user->hasPermissionTo('prospectos.convertir')) {
            return false;
        }

        if ($user->hasAnyRole(['admin', 'gerente'])) {
            return true;
        }

        return $user->hasRole('comercial') && $prospecto->asesor_id === $user->id;
    }
}
