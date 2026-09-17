<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Domain\Clientes\Models\Cliente;
use App\Domain\Negocios\DTOs\ActualizarNegocioDTO;
use App\Domain\Negocios\DTOs\CrearNegocioDTO;
use App\Domain\Negocios\Exceptions\NegocioException;
use App\Domain\Negocios\Models\Negocio;
use App\Domain\Negocios\Repositories\NegocioRepositoryInterface;
use App\Domain\Negocios\Services\NegocioService;
use App\Domain\SolicitudesCotizacion\Repositories\SolicitudCotizacionRepositoryInterface;
use App\Http\Controllers\Controller;
use App\Http\Requests\Negocios\ActualizarNegocioRequest;
use App\Http\Requests\Negocios\CrearNegocioRequest;
use App\Http\Resources\Negocios\NegocioResource;
use App\Http\Responses\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Throwable;

class NegocioController extends Controller
{
    public function __construct(
        private readonly NegocioService $service,
        private readonly NegocioRepositoryInterface $repo,
        private readonly SolicitudCotizacionRepositoryInterface $cotizaciones,
    ) {}

    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', Negocio::class);

        $filtros   = $request->only(['pipeline_estado_id', 'asesor_id', 'tipo_negocio_id', 'buscar', 'fecha_desde', 'fecha_hasta']);
        $resultado = $this->repo->paginar($filtros, (int) $request->get('per_page', 15));

        return ApiResponse::success(
            NegocioResource::collection($resultado)->response()->getData(true)
        );
    }

    public function store(CrearNegocioRequest $request): JsonResponse
    {
        try {
            $dto     = CrearNegocioDTO::fromArray(
                array_merge($request->validated(), ['asesor_id' => $request->user()->id])
            );
            $negocio = $this->service->crear($dto);

            return ApiResponse::created(new NegocioResource($negocio), 'Negocio creado exitosamente.');
        } catch (NegocioException $e) {
            return ApiResponse::error($e->getMessage(), [], 422);
        }
    }

    public function show(Negocio $negocio): JsonResponse
    {
        $this->authorize('view', $negocio);

        $negocio->load(['pipelineEstado', 'tipoNegocio', 'sector', 'motivoPerdida', 'asesor', 'prospecto', 'cliente']);

        return ApiResponse::success(new NegocioResource($negocio));
    }

    public function update(ActualizarNegocioRequest $request, Negocio $negocio): JsonResponse
    {
        try {
            $dto         = ActualizarNegocioDTO::fromArray($request->validated());
            $actualizado = $this->service->actualizar($negocio, $dto);

            return ApiResponse::success(new NegocioResource($actualizado), 'Negocio actualizado.');
        } catch (NegocioException $e) {
            return ApiResponse::error($e->getMessage(), [], 422);
        }
    }

    public function destroy(Negocio $negocio): JsonResponse
    {
        $this->authorize('delete', $negocio);

        $this->service->eliminar($negocio);

        return ApiResponse::success(message: 'Negocio eliminado.');
    }

    public function kanban(Request $request): JsonResponse
    {
        $this->authorize('viewAny', Negocio::class);

        return ApiResponse::success($this->repo->kanban());
    }

    public function forecast(Request $request): JsonResponse
    {
        $this->authorize('viewAny', Negocio::class);

        $filtros = $request->only(['asesor_id', 'mes', 'anio']);

        return ApiResponse::success($this->service->forecast($filtros));
    }

    /**
     * Solicitudes de cotización de SGP con cliente asignado, para el modal
     * "Cargar desde cotizaciones" de la pantalla de Negocios. Solo se listan las
     * que resuelven a un Cliente real (por NIT) en SFconnecting — si el NIT no
     * cruza localmente, no se ofrece aquí (le toca al flujo de Prospectos).
     * Excluye las que ya se usaron para crear un negocio (nro_solicitud_cotizacion)
     * y prellena valor_estimado con la suma de las escalas cotizadas.
     */
    public function candidatosSgp(Request $request): JsonResponse
    {
        $this->authorize('create', Negocio::class);

        try {
            $yaConvertidos = Negocio::whereNotNull('nro_solicitud_cotizacion')
                ->pluck('nro_solicitud_cotizacion');

            $candidatos = $this->cotizaciones
                ->candidatosNegocio($request->query('buscar'))
                ->reject(fn ($s) => $yaConvertidos->contains((string) $s->nro_solicitud));

            $nits = $candidatos->pluck('nit')->filter()->map(fn ($nit) => trim((string) $nit))->unique();

            $clientesPorNit = Cliente::whereIn('nit', $nits)
                ->pluck('id', 'nit');

            $candidatos = $candidatos->filter(
                fn ($s) => $s->nit && $clientesPorNit->has(trim((string) $s->nit))
            );

            $valoresTotales = $this->cotizaciones->valorTotalPorSolicitud(
                $candidatos->pluck('nro_solicitud')->all()
            );

            $resultado = $candidatos->map(fn ($s) => [
                'nro_solicitud'   => $s->nro_solicitud,
                'fecha_solicitud' => $s->fecha_solicitud?->format('d/m/Y'),
                'comercial'       => $s->nombre_vendedor ?: $s->vendedor,
                'nit'             => $s->nit,
                'cliente'         => $s->cliente,
                'cliente_id'      => $clientesPorNit->get(trim((string) $s->nit)),
                'tipo_cotizacion' => $s->tipo_cotizacion,
                'descripcion'     => $s->descripcion,
                'valor_estimado'  => (float) ($valoresTotales->get($s->nro_solicitud) ?? 0),
                'escalas'         => $s->escalas,
                'escalas_con_precio' => $s->escalas_con_precio,
                'partes'          => $s->partes,
            ])->values();

            return ApiResponse::success($resultado);
        } catch (Throwable $e) {
            Log::warning('negocios.candidatos-sgp: ERP no disponible', ['exception' => $e->getMessage()]);

            return ApiResponse::error('Sin conexión al ERP. No se pueden cargar las solicitudes de cotización en este momento.', [], 503);
        }
    }

    /**
     * Escalas activas de una solicitud puntual — se piden cuando el comercial elige
     * "Ver escalas" en el modal, para que escoja con cuál escala (cantidad/precio)
     * se crea el Negocio, en vez de asumir un valor agregado.
     */
    public function escalasSgp(string $nroSolicitud): JsonResponse
    {
        $this->authorize('create', Negocio::class);

        try {
            $escalas = $this->cotizaciones->escalasActivasDeSolicitud($nroSolicitud)
                ->map(fn ($e) => [
                    'escala'              => $e->escala,
                    'moneda'              => $e->moneda,
                    'precio_unitario'     => (float) $e->precio_unitario,
                    'precio_unitario_cop' => (float) $e->precio_unitario_cop,
                    'costo_unitario_cop'  => (float) $e->costo_unitario_cop,
                    'valor_total_escala'  => (float) $e->valor_total_escala,
                    'situacion_escala'    => $e->situacion_escala,
                ])
                ->values();

            return ApiResponse::success($escalas);
        } catch (Throwable $e) {
            Log::warning('negocios.candidatos-sgp.escalas: ERP no disponible', ['exception' => $e->getMessage()]);

            return ApiResponse::error('Sin conexión al ERP. No se pueden cargar las escalas en este momento.', [], 503);
        }
    }
}
