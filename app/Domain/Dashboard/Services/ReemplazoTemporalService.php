<?php

declare(strict_types=1);

namespace App\Domain\Dashboard\Services;

use App\Domain\Clientes\Models\Cliente;
use App\Domain\Clientes\Repositories\ClienteRepositoryInterface;
use App\Domain\Dashboard\Models\ReemplazoClienteMovido;
use App\Domain\Dashboard\Models\VendedorEquivalencia;
use App\Domain\Negocios\Repositories\NegocioRepositoryInterface;
use Illuminate\Support\Facades\DB;

class ReemplazoTemporalService
{
    public function __construct(
        private readonly ClienteRepositoryInterface $clienteRepo,
        private readonly NegocioRepositoryInterface $negocioRepo,
    ) {}

    /** Mueve los clientes del titular del código al reemplazante. Retorna cuántos se movieron. */
    public function activar(VendedorEquivalencia $reemplazo): int
    {
        $titular = VendedorEquivalencia::where('compania', $reemplazo->compania)
            ->where('cod_vendedor_siesa', $reemplazo->cod_vendedor_siesa)
            ->where('es_reemplazo', false)
            ->first();

        if (! $titular || $titular->asesor_id === $reemplazo->asesor_id) {
            return 0;
        }

        return DB::transaction(function () use ($reemplazo, $titular) {
            $clientes = $this->clienteRepo->reasignarAsesor(
                $titular->asesor_id,
                $reemplazo->asesor_id,
                $reemplazo->compania
            );

            foreach ($clientes as $cliente) {
                ReemplazoClienteMovido::create([
                    'vendedor_equivalencia_id' => $reemplazo->id,
                    'cliente_id'               => $cliente->id,
                    'titular_user_id'          => $titular->asesor_id,
                ]);
            }

            return $clientes->count();
        });
    }

    /**
     * Devuelve al titular los clientes que se movieron por este reemplazo, y también
     * los negocios que el reemplazante haya creado para esos clientes mientras cubría
     * (los negocios que el titular ya tenía antes nunca se tocan, siguen siendo suyos).
     *
     * @return array{clientes: int, negocios: int}
     */
    public function revertir(VendedorEquivalencia $reemplazo): array
    {
        return DB::transaction(function () use ($reemplazo) {
            $movidos = ReemplazoClienteMovido::where('vendedor_equivalencia_id', $reemplazo->id)
                ->whereNull('revertido_en')
                ->get();

            $negocios = 0;

            if ($movidos->isNotEmpty()) {
                $titularUserId = $movidos->first()->titular_user_id;
                $clienteIds    = $movidos->pluck('cliente_id')->all();

                $negocios = $this->negocioRepo
                    ->reasignarPorClientes($clienteIds, $reemplazo->asesor_id, $titularUserId)
                    ->count();
            }

            foreach ($movidos as $movido) {
                Cliente::where('id', $movido->cliente_id)
                    ->where('user_id', $reemplazo->asesor_id)
                    ->update(['user_id' => $movido->titular_user_id]);

                $movido->update(['revertido_en' => now()]);
            }

            return ['clientes' => $movidos->count(), 'negocios' => $negocios];
        });
    }
}
