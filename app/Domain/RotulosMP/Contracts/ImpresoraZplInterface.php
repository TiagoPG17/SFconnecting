<?php

declare(strict_types=1);

namespace App\Domain\RotulosMP\Contracts;

use App\Domain\RotulosMP\Exceptions\RotuloException;

interface ImpresoraZplInterface
{
    /**
     * Envía el ZPL (texto UTF-8) a una Zebra por TCP en una sola conexión.
     *
     * @throws RotuloException si no se puede conectar o no se envía completo.
     */
    public function enviar(string $ip, int $puerto, string $zpl): void;
}
