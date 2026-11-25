<?php

declare(strict_types=1);

namespace App\Domain\RotulosMP\Services;

use App\Domain\ERP\Contracts\ERPRepositoryInterface;
use App\Domain\RotulosMP\Contracts\ImpresoraZplInterface;
use App\Domain\RotulosMP\Exceptions\RotuloException;
use App\Domain\RotulosMP\Models\RotuloImpresion;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Rótulos de costales para salidas de MP de SGP (SLI), sin tocar SGP: lee sgpforma
 * en solo lectura, reparte cada material en costales y envía el ZPL directo a la Zebra.
 */
class RotuloCostalService
{
    public function __construct(
        private readonly ERPRepositoryInterface $erp,
        private readonly ImpresoraZplInterface $impresora,
        private readonly CalculadoraCostales $calculadora,
        private readonly GeneradorZplRotulo $zpl,
    ) {}

    /** "299428" → "SLI299428"; "sli299428 " → "SLI299428". */
    public function normalizarDocumento(string $documento): string
    {
        $documento = strtoupper(trim($documento));

        if (preg_match('/^\d+$/', $documento)) {
            $documento = 'SLI'.$documento;
        }

        if (! preg_match('/^[A-Z]{1,6}\d{1,12}$/', $documento)) {
            throw new RotuloException('Número de documento no válido. Ejemplo: SLI299428.');
        }

        return $documento;
    }

    /**
     * Renglones del documento para que bodega digite el peso por costal.
     *
     * @return array{documento: string, impresora: array{nombre: string, ip: string}, max_etiquetas: int, renglones: list<array>}
     */
    public function consultar(string $documento): array
    {
        $documento = $this->normalizarDocumento($documento);

        return [
            'documento'     => $documento,
            'impresora'     => $this->impresoraConfigurada(),
            'max_etiquetas' => $this->maxEtiquetas(),
            'renglones'     => $this->renglones($documento),
        ];
    }

    /**
     * Calcula qué se va a imprimir sin enviar nada. Los renglones con peso vacío se omiten.
     *
     * @param  array<int|string, string|null>  $pesos  id_row => peso por costal (misma unidad del renglón).
     * @return array{documento: string, total_rotulos: int, materiales: list<array>}
     */
    public function planificar(string $documento, array $pesos): array
    {
        $documento = $this->normalizarDocumento($documento);
        $renglones = collect($this->renglones($documento))->keyBy('id_row');

        $pesos = array_filter($pesos, fn ($p) => $p !== null && trim((string) $p) !== '');
        if ($pesos === []) {
            throw new RotuloException('Digita el peso por costal de al menos un material.');
        }

        $materiales = [];
        $total      = 0;
        foreach ($pesos as $idRow => $peso) {
            $renglon = $renglones->get((int) $idRow);
            if ($renglon === null) {
                throw new RotuloException("El renglón {$idRow} no pertenece al documento {$documento}. Vuelve a buscarlo.");
            }

            try {
                $cantidades = $this->calculadora->calcular($renglon['cantidad'], (string) $peso, $this->maxEtiquetas());
            } catch (RotuloException $e) {
                throw new RotuloException("{$renglon['ref']}: {$e->getMessage()}");
            }

            $total += count($cantidades);
            $materiales[] = [
                'renglon'     => $renglon,
                'peso_costal' => $this->calculadora->formatear($this->calculadora->aEscala((string) $peso)),
                'cantidades'  => $cantidades,
                'resumen'     => $this->resumen($cantidades, (string) $renglon['unidad']),
            ];
        }

        if ($total > $this->maxEtiquetas()) {
            throw new RotuloException("Saldrían {$total} rótulos en total (máximo {$this->maxEtiquetas()} por impresión). Imprime por partes o revisa los pesos.");
        }

        // Mismo orden que SGP (capa desc, artículo), sin importar el orden en que llegaron los pesos.
        $orden = $renglones->keys()->flip();
        usort($materiales, fn ($a, $b) => $orden[$a['renglon']['id_row']] <=> $orden[$b['renglon']['id_row']]);

        return ['documento' => $documento, 'total_rotulos' => $total, 'materiales' => $materiales];
    }

