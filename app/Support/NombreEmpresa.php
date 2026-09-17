<?php

declare(strict_types=1);

namespace App\Support;

use Illuminate\Support\Str;

/**
 * Normalización de nombre de empresa para cruzar candidatos de SGP contra
 * Cliente/Prospecto locales cuando no hay un identificador exacto (NIT) en
 * ambos lados. Mismo criterio que usa SGP para cruzar contra su propio
 * maestro de clientes: mayúsculas, sin tildes/puntos/espacios, SAS≈SA.
 */
class NombreEmpresa
{
    public static function normalizar(?string $nombre): string
    {
        if (! $nombre) {
            return '';
        }

        $normalizado = Str::of($nombre)->ascii()->upper()->toString();
        $normalizado = preg_replace('/[^A-Z0-9 ]/', '', $normalizado);
        $normalizado = trim(preg_replace('/\bSAS\b/', 'SA', $normalizado));

        return str_replace(' ', '', $normalizado);
    }
}
