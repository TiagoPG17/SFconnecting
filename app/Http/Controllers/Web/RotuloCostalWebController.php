<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web;

use App\Domain\ERP\Exceptions\ERPConnectionException;
use App\Domain\RotulosMP\Exceptions\RotuloException;
use App\Domain\RotulosMP\Services\RotuloCostalService;
use App\Http\Controllers\Controller;
use Closure;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Throwable;

/** Rótulos de costales de salida de MP (SLI de SGP), dentro de Consulta de lotes. */
class RotuloCostalWebController extends Controller
{
    public function __construct(
        private readonly RotuloCostalService $rotulos,
    ) {}

    public function consultar(Request $request): JsonResponse
    {
        $data = $request->validate(['documento' => ['required', 'string', 'max:20']]);

        return $this->responder(fn () => $this->rotulos->consultar($data['documento']));
    }

    public function previsualizar(Request $request): JsonResponse
    {
        $data = $this->validarPesos($request);

        return $this->responder(fn () => $this->resumenPlan($this->rotulos->planificar($data['documento'], $data['pesos'])));
    }

    public function imprimir(Request $request): JsonResponse
    {
        $data = $this->validarPesos($request);

        return $this->responder(function () use ($data, $request) {
            $resultado = $this->rotulos->imprimir($data['documento'], $data['pesos'], $request->user()?->id);

            return $this->resumenPlan($resultado) + ['impresora' => $resultado['impresora']];
        });
    }

    /** @return array{documento: string, pesos: array<int|string, string|null>} */
    private function validarPesos(Request $request): array
    {
        return $request->validate([
            'documento' => ['required', 'string', 'max:20'],
            'pesos'     => ['required', 'array', 'max:200'],
            'pesos.*'   => ['nullable', 'string', 'max:20'],
        ]);
    }

    private function resumenPlan(array $plan): array
    {
        return [
            'documento'     => $plan['documento'],
            'total_rotulos' => $plan['total_rotulos'],
            'materiales'    => array_map(fn ($m) => [
                'id_row'          => $m['renglon']['id_row'],
                'ref'             => $m['renglon']['ref'],
                'nombre_material' => $m['renglon']['nombre_material'],
                'unidad'          => $m['renglon']['unidad'],
                'num_rotulos'     => count($m['cantidades']),
                'resumen'         => $m['resumen'],
            ], $plan['materiales']),
        ];
    }

    private function responder(Closure $accion): JsonResponse
    {
        try {
            return response()->json(['success' => true] + $accion());
        } catch (RotuloException $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        } catch (ERPConnectionException $e) {
            Log::error('rotulos-mp: error consultando SGP', ['exception' => $e->getMessage()]);

            return response()->json(['success' => false, 'message' => 'No se pudo consultar SGP en este momento.'], 503);
        } catch (Throwable $e) {
            Log::error('rotulos-mp: error inesperado', ['exception' => $e->getMessage()]);

            return response()->json(['success' => false, 'message' => 'Ocurrió un error inesperado. Intenta de nuevo.'], 500);
        }
    }
}
