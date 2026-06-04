<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Domain\Clientes\DTOs\ActualizarContactoDTO;
use App\Domain\Clientes\DTOs\CrearContactoDTO;
use App\Domain\Clientes\Models\Cliente;
use App\Domain\Clientes\Models\Contacto;
use App\Domain\Clientes\Services\ContactoService;
use App\Http\Controllers\Controller;
use App\Http\Requests\Contactos\ActualizarContactoRequest;
use App\Http\Requests\Contactos\CrearContactoRequest;
use App\Http\Resources\Contactos\ContactoResource;
use App\Http\Responses\ApiResponse;
use Illuminate\Http\JsonResponse;

class ContactoController extends Controller
{
    public function __construct(
        private readonly ContactoService $service,
    ) {}

    public function index(Cliente $cliente): JsonResponse
    {
        $this->authorize('viewAny', [Contacto::class, $cliente]);

        $contactos = $this->service->porCliente($cliente->id);

        return ApiResponse::success(ContactoResource::collection($contactos)->resolve());
    }

    public function store(CrearContactoRequest $request, Cliente $cliente): JsonResponse
    {
        $dto = CrearContactoDTO::fromArray(
            array_merge($request->validated(), ['cliente_id' => $cliente->id])
        );

        $contacto = $this->service->crear($dto);

        return ApiResponse::created(new ContactoResource($contacto), 'Contacto registrado.');
    }

    public function show(Contacto $contacto): JsonResponse
    {
        $this->authorize('view', $contacto);

        $contacto->load('cliente');

        return ApiResponse::success(new ContactoResource($contacto));
    }

    public function update(ActualizarContactoRequest $request, Contacto $contacto): JsonResponse
    {
        $dto         = ActualizarContactoDTO::fromArray($request->validated());
        $actualizado = $this->service->actualizar($contacto, $dto);

        return ApiResponse::success(new ContactoResource($actualizado), 'Contacto actualizado.');
    }

    public function destroy(Contacto $contacto): JsonResponse
    {
        $this->authorize('delete', $contacto);

        $this->service->eliminar($contacto);

        return ApiResponse::success(message: 'Contacto eliminado.');
    }
}
