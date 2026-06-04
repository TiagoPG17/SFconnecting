<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web;

use App\Domain\Clientes\Repositories\ClienteRepositoryInterface;
use App\Domain\Seguimientos\Models\Seguimiento;
use App\Domain\Seguimientos\Repositories\SeguimientoRepositoryInterface;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SeguimientoWebController extends Controller
{
    public function __construct(
        private readonly SeguimientoRepositoryInterface $repo,
        private readonly ClienteRepositoryInterface $clienteRepo,
    ) {}

    public function index(Request $request): View
    {
        $this->authorize('viewAny', Seguimiento::class);

        $filtros = $request->only(['cliente_id', 'tipo', 'resultado', 'fecha_desde', 'fecha_hasta']);

        if (auth()->user()->hasRole('comercial')) {
            $filtros['user_id'] = auth()->id();
        }

        $seguimientos = $this->repo->paginar($filtros);

        // Para el filtro de cliente si viene preseleccionado
        $clienteSeleccionado = null;
        if (! empty($filtros['cliente_id'])) {
            $clienteSeleccionado = $this->clienteRepo->buscarPorId((int) $filtros['cliente_id']);
        }

        return view('seguimientos.index', compact('seguimientos', 'clienteSeleccionado'));
    }
}
