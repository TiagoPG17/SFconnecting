<?php

declare(strict_types=1);

namespace App\Domain\Negocios\Exceptions;

use RuntimeException;

class NegocioException extends RuntimeException
{
    public static function perdidoSinMotivo(): self
    {
        return new self('Se requiere un motivo de pérdida al marcar el negocio como perdido.');
    }

    public static function yaFinalizado(string $nombre): self
    {
        return new self("El negocio '{$nombre}' ya está en un estado final y no puede modificarse.");
    }

    public static function sinVinculo(): self
    {
        return new self('El negocio debe estar vinculado a un prospecto o un cliente.');
    }

    public static function companiaRequerida(): self
    {
        return new self('Debes indicar para qué compañía (Formacol o Contiflex) es este negocio.');
    }

    public static function companiaNoPermitida(): self
    {
        return new self('No tienes asignada esa compañía. Verifica tu mapeo de vendedor.');
    }
}
