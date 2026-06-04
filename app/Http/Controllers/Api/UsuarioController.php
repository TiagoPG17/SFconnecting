<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Usuarios\ActualizarUsuarioRequest;
use App\Http\Requests\Usuarios\CrearUsuarioRequest;
use App\Http\Resources\Usuarios\UsuarioResource;
use App\Http\Responses\ApiResponse;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UsuarioController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        abort_unless($request->user()?->can('usuarios.gestionar'), 403);
        $usuarios = User::with('roles')
            ->when($request->filled('buscar'), fn ($q) => $q->where(function ($q) use ($request) {
                $q->where('name', 'like', "%{$request->buscar}%")
                  ->orWhere('email', 'like', "%{$request->buscar}%");
            }))
            ->when($request->filled('rol'), fn ($q) => $q->role($request->rol))
            ->when($request->filled('activo'), fn ($q) => $q->where('activo', (bool) $request->activo))
            ->orderBy('name')
            ->paginate((int) $request->get('per_page', 15));

        return ApiResponse::success(
            UsuarioResource::collection($usuarios)->response()->getData(true)
        );
    }

    public function store(CrearUsuarioRequest $request): JsonResponse
    {
        $usuario = User::create([
            'name'     => $request->name,
            'email'    => $request->email,
            'password' => Hash::make($request->password),
            'activo'   => true,
        ]);

        $usuario->assignRole($request->rol);

        return ApiResponse::created(new UsuarioResource($usuario), 'Usuario creado exitosamente.');
    }

    public function show(Request $request, User $usuario): JsonResponse
    {
        abort_unless($request->user()?->can('usuarios.gestionar'), 403);
        $usuario->load('roles');

        return ApiResponse::success(new UsuarioResource($usuario));
    }

    public function update(ActualizarUsuarioRequest $request, User $usuario): JsonResponse
    {
        $datos = [
            'name'  => $request->name,
            'email' => $request->email,
        ];

        if ($request->filled('password')) {
            $datos['password'] = Hash::make($request->password);
        }

        $usuario->update($datos);
        $usuario->syncRoles([$request->rol]);

        return ApiResponse::success(new UsuarioResource($usuario->fresh('roles')), 'Usuario actualizado.');
    }

    public function toggle(Request $request, User $usuario): JsonResponse
    {
        abort_unless($request->user()?->can('usuarios.gestionar'), 403);
        if ($usuario->id === $request->user()->id) {
            return ApiResponse::error('No puedes desactivar tu propia cuenta.', [], 403);
        }

        $usuario->update(['activo' => ! $usuario->activo]);

        $accion = $usuario->activo ? 'activado' : 'desactivado';

        return ApiResponse::success(new UsuarioResource($usuario), "Usuario {$accion}.");
    }
}
