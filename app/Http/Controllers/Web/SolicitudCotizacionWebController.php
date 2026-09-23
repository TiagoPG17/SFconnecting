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
        $vendedorSgp  = $this->vendedorSgpDelUsuario($request);
        $verTodos     = $vendedorSgp === null;
        $sinCodigoSgp = $vendedorSgp === false;
        $filtros      = $this->filtrosDesde($request, $vendedorSgp);

        $erpDisponible = true;
        $resumen       = collect();
        $solicitudes   = null;
        $vendedores    = collect();

        if (! $sinCodigoSgp) {
            try {
                if ($verTodos) {
                    $vendedores = $this->repo->vendedoresDisponibles();
                }
                $resumen     = $this->repo->resumenPorComercial($filtros);
                $solicitudes = $this->repo->solicitudes($filtros);
            } catch (Throwable $e) {
                $erpDisponible = false;
                Log::warning('solicitudes-cotizacion.index: ERP no disponible', ['exception' => $e->getMessage()]);
            }
        }

        return view('solicitudes-cotizacion.index', compact('resumen', 'solicitudes', 'vendedores', 'filtros', 'erpDisponible', 'verTodos', 'sinCodigoSgp'));
    }

    public function escalas(Request $request): View
    {
        $vendedorSgp  = $this->vendedorSgpDelUsuario($request);
        $verTodos     = $vendedorSgp === null;
        $sinCodigoSgp = $vendedorSgp === false;
        $filtros      = $this->filtrosDesde($request, $vendedorSgp);

        $erpDisponible = true;
        $escalas       = null;
        $vendedores    = collect();

        if (! $sinCodigoSgp) {
            try {
                if ($verTodos) {
                    $vendedores = $this->repo->vendedoresDisponibles();
                }
                $escalas = $this->repo->escalas($filtros);
            } catch (Throwable $e) {
                $erpDisponible = false;
                Log::warning('solicitudes-cotizacion.escalas: ERP no disponible', ['exception' => $e->getMessage()]);
            }
        }

        return view('solicitudes-cotizacion.escalas', compact('escalas', 'vendedores', 'filtros', 'erpDisponible', 'verTodos', 'sinCodigoSgp'));
    }

    public function show(Request $request, string $nroSolicitud): View
    {
        $vendedorSgp = $this->vendedorSgpDelUsuario($request);

        // Sin código SGP no ve nada. La solicitud de otro vendedor responde 404 (no 403)
        // para no confirmarle a un comercial que existe.
        abort_if($vendedorSgp === false, 404);

        try {
            $solicitud = $this->repo->buscarSolicitud($nroSolicitud, $vendedorSgp);
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

    /**
     * Admin/gerente ven todas las solicitudes (null = sin restricción, pueden filtrar
     * por comercial). Cualquier otro rol queda amarrado a su propio código de SGP;
     * sin código asignado devuelve `false` — no se le muestra nada en vez de todo.
     */
    private function vendedorSgpDelUsuario(Request $request): string|false|null
    {
        $user = $request->user();

        if ($user->hasAnyRole(['admin', 'gerente'])) {
            return null;
        }

        return $user->vendedor_sgp ?: false;
    }

    private function filtrosDesde(Request $request, string|false|null $vendedorSgp): array
    {
        $filtros = $request->only(['vendedor', 'situacion_cliente', 'situacion_escala', 'buscar']);
        $filtros['mes_actual'] = $request->boolean('mes_actual', true);

        // Se pisa lo que venga en el query string: un comercial no puede pedir a otro.
        if (is_string($vendedorSgp)) {
            $filtros['vendedor'] = $vendedorSgp;
        }

        return $filtros;
    }
}
