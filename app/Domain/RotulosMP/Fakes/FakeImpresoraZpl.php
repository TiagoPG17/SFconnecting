<?php

declare(strict_types=1);

namespace App\Domain\RotulosMP\Fakes;

use App\Domain\RotulosMP\Contracts\ImpresoraZplInterface;
use App\Domain\RotulosMP\Exceptions\RotuloException;

/** Impresora de pruebas: guarda lo enviado en memoria y nunca abre sockets. */
class FakeImpresoraZpl implements ImpresoraZplInterface
{
    /** @var list<array{ip: string, puerto: int, zpl: string}> */
    public array $envios = [];

    private bool $fallar = false;

    public function enviar(string $ip, int $puerto, string $zpl): void
    {
        if ($this->fallar) {
            throw new RotuloException("La impresora {$ip} no responde (simulado).");
        }

        $this->envios[] = compact('ip', 'puerto', 'zpl');
    }

    public function simularFallo(): void
    {
        $this->fallar = true;
    }
}
