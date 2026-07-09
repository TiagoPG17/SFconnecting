<?php

declare(strict_types=1);

namespace App\Domain\SolicitudesCredito\Policies;

use App\Domain\SolicitudesCredito\Models\SolicitudCredito;
use App\Models\User;

class SolicitudCreditoPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasRole('admin');
    }

    public function view(User $user, SolicitudCredito $solicitud): bool
    {
        return $user->hasRole('admin');
    }

    public function create(User $user): bool
    {
        return $user->hasRole('admin');
    }

    public function delete(User $user, SolicitudCredito $solicitud): bool
    {
        return $user->hasRole('admin');
    }

    public function revisar(User $user, SolicitudCredito $solicitud): bool
    {
        return $user->hasRole('admin');
    }
}
