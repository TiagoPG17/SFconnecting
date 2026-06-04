<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Domain\Prospectos\DTOs\ActualizarProspectoDTO;
use App\Domain\Prospectos\DTOs\ConvertirProspectoDTO;
use App\Domain\Prospectos\DTOs\CrearProspectoDTO;
use App\Domain\Prospectos\Exceptions\ConversionProspectoException;
use App\Domain\Prospectos\Exceptions\ProspectoDuplicadoException;
use App\Domain\Prospectos\Models\Prospecto;
use App\Domain\Prospectos\Repositories\ProspectoRepositoryInterface;
use App\Domain\Prospectos\Services\ProspectoService;
use App\Http\Controllers\Controller;
use App\Http\Requests\Prospectos\ActualizarProspectoRequest;
use App\Http\Requests\Prospectos\ConvertirProspectoRequest;
use App\Http\Requests\Prospectos\CrearProspectoRequest;
use App\Http\Resources\Prospectos\ProspectoResource;
use App\Http\Responses\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProspectoController extends Controller
{
    public function __construct(
        private readonly ProspectoService $service,
        private readonly ProspectoRepositoryInterface $repo,
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
}
