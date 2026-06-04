<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Domain\Seguimientos\DTOs\CrearSeguimientoDTO;
use App\Domain\Seguimientos\Exceptions\SeguimientoException;
use App\Domain\Seguimientos\Models\Seguimiento;
use App\Domain\Seguimientos\Repositories\SeguimientoRepositoryInterface;
use App\Domain\Seguimientos\Services\SeguimientoService;
use App\Http\Controllers\Controller;
use App\Http\Requests\Seguimientos\ActualizarSeguimientoRequest;
use App\Http\Requests\Seguimientos\CrearSeguimientoRequest;
use App\Http\Resources\Seguimientos\SeguimientoResource;
use App\Http\Responses\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SeguimientoController extends Controller
{
    public function __construct(
        private readonly SeguimientoService $service,
        private readonly SeguimientoRepositoryInterface $repo,
    ) {}

    public function index(Request $request): JsonResponse
    {
        if ($request->filled('prospecto_id')) {
            $seguimientos = $this->repo->porProspecto((int) $request->get('prospecto_id'));

            return ApiResponse::success(
                SeguimientoResource::collection($seguimientos)->response()->getData(true)
            );
        }

        $clienteId = (int) $request->get('cliente_id');
        $pagina    = $this->repo->paginarPorCliente($clienteId);

        return ApiResponse::success(
            SeguimientoResource::collection($pagina)->response()->getData(true)
        );
    }

    public function store(CrearSeguimientoRequest $request): JsonResponse
    {
        $this->authorize('create', Seguimiento::class);

        try {
            $dto = CrearSeguimientoDTO::fromArray(
                array_merge($request->validated(), ['user_id' => $request->user()->id])
            );
            $seguimiento = $this->service->crear($dto);

            return ApiResponse::created(new SeguimientoResource($seguimiento), 'Seguimiento registrado.');
        } catch (SeguimientoException $e) {
            return ApiResponse::error($e->getMessage(), [], 422);
        }
    }

    public function show(Seguimiento $seguimiento): JsonResponse
    {
        $this->authorize('view', $seguimiento);

        $seguimiento->load(['asesor', 'contacto']);

        return ApiResponse::success(new SeguimientoResource($seguimiento));
    }

    public function update(ActualizarSeguimientoRequest $request, Seguimiento $seguimiento): JsonResponse
    {
        try {
            $actualizado = $this->service->actualizar($seguimiento, $request->validated());

            return ApiResponse::success(new SeguimientoResource($actualizado), 'Seguimiento actualizado.');
        } catch (SeguimientoException $e) {
            return ApiResponse::error($e->getMessage(), [], 422);
        }
    }

    public function destroy(Seguimiento $seguimiento): JsonResponse
    {
        $this->authorize('delete', $seguimiento);

        $this->service->eliminar($seguimiento);

        return ApiResponse::success(message: 'Seguimiento eliminado.');
    }

    public function proximos(Request $request): JsonResponse
    {
        $proximos = $this->service->proximosPorAsesor(
            $request->user()->id,
            (int) $request->get('dias', 7)
        );

        return ApiResponse::success(SeguimientoResource::collection($proximos));
    }
}
