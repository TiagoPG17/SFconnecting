<?php

declare(strict_types=1);

namespace App\Domain\Prospectos\Exceptions;

use RuntimeException;

class ConversionProspectoException extends RuntimeException
{
    public static function yaConvertido(string $empresa): self
    {
        return new self("El prospecto '{$empresa}' ya fue convertido en cliente.");
    }

    public static function estadoNoPermiteConversion(string $estado): self
    {
        return new self("El estado '{$estado}' no permite la conversión a cliente.");
    }
}
