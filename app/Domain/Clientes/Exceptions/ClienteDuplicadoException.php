<?php

declare(strict_types=1);

namespace App\Domain\Clientes\Exceptions;

use RuntimeException;

class ClienteDuplicadoException extends RuntimeException
{
    public static function porNit(string $nit): self
    {
        return new self("Ya existe un cliente con NIT '{$nit}'.");
    }

    public static function porEmail(string $email): self
    {
        return new self("Ya existe un cliente con email '{$email}'.");
    }
}
