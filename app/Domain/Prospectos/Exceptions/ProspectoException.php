<?php

declare(strict_types=1);

namespace App\Domain\Prospectos\Exceptions;

use RuntimeException;

class ProspectoException extends RuntimeException
{
    public static function companiaRequerida(): self
    {
        return new self('Debes indicar para qué compañía (Formacol o Contiflex) es este prospecto.');
    }

    public static function companiaNoPermitida(): self
    {
        return new self('No tienes asignada esa compañía. Verifica tu mapeo de vendedor.');
    }
}
