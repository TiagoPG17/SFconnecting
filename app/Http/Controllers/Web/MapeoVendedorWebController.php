<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web;

use App\Domain\Dashboard\Models\VendedorEquivalencia;
use App\Domain\Dashboard\Repositories\VendedorEquivalenciaRepositoryInterface;
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
    ) {}

    public function index(Request $request): View
    {
        $compania          = in_array((int) $request->input('cia'), [1, 2]) ? (int) $request->input('cia') : 1;
        $mapeos            = $this->repo->todos($compania);
        $todosMapadosIds   = VendedorEquivalencia::pluck('asesor_id')->unique()->toArray();
        $asesores          = User::role('comercial')
                                ->whereNotIn('id', $todosMapadosIds)
                                ->orderBy('name')
                                ->get(['id', 'name', 'email']);
        $vendedoresSiesa1  = $this->repo->vendedoresSiesa(1);
        $vendedoresSiesa2  = $this->repo->vendedoresSiesa(2);

        return view('mapeo-vendedores.index', compact(
            'mapeos', 'asesores', 'vendedoresSiesa1', 'vendedoresSiesa2', 'compania'
        ));
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'asesor_id' => ['required', 'exists:users,id'],
            'compania'  => ['required', 'integer', 'in:0,1,2'],
        ]);

        $asesorId = (int) $request->input('asesor_id');
        $compania = (int) $request->input('compania');
        $creados  = 0;
        $errores  = [];

        $companias = $compania === 0 ? [1, 2] : [$compania];

        foreach ($companias as $cia) {
            $cod    = trim($request->input("cod_vendedor_siesa_{$cia}", ''));
            $nombre = trim($request->input("nombre_vendedor_{$cia}", ''));

            if ($cod === '' || $nombre === '') {
                continue;
            }

            if ($this->repo->existe($asesorId, $cia)) {
                $errores[] = ($cia === 1 ? 'Formacol' : 'Contiflex') . ': ya existe un mapeo para este asesor.';
                continue;
            }

            $this->repo->crear([
                'asesor_id'          => $asesorId,
                'compania'           => $cia,
                'cod_vendedor_siesa' => $cod,
                'nombre_vendedor'    => $nombre,
                'activo'             => true,
            ]);
            $creados++;
        }

        if (!empty($errores)) {
            return back()->withErrors(['asesor_id' => implode(' | ', $errores)])->withInput();
        }

        if ($request->boolean('desde_usuario')) {
            return redirect()->route('presupuestos.index')
                ->with('success', 'Vendedor mapeado. Ahora asígnale un presupuesto.');
        }

        return redirect()->route('mapeo-vendedores.index', ['cia' => $compania === 0 ? 1 : $compania])
            ->with('success', $creados . ' mapeo(s) creado(s) correctamente.');
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
        ]);

        try {
            $this->repo->actualizar($mapeoVendedor, $data);
        } catch (\Throwable $e) {
            Log::error('[MapeoVendedor] error al actualizar', ['mapeo_id' => $mapeoVendedor->id, 'error' => $e->getMessage()]);
            return back()->withErrors(['general' => 'Error al guardar: ' . $e->getMessage()]);
        }

        return redirect()->route('mapeo-vendedores.index')
            ->with('success', 'Mapeo actualizado correctamente.');
    }

    public function destroy(VendedorEquivalencia $mapeoVendedor): RedirectResponse
    {
        $this->repo->eliminar($mapeoVendedor);

        return redirect()->route('mapeo-vendedores.index')
            ->with('success', 'Mapeo eliminado.');
    }
}
