<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Domain\Clientes\DTOs\ActualizarClienteDTO;
use App\Domain\Clientes\DTOs\CrearClienteDTO;
use App\Domain\Clientes\Exceptions\ClienteDuplicadoException;
use App\Domain\Clientes\Models\Cliente;
use App\Domain\Clientes\Repositories\ClienteRepositoryInterface;
use App\Domain\Clientes\Services\ClienteService;
use App\Http\Controllers\Controller;
use App\Http\Requests\Clientes\ActualizarClienteRequest;
use App\Http\Requests\Clientes\CrearClienteRequest;
use App\Http\Resources\Clientes\ClienteResource;
use App\Http\Responses\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ClienteController extends Controller
{
    public function __construct(
        private readonly ClienteRepositoryInterface $repo,
        private readonly ClienteService $service,
    ) {}

    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', Cliente::class);

        $filtros  = $request->only(['estado', 'ciudad', 'buscar', 'user_id']);
        $clientes = $this->repo->paginar($filtros, (int) $request->get('per_page', 15));

        return ApiResponse::success(
            ClienteResource::collection($clientes)->response()->getData(true)
        );
    }

    public function store(CrearClienteRequest $request): JsonResponse
    {
        try {
            $dto     = CrearClienteDTO::fromArray(array_merge($request->validated(), ['user_id' => $request->user()->id]));
            $cliente = $this->service->crear($dto);

            return ApiResponse::created(new ClienteResource($cliente), 'Cliente creado exitosamente.');
        } catch (ClienteDuplicadoException $e) {
            return ApiResponse::error($e->getMessage(), [], 409);
        }
    }

    public function show(Cliente $cliente): JsonResponse
    {
        $this->authorize('view', $cliente);

        $cliente->loadCount('contactos')->load('asesor');

        return ApiResponse::success(new ClienteResource($cliente));
    }

    public function update(ActualizarClienteRequest $request, Cliente $cliente): JsonResponse
    {
        try {
            $dto         = ActualizarClienteDTO::fromArray($request->validated());
            $actualizado = $this->service->actualizar($cliente, $dto);

            return ApiResponse::success(new ClienteResource($actualizado), 'Cliente actualizado.');
        } catch (ClienteDuplicadoException $e) {
            return ApiResponse::error($e->getMessage(), [], 409);
        }
    }

    public function destroy(Cliente $cliente): JsonResponse
    {
        $this->authorize('delete', $cliente);

        $this->service->eliminar($cliente);

        return ApiResponse::success(message: 'Cliente eliminado.');
    }

    public function restore(int $id): JsonResponse
    {
        $cliente = Cliente::withTrashed()->findOrFail($id);
        $this->authorize('restore', $cliente);

        $restaurado = $this->service->restaurar($id);

        return ApiResponse::success(new ClienteResource($restaurado), 'Cliente restaurado.');
    }
}
