<?php

declare(strict_types=1);

namespace App\Domain\Seguimientos\Policies;

use App\Domain\Seguimientos\Models\Seguimiento;
use App\Models\User;

class SeguimientoPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasRole(['admin', 'gerente', 'comercial']);
    }

    public function view(User $user, Seguimiento $seguimiento): bool
    {
        return $user->hasRole(['admin', 'gerente', 'comercial']);
    }

    public function create(User $user): bool
    {
        return $user->hasAnyRole(['admin', 'comercial']);
    }

    public function update(User $user, Seguimiento $seguimiento): bool
    {
        return $user->hasRole('admin')
            || ($user->hasRole('comercial') && $user->id === $seguimiento->user_id);
    }

    public function delete(User $user, Seguimiento $seguimiento): bool
    {
        return $user->hasRole('admin')
            || ($user->hasRole('comercial') && $user->id === $seguimiento->user_id);
    }
}
