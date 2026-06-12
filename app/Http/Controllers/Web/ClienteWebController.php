<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web;

use App\Domain\Clientes\DTOs\CrearClienteDTO;
use App\Domain\Clientes\Models\Cliente;
use App\Domain\Clientes\Repositories\ClienteRepositoryInterface;
use App\Domain\Clientes\Services\ClienteService;
use App\Domain\Dashboard\Repositories\DashboardVendedorRepositoryInterface;
use App\Domain\ERP\Contracts\ERPRepositoryInterface;
use App\Domain\Seguimientos\Repositories\SeguimientoRepositoryInterface;
use App\Http\Controllers\Controller;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ClienteWebController extends Controller
{
    public function __construct(
        private readonly ClienteRepositoryInterface $repo,
        private readonly ClienteService $service,
        private readonly SeguimientoRepositoryInterface $seguimientoRepo,
        private readonly ERPRepositoryInterface $erp,
        private readonly DashboardVendedorRepositoryInterface $vendedorRepo,
    ) {}

    public function index(Request $request): View
    {
        $this->authorize('viewAny', Cliente::class);

        $user = auth()->user();

        if ($user->hasRole('comercial')) {
            return $this->indexComercial($request, $user->id);
        }

        $filtros  = $request->only(['estado', 'buscar']);
        $clientes = $this->repo->paginar($filtros);

        return view('clientes.index', ['clientes' => $clientes, 'esModoErp' => false]);
    }

    public function show(Cliente $cliente): View
    {
        $this->authorize('view', $cliente);

        $cliente->load(['asesor', 'contactos', 'seguimientos.asesor', 'seguimientos.contacto']);
        $seguimientos   = $this->seguimientoRepo->timelineCliente($cliente->id, 10);
        $actividad      = $this->calcularActividad($cliente->seguimientos);
        $datosErp       = $this->consultarErp($cliente->nit);
        $actividadSiesa = $this->consultarVentasSiesa($cliente->nit);
        $facturas       = $this->consultarFacturas($cliente->nit);

        return view('clientes.show', compact('cliente', 'seguimientos', 'actividad', 'datosErp', 'actividadSiesa', 'facturas'));
    }

    public function showErp(Request $request, string $nit): View
    {
        $datosErp = $this->consultarErp($nit);

        abort_if($datosErp === null, 404);

        $user    = auth()->user();
        $cliente = $this->repo->buscarPorNit($nit);

        if ($cliente === null && $user->hasRole('comercial')) {
            $dto     = CrearClienteDTO::fromArray([
                'razon_social' => $datosErp['RAZON_SOCIAL'] ?? $nit,
                'nit'          => $nit,
                'user_id'      => $user->id,
                'ciudad'       => $datosErp['CIUDAD'] ?? null,
                'estado'       => 'activo',
            ]);
            $cliente = $this->repo->crear($dto);
        }

        $seguimientos   = collect();
        $actividad      = ['mensual' => [], 'trimestral' => [], 'anual' => []];
        $actividadSiesa = $this->consultarVentasSiesa($nit);

        if ($cliente !== null) {
            $this->authorize('view', $cliente);
            $cliente->load(['asesor', 'contactos', 'seguimientos.asesor', 'seguimientos.contacto']);
            $seguimientos = $this->seguimientoRepo->timelineCliente($cliente->id, 10);
            $actividad    = $this->calcularActividad($cliente->seguimientos);
        }

        $facturas = $this->consultarFacturas($nit);

        return view('clientes.show', compact('cliente', 'seguimientos', 'actividad', 'datosErp', 'actividadSiesa', 'facturas'));
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

    public function sincronizarCartera(Request $request): JsonResponse
    {
        $user = auth()->user();

        if (! $user->hasRole('comercial')) {
            return response()->json(['success' => false, 'message' => 'Sin permiso.'], 403);
        }

        $nombreVendedor = $this->vendedorRepo->nombreVendedorSiesa($user->id);

        if ($nombreVendedor === null) {
            return response()->json([
                'success' => false,
                'message' => 'Tu usuario no tiene un vendedor SIESA asignado. Contacta al administrador.',
            ], 422);
        }

        try {
            $resultado = $this->service->sincronizarCarteraDesdeErp($nombreVendedor, $user->id);

            $creados     = $resultado['creados'];
            $actualizados = $resultado['actualizados'];
            $total        = $creados + $actualizados;

            return response()->json([
                'success'      => true,
                'message'      => "Cartera sincronizada: {$creados} " . ($creados === 1 ? 'cliente importado' : 'clientes importados') . ", {$actualizados} actualizados.",
                'creados'      => $creados,
                'actualizados' => $actualizados,
                'total'        => $total,
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'No se pudo conectar con SIESA. Intenta de nuevo más tarde.',
            ], 500);
        }
    }

    private function indexComercial(Request $request, int $userId): View
    {
        $nombreVendedor = $this->vendedorRepo->nombreVendedorSiesa($userId);

        $filtros  = array_merge($request->only(['buscar']), ['user_id' => $userId]);
        $clientes = $this->repo->paginar($filtros);

        return view('clientes.index', [
            'clientes'      => $clientes,
            'esModoErp'     => false,
            'esComercial'   => true,
            'sinMapeoErp'   => $nombreVendedor === null,
        ]);
    }

    private function consultarErp(string $nit): ?array
    {
        try {
            return $this->erp->isAvailable() ? $this->erp->clientePorNit($nit) : null;
        } catch (\Throwable) {
            return null;
        }
    }

    private function consultarVentasSiesa(string $nit): array
    {
        try {
            return $this->erp->isAvailable() ? $this->erp->ventasMensualesPorNit($nit) : [];
        } catch (\Throwable) {
            return [];
        }
    }

    private function consultarFacturas(string $nit): array
    {
        try {
            return $this->erp->isAvailable() ? $this->erp->facturasPorNit($nit) : [];
        } catch (\Throwable) {
            return [];
        }
    }

    private function calcularActividad(Collection $todos): array
    {
        $mensual = [];
        for ($i = 11; $i >= 0; $i--) {
            $fecha     = now()->subMonths($i);
            $clave     = $fecha->format('Y-m');
            $mensual[] = [
                'label' => ucfirst($fecha->locale('es')->isoFormat('MMM YY')),
                'total' => $todos->filter(
                    fn ($s) => $s->fecha_seguimiento->format('Y-m') === $clave
                )->count(),
            ];
        }

        $trimestral = [];
        for ($i = 7; $i >= 0; $i--) {
            $fecha        = now()->subQuarters($i);
            $year         = $fecha->year;
            $q            = (int) ceil($fecha->month / 3);
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
            $year    = now()->year - $i;
            $anual[] = [
                'label' => (string) $year,
                'total' => $todos->filter(
                    fn ($s) => $s->fecha_seguimiento->year === $year
                )->count(),
            ];
        }

        return compact('mensual', 'trimestral', 'anual');
    }
}
