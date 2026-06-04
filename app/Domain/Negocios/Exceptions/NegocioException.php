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
}
