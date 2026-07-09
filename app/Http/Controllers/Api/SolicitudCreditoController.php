<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Domain\SolicitudesCredito\DTOs\CrearSolicitudCreditoDTO;
use App\Domain\SolicitudesCredito\DTOs\DecidirSolicitudCreditoDTO;
use App\Domain\SolicitudesCredito\Exceptions\SolicitudCreditoException;
use App\Domain\SolicitudesCredito\Models\SolicitudCredito;
use App\Domain\SolicitudesCredito\Repositories\SolicitudCreditoRepositoryInterface;
use App\Domain\SolicitudesCredito\Services\SolicitudCreditoService;
use App\Http\Controllers\Controller;
use App\Http\Requests\SolicitudesCredito\CrearSolicitudCreditoRequest;
use App\Http\Requests\SolicitudesCredito\DecidirSolicitudCreditoRequest;
use App\Http\Resources\SolicitudesCredito\SolicitudCreditoResource;
use App\Http\Responses\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SolicitudCreditoController extends Controller
{
    public function __construct(
        private readonly SolicitudCreditoService $service,
        private readonly SolicitudCreditoRepositoryInterface $repo,
    ) {}

    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', SolicitudCredito::class);

        $filtros = $request->only(['pipeline_estado_id', 'cliente_id', 'buscar']);

        if ($request->user()->hasRole('comercial') && ! $request->user()->hasAnyRole(['admin', 'gerente'])) {
            $filtros['asesor_id'] = $request->user()->id;
        } else {
            $filtros['asesor_id'] = $request->get('asesor_id');
        }

        $resultado = $this->repo->paginar($filtros, (int) $request->get('per_page', 15));

        return ApiResponse::success(
            SolicitudCreditoResource::collection($resultado)->response()->getData(true)
        );
    }

    public function store(CrearSolicitudCreditoRequest $request): JsonResponse
    {
        try {
            $dto = CrearSolicitudCreditoDTO::fromArray(
                array_merge($request->validated(), ['asesor_id' => $request->user()->id])
            );
            $solicitud = $this->service->crear($dto);

            return ApiResponse::created(new SolicitudCreditoResource($solicitud), 'Solicitud de crédito radicada exitosamente.');
        } catch (SolicitudCreditoException $e) {
            return ApiResponse::error($e->getMessage(), [], 422);
        }
    }

    public function show(SolicitudCredito $solicitudCredito): JsonResponse
    {
        $this->authorize('view', $solicitudCredito);

        $solicitudCredito->load(['negocio', 'cliente', 'pipelineEstado', 'asesor', 'revisor', 'auditoria.usuario']);

        return ApiResponse::success(new SolicitudCreditoResource($solicitudCredito));
    }

    public function destroy(SolicitudCredito $solicitudCredito): JsonResponse
    {
        $this->authorize('delete', $solicitudCredito);

        $solicitudCredito->delete();

        return ApiResponse::success(message: 'Solicitud de crédito eliminada.');
    }

    public function decidir(DecidirSolicitudCreditoRequest $request, SolicitudCredito $solicitudCredito): JsonResponse
    {
        try {
            $dto = DecidirSolicitudCreditoDTO::fromArray(
                array_merge($request->validated(), ['revisado_por' => $request->user()->id])
            );
            $actualizada = $this->service->decidir($solicitudCredito, $dto);

            return ApiResponse::success(new SolicitudCreditoResource($actualizada), 'Solicitud de crédito actualizada.');
        } catch (SolicitudCreditoException $e) {
            return ApiResponse::error($e->getMessage(), [], 422);
        }
    }
}
