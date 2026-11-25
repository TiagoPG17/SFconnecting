<?php

declare(strict_types=1);

namespace App\Domain\RotulosMP\Services;

use App\Domain\RotulosMP\Contracts\ImpresoraZplInterface;
use App\Domain\RotulosMP\Exceptions\RotuloException;

/** Envío de ZPL crudo al puerto 9100 de la Zebra, codificado en cp850 (igual que SGP en producción). */
class ImpresoraZplTcp implements ImpresoraZplInterface
{
    private const TIMEOUT_SEGUNDOS = 8;

    public function enviar(string $ip, int $puerto, string $zpl): void
    {
        $bytes = mb_convert_encoding($zpl, 'CP850', 'UTF-8');

        $socket = @fsockopen($ip, $puerto, $errno, $errstr, self::TIMEOUT_SEGUNDOS);
        if ($socket === false) {
            throw new RotuloException("La impresora {$ip} no responde ({$errstr}). Revisa que esté encendida y en red.");
        }

        try {
            stream_set_timeout($socket, self::TIMEOUT_SEGUNDOS);

            $enviados = 0;
            $largo    = strlen($bytes);
            while ($enviados < $largo) {
                $escritos = fwrite($socket, substr($bytes, $enviados));
                if ($escritos === false || $escritos === 0) {
                    throw new RotuloException("Se cortó la conexión con la impresora {$ip} mientras se enviaban los rótulos.");
                }
                $enviados += $escritos;
            }
            fflush($socket);
        } finally {
            fclose($socket);
        }
    }
}
