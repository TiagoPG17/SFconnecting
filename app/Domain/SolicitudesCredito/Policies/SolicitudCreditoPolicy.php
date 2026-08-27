<?php

declare(strict_types=1);

namespace App\Domain\SolicitudesCredito\Policies;

use App\Domain\SolicitudesCredito\Models\SolicitudCredito;
use App\Models\User;

class SolicitudCreditoPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermissionTo('solicitudes_credito.ver');
    }

    public function view(User $user, SolicitudCredito $solicitud): bool
    {
        if ($user->hasAnyRole(['admin', 'gerente', 'cartera'])) {
            return true;
        }

        return $user->hasRole('comercial') && $solicitud->asesor_id === $user->id;
    }

    public function create(User $user): bool
    {
        return $user->hasAnyRole(['admin', 'comercial']);
    }

    public function delete(User $user, SolicitudCredito $solicitud): bool
    {
        return $user->hasRole('admin');
    }

    public function revisar(User $user, SolicitudCredito $solicitud): bool
    {
        return $user->hasPermissionTo('solicitudes_credito.revisar');
    }
}
