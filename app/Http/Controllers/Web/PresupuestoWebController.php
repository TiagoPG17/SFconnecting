<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web;

use App\Domain\Dashboard\Models\Presupuesto;
use App\Domain\Dashboard\Repositories\PresupuestoRepositoryInterface;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PresupuestoWebController extends Controller
{
    public function __construct(
        private readonly PresupuestoRepositoryInterface $repo,
    ) {}

    public function index(Request $request): View
    {
        $anio        = (int) $request->input('anio', now()->year);
        $compania    = (int) config('crm.compania', 1);
        $presupuestos = $this->repo->todos($anio);
        $asesores    = User::role('comercial')->orderBy('name')->get(['id', 'name']);
        $anios       = range(now()->year + 1, now()->year - 3);

        return view('presupuestos.index', compact('presupuestos', 'asesores', 'anio', 'anios', 'compania'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'asesor_id'   => ['required', 'exists:users,id'],
            'anio'        => ['required', 'integer', 'min:2020', 'max:2030'],
            'presupuesto' => ['required', 'numeric', 'min:0'],
        ]);

        $compania = (int) config('crm.compania', 1);

        if ($this->repo->existe((int) $data['asesor_id'], $compania, (int) $data['anio'])) {
            return back()->withErrors(['asesor_id' => 'Ya existe un presupuesto para ese asesor en ese año.'])->withInput();
        }

        $this->repo->crear([
            'asesor_id'   => $data['asesor_id'],
            'compania'    => $compania,
            'anio'        => $data['anio'],
            'presupuesto' => $data['presupuesto'],
        ]);

        return redirect()->route('presupuestos.index', ['anio' => $data['anio']])
            ->with('success', 'Presupuesto creado correctamente.');
    }

    public function update(Request $request, Presupuesto $presupuesto): RedirectResponse
    {
        $data = $request->validate([
            'presupuesto' => ['required', 'numeric', 'min:0'],
        ]);

        $this->repo->actualizar($presupuesto, ['presupuesto' => $data['presupuesto']]);

        return redirect()->route('presupuestos.index', ['anio' => $presupuesto->anio])
            ->with('success', 'Presupuesto actualizado correctamente.');
    }

    public function destroy(Presupuesto $presupuesto): RedirectResponse
    {
        $anio = $presupuesto->anio;
        $this->repo->eliminar($presupuesto);

        return redirect()->route('presupuestos.index', ['anio' => $anio])
            ->with('success', 'Presupuesto eliminado.');
    }
}
