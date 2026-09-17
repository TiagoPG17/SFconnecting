<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Domain\Clientes\Models\Cliente;
use App\Domain\Prospectos\DTOs\ActualizarProspectoDTO;
use App\Domain\Prospectos\DTOs\ConvertirProspectoDTO;
use App\Domain\Prospectos\DTOs\CrearProspectoDTO;
use App\Domain\Prospectos\Exceptions\ConversionProspectoException;
use App\Domain\Prospectos\Exceptions\ProspectoDuplicadoException;
use App\Domain\Prospectos\Exceptions\ProspectoException;
use App\Domain\Prospectos\Models\Prospecto;
use App\Domain\Prospectos\Repositories\ProspectoRepositoryInterface;
use App\Domain\Prospectos\Services\ProspectoService;
use App\Domain\SolicitudesCotizacion\Repositories\SolicitudCotizacionRepositoryInterface;
use App\Http\Controllers\Controller;
use App\Http\Requests\Prospectos\ActualizarProspectoRequest;
use App\Http\Requests\Prospectos\ConvertirProspectoRequest;
use App\Http\Requests\Prospectos\CrearProspectoRequest;
use App\Http\Resources\Prospectos\ProspectoResource;
use App\Http\Responses\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Throwable;

class ProspectoController extends Controller
{
    public function __construct(
        private readonly ProspectoService $service,
        private readonly ProspectoRepositoryInterface $repo,
        private readonly SolicitudCotizacionRepositoryInterface $cotizaciones,
    ) {}

    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', Prospecto::class);

        $filtros = $request->only([
            'estado_pipeline_id', 'asesor_id', 'prioridad_id', 'origen_id',
            'buscar', 'fecha_desde', 'fecha_hasta',
        ]);

        $resultado = $this->repo->paginar($filtros, (int) $request->get('per_page', 15));

        return ApiResponse::success(
            ProspectoResource::collection($resultado)->response()->getData(true)
        );
    }

    public function store(CrearProspectoRequest $request): JsonResponse
    {
        try {
            $dto = CrearProspectoDTO::fromArray(
                array_merge($request->validated(), ['asesor_id' => $request->user()->id])
            );
            $prospecto = $this->service->crear($dto);

            return ApiResponse::created(new ProspectoResource($prospecto), 'Prospecto creado exitosamente.');
        } catch (ProspectoDuplicadoException $e) {
            return ApiResponse::error($e->getMessage(), [], 409);
        } catch (ProspectoException $e) {
            return ApiResponse::error($e->getMessage(), [], 422);
        }
    }

    public function show(Prospecto $prospecto): JsonResponse
    {
        $this->authorize('view', $prospecto);

        $prospecto->load(['estadoPipeline', 'origen', 'prioridad', 'asesor', 'clienteConvertido']);

        return ApiResponse::success(new ProspectoResource($prospecto));
    }

    public function update(ActualizarProspectoRequest $request, Prospecto $prospecto): JsonResponse
    {
        try {
            $dto         = ActualizarProspectoDTO::fromArray($request->validated());
            $actualizado = $this->service->actualizar($prospecto, $dto);

            return ApiResponse::success(new ProspectoResource($actualizado), 'Prospecto actualizado.');
        } catch (ProspectoDuplicadoException $e) {
            return ApiResponse::error($e->getMessage(), [], 409);
        }
    }

    public function destroy(Prospecto $prospecto): JsonResponse
    {
        $this->authorize('delete', $prospecto);

        $this->service->eliminar($prospecto);

        return ApiResponse::success(message: 'Prospecto eliminado.');
    }

    public function convertir(ConvertirProspectoRequest $request, Prospecto $prospecto): JsonResponse
    {
        try {
            $dto = ConvertirProspectoDTO::fromArray(
                array_merge($request->validated(), ['usuario_id' => $request->user()->id])
            );
            $convertido = $this->service->convertirEnCliente($prospecto, $dto);

            return ApiResponse::success(new ProspectoResource($convertido), 'Prospecto convertido en cliente.');
        } catch (ConversionProspectoException $e) {
            return ApiResponse::error($e->getMessage(), [], 422);
        }
    }

    public function kanban(Request $request): JsonResponse
    {
        $this->authorize('viewAny', Prospecto::class);

        $kanban = $this->repo->kanbanPorTipo('prospecto');

        return ApiResponse::success($kanban);
    }

    /**
     * Solicitudes de cotización de SGP sin cliente asignado, para el modal
     * "Cargar desde cotizaciones" de la pantalla de Prospectos. Excluye las que
     * ya se usaron para crear un prospecto (nro_solicitud_cotizacion) y las que
     * ya son cliente en SFconnecting (por NIT, aunque SGP no las haya cruzado
     * contra su propio maestro). Marca con posible_duplicado_prospecto las que
     * coinciden por nombre normalizado con un prospecto ya existente (Prospecto
     * no guarda NIT, así que aquí solo se avisa, no se excluye).
     */
    public function candidatosSgp(Request $request): JsonResponse
    {
        $this->authorize('create', Prospecto::class);

        try {
            $yaConvertidas = Prospecto::whereNotNull('nro_solicitud_cotizacion')
                ->pluck('nro_solicitud_cotizacion');

            $nitsClientes = Cliente::whereNotNull('nit')
                ->pluck('nit')
                ->map(fn ($nit) => trim((string) $nit))
                ->filter()
                ->flip();

            $prospectosPorNombre = Prospecto::pluck('empresa')
                ->map(fn ($empresa) => $this->normalizarNombre($empresa))
                ->filter()
                ->flip();

            $candidatos = $this->cotizaciones
                ->candidatosProspecto($request->query('buscar'))
                ->reject(fn ($s) => $yaConvertidas->contains((string) $s->nro_solicitud))
                ->reject(fn ($s) => $s->nit && $nitsClientes->has(trim((string) $s->nit)))
                ->map(function ($s) use ($prospectosPorNombre) {
                    $nombreCliente = $s->cliente ?: $s->cliente_digitado;

                    return [
                        'nro_solicitud'   => $s->nro_solicitud,
                        'fecha_solicitud' => $s->fecha_solicitud?->format('d/m/Y'),
                        'comercial'       => $s->nombre_vendedor ?: $s->vendedor,
                        'nit'             => $s->nit,
                        'cliente'         => $nombreCliente,
                        'tipo_cotizacion' => $s->tipo_cotizacion,
                        'escalas'         => $s->escalas,
                        'partes'          => $s->partes,
                        'posible_duplicado_prospecto' => $nombreCliente !== null
                            && $prospectosPorNombre->has($this->normalizarNombre($nombreCliente)),
                    ];
                })
                ->values();

            return ApiResponse::success($candidatos);
        } catch (Throwable $e) {
            Log::warning('prospectos.candidatos-sgp: ERP no disponible', ['exception' => $e->getMessage()]);

            return ApiResponse::error('Sin conexión al ERP. No se pueden cargar las solicitudes de cotización en este momento.', [], 503);
        }
    }

    /** Mayúsculas, sin tildes/puntos/espacios, SAS≈SA — mismo criterio que usa SGP para cruzar clientes. */
    private function normalizarNombre(?string $nombre): string
    {
        if (! $nombre) {
            return '';
        }

        $normalizado = Str::of($nombre)->ascii()->upper()->toString();
        $normalizado = preg_replace('/[^A-Z0-9 ]/', '', $normalizado);
        $normalizado = trim(preg_replace('/\bSAS\b/', 'SA', $normalizado));

        return str_replace(' ', '', $normalizado);
    }
}
