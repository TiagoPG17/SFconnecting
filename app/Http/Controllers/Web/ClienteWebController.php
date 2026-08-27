<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web;

use App\Domain\Clientes\DTOs\CrearClienteDTO;
use App\Domain\Clientes\Models\Cliente;
use App\Domain\Clientes\Repositories\ClienteRepositoryInterface;
use App\Domain\Clientes\Services\ClienteService;
use App\Domain\Dashboard\Models\VendedorEquivalencia;
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

    public function index(Request $request): View|JsonResponse
    {
        $this->authorize('viewAny', Cliente::class);

        $user = auth()->user();

        if ($user->hasRole('comercial')) {
            return $this->indexComercial($request, $user->id);
        }

        $filtros  = $request->only(['estado', 'buscar']);
        $clientes = $this->repo->paginar($filtros);

        if ($request->wantsJson()) {
            return response()->json($this->clientesAJson($clientes));
        }

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
        $comparativoAnual = $this->consultarComparativoAnual($cliente->nit);
        $facturas       = $this->consultarFacturas($cliente->nit);

        return view('clientes.show', compact('cliente', 'seguimientos', 'actividad', 'datosErp', 'actividadSiesa', 'comparativoAnual', 'facturas'));
    }

    public function showErp(Request $request, string $nit): View
    {
        $datosErp = $this->consultarErp($nit);

        abort_if($datosErp === null, 404);

        $user     = auth()->user();
        $compania = $this->vendedorRepo->companiaPrincipal($user->id);
        $cliente  = $this->repo->buscarPorNit($nit, $compania);

        if ($cliente === null && $user->hasRole('comercial')) {
            $dto     = CrearClienteDTO::fromArray([
                'razon_social' => $datosErp['RAZON_SOCIAL'] ?? $nit,
                'nit'          => $nit,
                'user_id'      => $user->id,
                'compania'     => $compania,
                'ciudad'       => $datosErp['CIUDAD'] ?? null,
                'estado'       => 'activo',
            ]);
            $cliente = $this->repo->crear($dto);
        }

        $seguimientos     = collect();
        $actividad        = ['mensual' => [], 'trimestral' => [], 'anual' => []];
        $actividadSiesa   = $this->consultarVentasSiesa($nit);
        $comparativoAnual = $this->consultarComparativoAnual($nit);

        if ($cliente !== null) {
            $this->authorize('view', $cliente);
            $cliente->load(['asesor', 'contactos', 'seguimientos.asesor', 'seguimientos.contacto']);
            $seguimientos = $this->seguimientoRepo->timelineCliente($cliente->id, 10);
            $actividad    = $this->calcularActividad($cliente->seguimientos);
        }

        $facturas = $this->consultarFacturas($nit);

        return view('clientes.show', compact('cliente', 'seguimientos', 'actividad', 'datosErp', 'actividadSiesa', 'comparativoAnual', 'facturas'));
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
            $compania  = $this->vendedorRepo->companiaPrincipal($user->id);
            $resultado = $this->service->sincronizarCarteraDesdeErp($nombreVendedor, $user->id, $compania);

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

    private function indexComercial(Request $request, int $userId): View|JsonResponse
    {
        $nombreVendedor = $this->vendedorRepo->nombreVendedorSiesa($userId);

        $filtros  = array_merge($request->only(['buscar']), ['user_id' => $userId]);
        $clientes = $this->repo->paginar($filtros);

        if ($request->wantsJson()) {
            return response()->json($this->clientesAJson($clientes));
        }

        return view('clientes.index', [
            'clientes'      => $clientes,
            'esModoErp'     => false,
            'esComercial'   => true,
            'sinMapeoErp'   => $nombreVendedor === null,
        ]);
    }

    private function clientesAJson(\Illuminate\Pagination\LengthAwarePaginator $paginador): array
    {
        return [
            'items' => $paginador->map(fn ($c) => [
                'id'          => $c->id,
                'razon_social'=> $c->razon_social,
                'nit'         => $c->nit,
                'compania'    => $c->compania,
                'email'       => $c->email ?? '',
                'telefono'    => $c->telefono ?? '',
                'ciudad'      => $c->ciudad ?? '',
                'estado'      => $c->estado,
                'asesor'      => $c->asesor?->name ?? '—',
                'url_show'    => route('clientes.show', $c),
                'url_edit'    => route('clientes.edit', $c),
            ])->values()->toArray(),
            'meta' => [
                'total'        => $paginador->total(),
                'current_page' => $paginador->currentPage(),
                'last_page'    => $paginador->lastPage(),
                'per_page'     => $paginador->perPage(),
            ],
        ];
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

    private function consultarComparativoAnual(string $nit): array
    {
        try {
            return $this->erp->isAvailable() ? $this->erp->comparativoAnualPorNit($nit) : [];
        } catch (\Throwable) {
            return [];
        }
    }

    private function consultarFacturas(string $nit): array
    {
        if (! $this->erp->isAvailable()) {
            return [];
        }

        $facturas = $this->enriquecerNombreVendedor($this->erp->facturasPorNit($nit));

        return $this->filtrarFacturasPorCompaniaDelComercial($facturas);
    }

    /**
     * Un comercial solo debe ver facturas de la(s) compañía(s) en las que tiene
     * mapeo activo en sf_vendedor_equivalencia (puede tener Formacol, Contiflex o ambas
     * si le ha vendido al cliente desde las dos). Admin/gerente ven todo sin filtrar.
     */
    private function filtrarFacturasPorCompaniaDelComercial(array $facturas): array
    {
        $user = auth()->user();

        if (empty($facturas) || ! $user->hasRole('comercial')) {
            return $facturas;
        }

        $companias = $this->vendedorRepo->companiasDelAsesor($user->id);

        return array_values(array_filter(
            $facturas,
            fn (array $factura) => in_array((int) ($factura['COMPANIA'] ?? 0), $companias, true)
        ));
    }

    private function enriquecerNombreVendedor(array $facturas): array
    {
        if (empty($facturas)) {
            return $facturas;
        }

        $companias = collect($facturas)->pluck('COMPANIA')->filter()->unique()->values()->all();

        $mapa = VendedorEquivalencia::whereIn('compania', $companias)
            ->get(['compania', 'cod_vendedor_siesa', 'nombre_vendedor'])
            ->mapWithKeys(fn (VendedorEquivalencia $v) => [
                $v->compania.'|'.trim((string) $v->cod_vendedor_siesa) => $v->nombre_vendedor,
            ]);

        return array_map(function (array $factura) use ($mapa) {
            $clave = ($factura['COMPANIA'] ?? '').'|'.trim((string) ($factura['COD_VENDEDOR'] ?? ''));
            $factura['NOMBRE_VENDEDOR'] = $mapa->get($clave);

            return $factura;
        }, $facturas);
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
