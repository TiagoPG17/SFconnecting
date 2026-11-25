<?php

declare(strict_types=1);

namespace App\Domain\RotulosMP\Services;

use Carbon\CarbonInterface;

/**
 * Arma el ZPL del rótulo de salida de MP con el mismo formato que SGP usa para las
 * impresoras ZTC/S4M (799 × 799 puntos). La única diferencia es que cada etiqueta
 * representa un costal: ROT n/N y CANTIDAD = peso de ese costal.
 */
class GeneradorZplRotulo
{
    /** Configuración de la Zebra; va una sola vez al inicio del envío. ^CI0 + cp850 para tildes y Ñ. */
    public const CABECERA = '^XA~TA0~JSB^LT0^MMT^MNW^MTT^PON^PMN^LH0,0^JMA^PR4,4^MD0^JUS^LRN^CI0^XZ';

    /**
     * @param  array<string, mixed>  $renglon  Fila de renglonesSalidaMP().
     */
    public function etiqueta(array $renglon, string $cantidad, int $n, int $total, CarbonInterface $fecha): string
    {
        $c = fn (string $campo) => $this->limpiar($renglon[$campo] ?? null);

        return '^XA^LL0799'
            .'^PW799'
            .'^FT27,50 ^A0N,36,36^FH\^FDPEDIDO: '.$c('pedido').'^FS'
            .'^FT27,95 ^A0N,26,26^FH\^FDDOCUMENTO: '.$c('documento').'        FORMULA: '.$c('formula').'^FS'
            .'^FT27,135^A0N,27,27^FH\^FD'.$c('cliente').'^FS'
            .'^FT27,175^A0N,26,26^FH\^FDCOLOR: '.$c('color').'   MATERIAL: '.$c('material').'^FS'
            .'^FT27,215^A0N,26,26^FH\^FDPROCESO: '.$c('proceso').'^FS'
            .'^FT27,260^A0N,36,36^FH\^FDCAPA: '.$c('capa').'    REF:'.$c('ref').'^FS'
            .'^FT27,305^A0N,36,36^FH\^FD'.$c('nombre_material').'^FS'
            .'^FT27,350^A0N,36,36^FH\^FDCANTIDAD: '.$this->limpiar($cantidad).' '.$c('unidad').'     LOTE:'.$c('lote').'^FS'
            .'^FT27,390^A0N,23,23^FH\^FDROT: '.$n.'/'.$total.'                  '.$fecha->format('d/m/Y').'^FS'
            .'^PQ1,0,1,Y^XZ';
    }

    /** @param  list<string>  $etiquetas */
    public function documento(array $etiquetas): string
    {
        return self::CABECERA.implode('', $etiquetas);
    }

    /**
     * Quita lo que rompe el ZPL: ^ y ~ son comandos y \ es el escape hexadecimal de ^FH\.
     * También saltos de línea y caracteres de control.
     */
    public function limpiar(mixed $valor): string
    {
        if ($valor === null) {
            return '';
        }

        $texto = preg_replace('/[\^~\\\\]|[\x00-\x1F\x7F]/u', ' ', (string) $valor) ?? '';

        return trim(preg_replace('/ {2,}/', ' ', $texto) ?? '');
    }
}
