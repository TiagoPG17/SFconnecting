<?php

declare(strict_types=1);

namespace App\Support;

use App\Domain\Dashboard\Models\VendedorEquivalencia;
use App\Models\User;

/**
 * Las funcionalidades de SGP (modales "Cargar solicitudes de cotización" en
 * Prospectos y Negocios) son exclusivas de comerciales de Formacol — Contiflex
 * no las usa. No hay un campo único de compañía en el usuario, así que se
 * infiere de sus mapeos en Vendedor Equivalencia (mapeo-vendedores):
 * - Sin mapeo, o con Formacol (sola o junto a Contiflex) → permitido.
 * - Solo Contiflex (compania=2, sin Formacol) → bloqueado.
 * Admin y gerente siempre tienen acceso, sin importar su compañía.
 */
class AccesoSgp
{
    private const COMPANIA_FORMACOL = 1;

    public static function permitido(User $user): bool
    {
        if ($user->hasAnyRole(['admin', 'gerente'])) {
            return true;
        }

        $companias = VendedorEquivalencia::where('asesor_id', $user->id)
            ->where('activo', true)
            ->pluck('compania')
            ->unique();

        if ($companias->isEmpty()) {
            return true;
        }

        return $companias->contains(self::COMPANIA_FORMACOL);
    }
}
