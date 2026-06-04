<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Spatie\Permission\Models\Role;

class UsuarioWebController extends Controller
{
    public function index(Request $request): View
    {
        abort_unless(auth()->user()?->can('usuarios.gestionar'), 403);

        $filtros = $request->only(['buscar', 'rol', 'activo']);

        $usuarios = User::with('roles')
            ->when($request->filled('buscar'), fn ($q) => $q->where(function ($q) use ($request) {
                $q->where('name', 'like', "%{$request->buscar}%")
                  ->orWhere('email', 'like', "%{$request->buscar}%");
            }))
            ->when($request->filled('rol'), fn ($q) => $q->role($request->rol))
            ->when($request->filled('activo'), fn ($q) => $q->where('activo', $request->activo === '1'))
            ->orderBy('name')
            ->paginate(15)
            ->withQueryString();

        $roles = Role::orderBy('name')->pluck('name');

        return view('usuarios.index', compact('usuarios', 'filtros', 'roles'));
    }

    public function create(): View
    {
        abort_unless(auth()->user()?->can('usuarios.gestionar'), 403);

        $roles = Role::orderBy('name')->pluck('name');

        return view('usuarios.create', compact('roles'));
    }

    public function edit(User $usuario): View
    {
        abort_unless(auth()->user()?->can('usuarios.gestionar'), 403);

        $roles = Role::orderBy('name')->pluck('name');

        return view('usuarios.edit', compact('usuario', 'roles'));
    }
}
