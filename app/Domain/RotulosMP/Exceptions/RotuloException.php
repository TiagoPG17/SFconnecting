<?php

declare(strict_types=1);

namespace App\Domain\RotulosMP\Exceptions;

use RuntimeException;

/** Error de negocio de rótulos de costales: el mensaje se muestra tal cual al usuario. */
class RotuloException extends RuntimeException
{
}
