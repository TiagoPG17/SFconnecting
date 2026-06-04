<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web;

use App\Domain\Clientes\Models\Cliente;
use App\Domain\Clientes\Repositories\ClienteRepositoryInterface;
use App\Domain\Seguimientos\Repositories\SeguimientoRepositoryInterface;
use App\Http\Controllers\Controller;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ClienteWebController extends Controller
{
    public function __construct(
        private readonly ClienteRepositoryInterface $repo,
        private readonly SeguimientoRepositoryInterface $seguimientoRepo,
    ) {}

    public function index(Request $request): View
    {
        $this->authorize('viewAny', Cliente::class);

        $filtros = $request->only(['estado', 'buscar']);

        if (auth()->user()->hasRole('comercial')) {
            $filtros['user_id'] = auth()->id();
        }

        $clientes = $this->repo->paginar($filtros);

        return view('clientes.index', compact('clientes'));
    }

    public function show(Cliente $cliente): View
    {
        $this->authorize('view', $cliente);

        $cliente->load(['asesor', 'contactos', 'seguimientos.asesor', 'seguimientos.contacto']);
        $seguimientos = $this->seguimientoRepo->timelineCliente($cliente->id, 10);
        $actividad    = $this->calcularActividad($cliente->seguimientos);

        return view('clientes.show', compact('cliente', 'seguimientos', 'actividad'));
    }

    private function calcularActividad(Collection $todos): array
    {
        $mensual = [];
        for ($i = 11; $i >= 0; $i--) {
            $fecha = now()->subMonths($i);
            $clave = $fecha->format('Y-m');
            $mensual[] = [
                'label' => ucfirst($fecha->locale('es')->isoFormat('MMM YY')),
                'total' => $todos->filter(
                    fn ($s) => $s->fecha_seguimiento->format('Y-m') === $clave
                )->count(),
            ];
        }

        $trimestral = [];
        for ($i = 7; $i >= 0; $i--) {
            $fecha = now()->subQuarters($i);
            $year  = $fecha->year;
            $q     = (int) ceil($fecha->month / 3);
            $trimestral[] = [
                'label' => "Q{$q} {$year}",
                'total' => $todos->filter(
                    fn ($s) => $s->fecha_seguimiento->year === $year
                        && (int) ceil($s->fecha_seguimiento->month / 3) === $q
                )->count(),
            ];
        }

        $anual = [];
        for ($i = 4; $i >= 0; $i--) {
            $year  = now()->year - $i;
            $anual[] = [
                'label' => (string) $year,
                'total' => $todos->filter(
                    fn ($s) => $s->fecha_seguimiento->year === $year
                )->count(),
            ];
        }

        return compact('mensual', 'trimestral', 'anual');
    }

    public function create(): View
    {
        $this->authorize('create', Cliente::class);

        return view('clientes.create');
    }

    public function edit(Cliente $cliente): View
    {
        $this->authorize('update', $cliente);

        return view('clientes.edit', compact('cliente'));
    }
}
