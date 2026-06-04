<?php

declare(strict_types=1);

namespace App\Domain\Seguimientos\Exceptions;

use RuntimeException;

class SeguimientoException extends RuntimeException
{
    public static function clienteInexistente(int $clienteId): self
    {
        return new self("No existe el cliente con ID {$clienteId}.");
    }

    public static function fechaInvalida(string $fecha): self
    {
        return new self("La fecha '{$fecha}' no es válida para un seguimiento.");
    }

    public static function contactoEliminado(int $contactoId): self
    {
        return new self("El contacto ID {$contactoId} fue eliminado y no puede usarse.");
    }
}
