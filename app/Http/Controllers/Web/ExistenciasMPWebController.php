<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web;

use App\Domain\ERP\Contracts\ERPRepositoryInterface;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;
use Throwable;

class ExistenciasMPWebController extends Controller
{
    public function __construct(
        private readonly ERPRepositoryInterface $erp,
    ) {}

    public function index(Request $request): View
    {
        $buscar     = $request->get('buscar');
        $porPagina  = 20;
        $pagina     = max(1, (int) $request->query('page', 1));
        $ordenarPor = $request->query('sort');
        $direccion  = $request->query('dir') === 'desc' ? 'desc' : 'asc';
        $diasVencer = $request->query('dias_vencer') !== null ? (int) $request->query('dias_vencer') : null;

        $existencias   = [];
        $total         = 0;
        $erpDisponible = false;

        try {
            if ($this->erp->isAvailable()) {
                $erpDisponible = true;
                $resultado     = $this->erp->existenciasMP($buscar, $pagina, $porPagina, $ordenarPor, $direccion, $diasVencer);
                $existencias   = $resultado['data'];
                $total         = $resultado['total'];
            }
        } catch (Throwable) {
        }

        $totalPaginas = $total > 0 ? (int) ceil($total / $porPagina) : 1;
        $pagina       = min($pagina, $totalPaginas);

        return view('existencias-mp.index', compact(
            'existencias', 'total', 'erpDisponible', 'buscar', 'pagina', 'totalPaginas', 'porPagina',
            'ordenarPor', 'direccion', 'diasVencer'
        ));
    }

    public function actualizarCampo(Request $request): JsonResponse
    {
        $data = $request->validate([
            'compania'   => ['required', 'integer'],
            'cod_bodega' => ['required', 'string', 'max:20'],
            'referencia' => ['required', 'string', 'max:50'],
            'lote'       => ['required', 'string', 'max:100'],
            'campo'      => ['required', 'in:fecha_vencimiento,ubicacion'],
            'valor'      => ['nullable', 'string', 'max:100'],
        ]);

        $columna = $data['campo'] === 'fecha_vencimiento' ? 'FechaVencimiento' : 'Ubicacion';
        $valor   = $data['valor'] !== null && $data['valor'] !== '' ? $data['valor'] : null;

        try {
            $this->erp->actualizarLoteExistenciaMP(
                $data['compania'],
                $data['cod_bodega'],
                $data['referencia'],
                $data['lote'],
                [$columna => $valor]
            );

            return response()->json(['success' => true]);
        } catch (Throwable $e) {
            Log::error('existencias-mp.actualizarCampo: fallo al guardar', [
                'data'      => $data,
                'exception' => $e->getMessage(),
            ]);

            return response()->json(['success' => false, 'message' => 'No se pudo guardar el cambio en el ERP.'], 500);
        }
    }
}
