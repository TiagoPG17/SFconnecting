<?php

declare(strict_types=1);

namespace App\Domain\Prospectos\Exceptions;

use RuntimeException;

class ProspectoDuplicadoException extends RuntimeException
{
    public static function porEmail(string $email): self
    {
        return new self("Ya existe un prospecto con el email '{$email}'.");
    }

    public static function porEmpresaYContacto(string $empresa, string $contacto): self
    {
        return new self("Ya existe un prospecto para '{$empresa}' con el contacto '{$contacto}'.");
    }
}
