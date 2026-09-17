<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web;

use App\Domain\Dashboard\Models\VendedorEquivalencia;
use App\Domain\Dashboard\Repositories\VendedorEquivalenciaRepositoryInterface;
use App\Domain\Dashboard\Services\ReemplazoTemporalService;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;

class MapeoVendedorWebController extends Controller
{
    public function __construct(
        private readonly VendedorEquivalenciaRepositoryInterface $repo,
        private readonly ReemplazoTemporalService $reemplazoService,
    ) {}

    public function index(Request $request): View
    {
        $compania           = in_array((int) $request->input('cia'), [1, 2]) ? (int) $request->input('cia') : 1;
        $mapeos             = $this->repo->todos($compania);
        $todosMapadosIds    = VendedorEquivalencia::pluck('asesor_id')->unique()->toArray();
        $asesoresSinMapear  = User::role('comercial')
                                ->whereNotIn('id', $todosMapadosIds)
                                ->orderBy('name')
                                ->get(['id', 'name', 'email']);
        // Incluye a asesores que ya tienen mapeo: un asesor puede necesitar un segundo
        // código en la misma compañía (ej. cubre a un compañero de vacaciones).
        $todosLosAsesores   = User::role('comercial')
                                ->orderBy('name')
                                ->get(['id', 'name', 'email']);
        $vendedoresSiesa1   = $this->repo->vendedoresSiesa(1);
        $vendedoresSiesa2   = $this->repo->vendedoresSiesa(2);

        return view('mapeo-vendedores.index', compact(
            'mapeos', 'asesoresSinMapear', 'todosLosAsesores', 'vendedoresSiesa1', 'vendedoresSiesa2', 'compania'
        ));
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'asesor_id' => ['required', 'exists:users,id'],
            'compania'  => ['required', 'integer', 'in:0,1,2'],
        ]);

        $asesorId    = (int) $request->input('asesor_id');
        $compania    = (int) $request->input('compania');
        $esReemplazo = $request->boolean('es_reemplazo');
        $creados     = 0;
        $movidos     = 0;
        $errores     = [];

        $companias = $compania === 0 ? [1, 2] : [$compania];

        foreach ($companias as $cia) {
            $cod    = trim($request->input("cod_vendedor_siesa_{$cia}", ''));
            $nombre = trim($request->input("nombre_vendedor_{$cia}", ''));

            if ($cod === '' || $nombre === '') {
                continue;
            }

            // Un reemplazo usa a propósito el mismo código que su titular, así que
            // no aplica la validación de "código ya mapeado a otro asesor".
            if (! $esReemplazo && $this->repo->existeCodigo($cod, $cia)) {
                $errores[] = ($cia === 1 ? 'Formacol' : 'Contiflex') . ': este código ya está mapeado a otro asesor.';
                continue;
            }

            $mapeo = $this->repo->crear([
                'asesor_id'          => $asesorId,
                'compania'           => $cia,
                'cod_vendedor_siesa' => $cod,
                'nombre_vendedor'    => $nombre,
                'activo'             => true,
                'es_reemplazo'       => $esReemplazo,
            ]);
            $creados++;

            if ($esReemplazo) {
                $movidos += $this->reemplazoService->activar($mapeo);
            }
        }

        if (!empty($errores)) {
            return back()->withErrors(['asesor_id' => implode(' | ', $errores)])->withInput();
        }

        if ($request->boolean('desde_usuario')) {
            return redirect()->route('presupuestos.index')
                ->with('success', 'Vendedor mapeado. Ahora asígnale un presupuesto.');
        }

        $mensaje = $creados . ' mapeo(s) creado(s) correctamente.';
        if ($movidos > 0) {
            $mensaje .= " {$movidos} cliente(s) reasignado(s) al reemplazante.";
        }

        return redirect()->route('mapeo-vendedores.index', ['cia' => $compania === 0 ? 1 : $compania])
            ->with('success', $mensaje);
    }

    public function update(Request $request, VendedorEquivalencia $mapeoVendedor): RedirectResponse
    {
        Log::info('[MapeoVendedor] update() recibido', [
            'mapeo_id'  => $mapeoVendedor->id,
            'all_input' => $request->all(),
        ]);

        $data = $request->validate([
            'cod_vendedor_siesa' => ['required', 'string', 'max:20'],
            'nombre_vendedor'    => ['required', 'string', 'max:200'],
            'activo'             => ['boolean'],
            'es_reemplazo'       => ['boolean'],
        ]);

        $eraReemplazoActivo = $mapeoVendedor->es_reemplazo && $mapeoVendedor->activo;

        try {
            $mapeoVendedor = $this->repo->actualizar($mapeoVendedor, $data);
        } catch (\Throwable $e) {
            Log::error('[MapeoVendedor] error al actualizar', ['mapeo_id' => $mapeoVendedor->id, 'error' => $e->getMessage()]);
            return back()->withErrors(['general' => 'Error al guardar: ' . $e->getMessage()]);
        }

        $esReemplazoActivoAhora = $mapeoVendedor->es_reemplazo && $mapeoVendedor->activo;
        $mensaje = 'Mapeo actualizado correctamente.';

        if ($eraReemplazoActivo && ! $esReemplazoActivoAhora) {
            $resultado = $this->reemplazoService->revertir($mapeoVendedor);
            if ($resultado['clientes'] > 0) {
                $mensaje .= " {$resultado['clientes']} cliente(s) devuelto(s) al titular.";
            }
            if ($resultado['negocios'] > 0) {
                $mensaje .= " {$resultado['negocios']} negocio(s) devuelto(s) al titular.";
            }
        } elseif (! $eraReemplazoActivo && $esReemplazoActivoAhora) {
            $movidos = $this->reemplazoService->activar($mapeoVendedor);
            if ($movidos > 0) {
                $mensaje .= " {$movidos} cliente(s) reasignado(s).";
            }
        }

        return redirect()->route('mapeo-vendedores.index')
            ->with('success', $mensaje);
    }

    public function destroy(VendedorEquivalencia $mapeoVendedor): RedirectResponse
    {
        if ($mapeoVendedor->es_reemplazo && $mapeoVendedor->activo) {
            $this->reemplazoService->revertir($mapeoVendedor);
        }

        $this->repo->eliminar($mapeoVendedor);

        return redirect()->route('mapeo-vendedores.index')
            ->with('success', 'Mapeo eliminado.');
    }
}
