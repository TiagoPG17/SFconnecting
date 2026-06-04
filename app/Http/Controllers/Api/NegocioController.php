<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Domain\Negocios\DTOs\ActualizarNegocioDTO;
use App\Domain\Negocios\DTOs\CrearNegocioDTO;
use App\Domain\Negocios\Exceptions\NegocioException;
use App\Domain\Negocios\Models\Negocio;
use App\Domain\Negocios\Repositories\NegocioRepositoryInterface;
use App\Domain\Negocios\Services\NegocioService;
use App\Http\Controllers\Controller;
use App\Http\Requests\Negocios\ActualizarNegocioRequest;
use App\Http\Requests\Negocios\CrearNegocioRequest;
use App\Http\Resources\Negocios\NegocioResource;
use App\Http\Responses\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class NegocioController extends Controller
{
    public function __construct(
        private readonly NegocioService $service,
        private readonly NegocioRepositoryInterface $repo,
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

        $negocio->load(['pipelineEstado', 'tipoNegocio', 'motivoPerdida', 'asesor', 'prospecto', 'cliente']);

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
}
