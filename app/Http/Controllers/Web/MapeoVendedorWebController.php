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

    public function index(): View
    {
        $compania = (int) config('crm.compania', 1);
        $mapeos   = $this->repo->todos($compania);
        $asesores = User::role('comercial')
            ->whereDoesntHave('equivalencia', fn ($q) => $q->where('compania', $compania))
            ->orderBy('name')
            ->get(['id', 'name', 'email']);
        $vendedoresSiesa = $this->repo->vendedoresSiesa($compania);

        return view('mapeo-vendedores.index', compact('mapeos', 'asesores', 'vendedoresSiesa', 'compania'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data     = $request->validate([
            'asesor_id'          => ['required', 'exists:users,id'],
            'cod_vendedor_siesa' => ['required', 'string', 'max:20'],
            'nombre_vendedor'    => ['required', 'string', 'max:200'],
        ]);

        $compania = (int) config('crm.compania', 1);

        if ($this->repo->existe((int) $data['asesor_id'], $compania)) {
            return back()->withErrors(['asesor_id' => 'Este asesor ya tiene un mapeo activo.'])->withInput();
        }

        $this->repo->crear([
            'asesor_id'          => $data['asesor_id'],
            'compania'           => $compania,
            'cod_vendedor_siesa' => $data['cod_vendedor_siesa'],
            'nombre_vendedor'    => $data['nombre_vendedor'],
            'activo'             => true,
        ]);

        // Si viene de la creación de usuario, redirigir a presupuestos
        if ($request->boolean('desde_usuario')) {
            return redirect()->route('presupuestos.index')
                ->with('success', 'Vendedor mapeado. Ahora asígnale un presupuesto.');
        }

        return redirect()->route('mapeo-vendedores.index')
            ->with('success', 'Mapeo creado correctamente.');
    }

    public function update(Request $request, VendedorEquivalencia $mapeoVendedor): RedirectResponse
    {
        Log::info('[MapeoVendedor] update() recibido', [
            'mapeo_id'    => $mapeoVendedor->id,
            'all_input'   => $request->all(),
            'method'      => $request->method(),
        ]);

        $data = $request->validate([
            'cod_vendedor_siesa' => ['required', 'string', 'max:20'],
            'nombre_vendedor'    => ['required', 'string', 'max:200'],
            'activo'             => ['boolean'],
        ]);

        Log::info('[MapeoVendedor] validación OK, actualizando', [
            'mapeo_id' => $mapeoVendedor->id,
            'data'     => $data,
        ]);

        try {
            $this->repo->actualizar($mapeoVendedor, $data);
            Log::info('[MapeoVendedor] actualizado correctamente', ['mapeo_id' => $mapeoVendedor->id]);
        } catch (\Throwable $e) {
            Log::error('[MapeoVendedor] error al actualizar', [
                'mapeo_id' => $mapeoVendedor->id,
                'error'    => $e->getMessage(),
            ]);
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
