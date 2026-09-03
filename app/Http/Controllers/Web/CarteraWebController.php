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

class CarteraWebController extends Controller
{
    public function __construct(
        private readonly ERPRepositoryInterface $erp,
    ) {}

    public function index(Request $request): View
    {
        $compania  = (int) $request->query('compania', 0);
        $soloHoy   = $request->boolean('solo_hoy');
        $buscar    = $request->query('buscar');
        $porPagina = 20;
        $pagina    = max(1, (int) $request->query('page', 1));

        $pedidos       = [];
        $total         = 0;
        $pendientes    = [];
        $erpDisponible = false;

        try {
            if ($this->erp->isAvailable()) {
                $erpDisponible = true;
                $resultado     = $this->erp->gestionCartera($compania, $soloHoy, $buscar, $pagina, $porPagina);
                $pedidos       = $resultado['data'];
                $total         = $resultado['total'];
                $pendientes    = $this->erp->notificacionesCarteraPendientes($compania);
            }
        } catch (Throwable) {
        }

        $totalPaginas = $total > 0 ? (int) ceil($total / $porPagina) : 1;
        $pagina       = min($pagina, $totalPaginas);
        $puedeEditar  = $request->user()->hasAnyRole(['cartera', 'admin']);

        return view('gestion-cartera.index', compact(
            'pedidos', 'pendientes', 'erpDisponible', 'compania', 'soloHoy',
            'buscar', 'pagina', 'totalPaginas', 'total', 'porPagina', 'puedeEditar'
        ));
    }

    public function notificar(Request $request): JsonResponse
    {
        $data = $request->validate([
            'pedidos'                          => ['required', 'array', 'min:1'],
            'pedidos.*.compania'               => ['required', 'integer'],
            'pedidos.*.nro_documento'          => ['required', 'string', 'max:20'],
            'pedidos.*.fecha_inicio_cobro'      => ['required', 'date'],
        ]);

        try {
            $insertados = $this->erp->notificarCartera($data['pedidos']);

            return response()->json(['success' => true, 'insertados' => $insertados]);
        } catch (Throwable $e) {
            Log::error('gestion-cartera.notificar: fallo al insertar notificaciones', [
                'data'      => $data,
                'exception' => $e->getMessage(),
            ]);

            return response()->json(['success' => false, 'message' => 'No se pudo registrar la notificación.'], 500);
        }
    }

    public function resolver(Request $request): JsonResponse
    {
        $data = $request->validate([
            'compania'      => ['required', 'integer'],
            'nro_documento' => ['required', 'string', 'max:20'],
        ]);

        try {
            $resuelto = $this->erp->marcarNotificacionCarteraResuelta($data['compania'], $data['nro_documento']);

            return response()->json(['success' => true, 'resuelto' => $resuelto]);
        } catch (Throwable $e) {
            Log::error('gestion-cartera.resolver: fallo al marcar como resuelto', [
                'data'      => $data,
                'exception' => $e->getMessage(),
            ]);

            return response()->json(['success' => false, 'message' => 'No se pudo marcar como resuelto.'], 500);
        }
    }
}
