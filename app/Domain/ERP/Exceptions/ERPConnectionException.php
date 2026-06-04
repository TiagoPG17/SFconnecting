<?php

declare(strict_types=1);

namespace App\Domain\ERP\Exceptions;

use RuntimeException;

class ERPConnectionException extends RuntimeException
{
    public static function timeout(string $host): self
    {
        return new self("Timeout al conectar con ERP Contiflex en '{$host}'.");
    }

    public static function unavailable(): self
    {
        return new self('ERP Contiflex no disponible en este momento.');
    }

    public static function queryFailed(string $reason): self
    {
        return new self("Consulta ERP fallida: {$reason}");
    }
}
