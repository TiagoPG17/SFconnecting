<?php

declare(strict_types=1);

namespace App\Domain\RotulosMP\Services;

use App\Domain\RotulosMP\Exceptions\RotuloException;

/**
 * Reparte la cantidad de un renglón en costales de peso estándar más uno de saldo
 * (opción A del requerimiento): 103000 / 25000 → 25000 ×4 + 3000.
 *
 * Trabaja con enteros escalados a 5 decimales (precisión de inv_movtodet.cantidad_art)
 * para que la suma de las etiquetas dé exactamente el total, sin errores de float.
 */
class CalculadoraCostales
{
    private const DECIMALES = 5;

    /**
     * @param  string  $total       Cantidad del renglón, ej. "103000.00000".
     * @param  string  $pesoCostal  Peso por costal en la misma unidad, ej. "25000".
     * @return list<string> Cantidad de cada etiqueta ya formateada, ej. ["25000", ..., "3000"].
     */
    public function calcular(string $total, string $pesoCostal, int $maxEtiquetas): array
    {
        $t = $this->aEscala($total);
        $p = $this->aEscala($pesoCostal);

        if ($t <= 0) {
            throw new RotuloException('El renglón no tiene cantidad para rotular.');
        }
        if ($p <= 0) {
            throw new RotuloException('El peso por costal debe ser mayor que cero.');
        }

        if ($p >= $t) {
            return [$this->formatear($t)];
        }

        $completos = intdiv($t, $p);
        $saldo     = $t % $p;
        $n         = $completos + ($saldo > 0 ? 1 : 0);

        if ($n > $maxEtiquetas) {
            throw new RotuloException(
                "Saldrían {$n} rótulos (máximo {$maxEtiquetas}). Revisa que el peso por costal esté en la misma unidad del material."
            );
        }

        $etiquetas = array_fill(0, $completos, $this->formatear($p));
        if ($saldo > 0) {
            $etiquetas[] = $this->formatear($saldo);
        }

        return $etiquetas;
    }

    /** "103000.00000" → 10300000000. Acepta solo dígitos con punto decimal opcional. */
    public function aEscala(string $valor): int
    {
        $valor = trim($valor);
        if (! preg_match('/^\d{1,13}(?:\.(\d*))?$/', $valor, $m)) {
            throw new RotuloException("Cantidad no válida: {$valor}");
        }

        [$entero] = explode('.', $valor);
        $decimales = $m[1] ?? '';

        if (strlen(rtrim($decimales, '0')) > self::DECIMALES) {
            throw new RotuloException('La cantidad admite máximo '.self::DECIMALES.' decimales.');
        }

        $decimales = str_pad(substr($decimales, 0, self::DECIMALES), self::DECIMALES, '0');

        return (int) $entero * 10 ** self::DECIMALES + (int) $decimales;
    }

    /** 2500000000 → "25000"; 250050000 → "2500.5". Sin ".0" final. */
    public function formatear(int $escalado): string
    {
        $base    = 10 ** self::DECIMALES;
        $entero  = intdiv($escalado, $base);
        $decimal = rtrim(str_pad((string) ($escalado % $base), self::DECIMALES, '0', STR_PAD_LEFT), '0');

        return $decimal === '' ? (string) $entero : "{$entero}.{$decimal}";
    }
}