    /**
     * Imprime los rótulos en una sola conexión y deja registro. Bloquea el documento
     * mientras imprime para que un doble clic no saque etiquetas repetidas.
     *
     * @param  array<int|string, string|null>  $pesos
     * @return array{documento: string, total_rotulos: int, impresora: array{nombre: string, ip: string}, materiales: list<array>}
     */
    public function imprimir(string $documento, array $pesos, ?int $userId): array
    {
        $plan      = $this->planificar($documento, $pesos);
        $impresora = $this->impresoraConfigurada();

        $lock = Cache::lock('rotulos-mp:'.$plan['documento'], 30);
        if (! $lock->get()) {
            throw new RotuloException("Ya se están imprimiendo rótulos del {$plan['documento']}. Espera unos segundos.");
        }

        try {
            $fecha     = now();
            $etiquetas = [];
            foreach ($plan['materiales'] as $m) {
                $n = count($m['cantidades']);
                foreach ($m['cantidades'] as $i => $cantidad) {
                    $etiquetas[] = $this->zpl->etiqueta($m['renglon'], $cantidad, $i + 1, $n, $fecha);
                }
            }

            $this->impresora->enviar($impresora['ip'], $this->puerto(), $this->zpl->documento($etiquetas));

            try {
                DB::transaction(function () use ($plan, $impresora, $userId) {
                    foreach ($plan['materiales'] as $m) {
                        RotuloImpresion::create([
                            'documento'      => $plan['documento'],
                            'renglon_id'     => $m['renglon']['id_row'],
                            'referencia'     => $m['renglon']['ref'],
                            'lote'           => $m['renglon']['lote'],
                            'unidad'         => $m['renglon']['unidad'],
                            'cantidad_total' => $m['renglon']['cantidad'],
                            'peso_costal'    => $m['peso_costal'],
                            'num_rotulos'    => count($m['cantidades']),
                            'impresora'      => $impresora['nombre'],
                            'impresora_ip'   => $impresora['ip'],
                            'user_id'        => $userId,
                        ]);
                    }
                });
            } catch (Throwable $e) {
                // Los rótulos ya salieron: un fallo de la bitácora no debe reportarse como fallo de impresión.
                Log::error('rotulos-mp: impresos pero no se pudo guardar el registro', [
                    'documento' => $plan['documento'],
                    'exception' => $e->getMessage(),
                ]);
            }
        } finally {
            $lock->release();
        }

        return $plan + ['impresora' => $impresora];
    }

    /** @return array{nombre: string, ip: string} */
    public function impresoraConfigurada(): array
    {
        $nombre    = (string) config('services.rotulos_mp.impresora');
        $impresora = $this->erp->impresoraSgpPorNombre($nombre);

        if ($impresora === null || $impresora['ip'] === '') {
            throw new RotuloException("No se encontró la impresora {$nombre} en SGP (ini_impresoras).");
        }

        return $impresora;
    }

    /** @return list<array> */
    private function renglones(string $documento): array
    {
        $renglones = $this->erp->renglonesSalidaMP($documento);

        if ($renglones === []) {
            throw new RotuloException("No se encontró el documento {$documento} o no tiene materiales.");
        }

        return $renglones;
    }

    /** ["25000","25000","3000"] → "2 de 25000 GRS y 1 de 3000 GRS" */
    private function resumen(array $cantidades, string $unidad): string
    {
        $grupos = array_count_values($cantidades);
        $partes = [];
        foreach ($grupos as $cantidad => $veces) {
            $partes[] = trim("{$veces} de {$cantidad} {$unidad}");
        }

        return implode(' y ', $partes);
    }

    private function maxEtiquetas(): int
    {
        return max(1, (int) config('services.rotulos_mp.max_etiquetas', 100));
    }

    private function puerto(): int
    {
        return (int) config('services.rotulos_mp.puerto', 9100);
    }
}
