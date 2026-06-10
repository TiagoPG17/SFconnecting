<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web;

use App\Domain\Auditoria\Services\AuditoriaService;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AuditoriaWebController extends Controller
{
    public function __construct(
        private readonly AuditoriaService $service,
    ) {}

    public function index(Request $request): View
    {
        abort_unless(auth()->user()?->hasRole('admin'), 403);

        $filtros = $request->only(['usuario_id', 'modulo', 'accion', 'desde', 'hasta']);

        $registros = $this->service->listar($filtros);
        $usuarios  = User::orderBy('name')->get(['id', 'name']);

        return view('auditoria.index', compact('registros', 'filtros', 'usuarios'));
    }
}
