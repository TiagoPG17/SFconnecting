<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web;

use App\Domain\SolicitudesCotizacion\Repositories\SolicitudCotizacionRepositoryInterface;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;
use Throwable;

class SolicitudCotizacionWebController extends Controller
{
    public function __construct(
        private readonly SolicitudCotizacionRepositoryInterface $repo,
    ) {}

    public function index(Request $request): View
    {
        $filtros = $this->filtrosDesde($request);

        $erpDisponible = true;
        $resumen       = collect();
        $solicitudes   = null;
        $vendedores    = collect();

        try {
            $vendedores  = $this->repo->vendedoresDisponibles();
            $resumen     = $this->repo->resumenPorComercial($filtros);
            $solicitudes = $this->repo->solicitudes($filtros);
        } catch (Throwable $e) {
            $erpDisponible = false;
            Log::warning('solicitudes-cotizacion.index: ERP no disponible', ['exception' => $e->getMessage()]);
        }

        return view('solicitudes-cotizacion.index', compact('resumen', 'solicitudes', 'vendedores', 'filtros', 'erpDisponible'));
    }

    public function escalas(Request $request): View
    {
        $filtros = $this->filtrosDesde($request);

        $erpDisponible = true;
        $escalas       = null;
        $vendedores    = collect();

        try {
            $vendedores = $this->repo->vendedoresDisponibles();
            $escalas    = $this->repo->escalas($filtros);
        } catch (Throwable $e) {
            $erpDisponible = false;
            Log::warning('solicitudes-cotizacion.escalas: ERP no disponible', ['exception' => $e->getMessage()]);
        }

        return view('solicitudes-cotizacion.escalas', compact('escalas', 'vendedores', 'filtros', 'erpDisponible'));
    }

    public function show(string $nroSolicitud): View
    {
        try {
            $solicitud = $this->repo->buscarSolicitud($nroSolicitud);
        } catch (Throwable $e) {
            Log::warning('solicitudes-cotizacion.show: ERP no disponible', ['exception' => $e->getMessage()]);
            abort(503, 'Sin conexión al ERP. No se puede cargar la solicitud en este momento.');
        }

        abort_if(! $solicitud, 404);

        return view('solicitudes-cotizacion.show', compact('solicitud'));
    }

    public function bajas(): View
    {
        $erpDisponible = true;
        $bajas         = null;

        try {
            $bajas = $this->repo->bajas();
        } catch (Throwable $e) {
            $erpDisponible = false;
            Log::warning('solicitudes-cotizacion.bajas: ERP no disponible', ['exception' => $e->getMessage()]);
        }

        return view('solicitudes-cotizacion.bajas', compact('bajas', 'erpDisponible'));
    }

    private function filtrosDesde(Request $request): array
    {
        $filtros = $request->only(['vendedor', 'situacion_cliente', 'situacion_escala', 'buscar']);
        $filtros['mes_actual'] = $request->boolean('mes_actual', true);

        return $filtros;
    }
}
