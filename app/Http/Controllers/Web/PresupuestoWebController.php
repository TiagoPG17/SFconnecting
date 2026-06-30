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
        $anio     = (int) $request->input('anio', now()->year);
        $todos    = $this->repo->todos($anio);
        $asesores = User::role('comercial')->orderBy('name')->get(['id', 'name']);
        $anios    = range(now()->year + 1, now()->year - 3);

        $todos->load('meses');

        $porAsesor = $todos->groupBy('asesor_id')->map(fn ($rows) => [
            'asesor' => $rows->first()->asesor,
            'cia1'   => $rows->firstWhere('compania', 1),
            'cia2'   => $rows->firstWhere('compania', 2),
            'anio'   => $anio,
        ])->values();

        return view('presupuestos.index', compact('porAsesor', 'asesores', 'anio', 'anios'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'asesor_id'     => ['required', 'exists:users,id'],
            'anio'          => ['required', 'integer', 'min:2020', 'max:2030'],
            'compania'      => ['required', 'integer', 'in:0,1,2'],
            'presupuesto'   => ['required_if:compania,1,2', 'nullable', 'numeric', 'min:0'],
            'presupuesto_1' => ['required_if:compania,0', 'nullable', 'numeric', 'min:0'],
            'presupuesto_2' => ['required_if:compania,0', 'nullable', 'numeric', 'min:0'],
        ]);

        $asesorId = (int) $data['asesor_id'];
        $anio     = (int) $data['anio'];

        if ((int) $data['compania'] === 0) {
            $this->repo->upsert($asesorId, 1, $anio, (float) $data['presupuesto_1']);
            $this->repo->upsert($asesorId, 2, $anio, (float) $data['presupuesto_2']);
        } else {
            $this->repo->upsert($asesorId, (int) $data['compania'], $anio, (float) $data['presupuesto']);
        }

        return redirect()->route('presupuestos.index', ['anio' => $anio])
            ->with('success', 'Presupuesto guardado correctamente.');
    }

    public function update(Request $request, Presupuesto $presupuesto): RedirectResponse
    {
        $data = $request->validate([
            'asesor_id'     => ['required', 'exists:users,id'],
            'anio'          => ['required', 'integer'],
            'presupuesto_1' => ['nullable', 'numeric', 'min:0'],
            'presupuesto_2' => ['nullable', 'numeric', 'min:0'],
        ]);

        $asesorId = (int) $data['asesor_id'];
        $anio     = (int) $data['anio'];

        foreach ([1 => $data['presupuesto_1'], 2 => $data['presupuesto_2']] as $cia => $valor) {
            if ($valor !== null && $valor !== '') {
                $this->repo->upsert($asesorId, $cia, $anio, (float) $valor);
            } else {
                $existing = Presupuesto::where('asesor_id', $asesorId)
                    ->where('compania', $cia)
                    ->where('anio', $anio)
                    ->first();
                if ($existing) {
                    $this->repo->eliminar($existing);
                }
            }
        }

        return redirect()->route('presupuestos.index', ['anio' => $anio])
            ->with('success', 'Presupuesto actualizado correctamente.');
    }

    public function storeMeses(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'asesor_id'   => ['required', 'exists:users,id'],
            'anio'        => ['required', 'integer'],
            'cia1_id'     => ['nullable', 'integer', 'exists:sf_presupuesto,id'],
            'cia2_id'     => ['nullable', 'integer', 'exists:sf_presupuesto,id'],
            'cia1_mes'    => ['nullable', 'array', 'size:12'],
            'cia1_mes.*'  => ['nullable', 'numeric', 'min:0'],
            'cia2_mes'    => ['nullable', 'array', 'size:12'],
            'cia2_mes.*'  => ['nullable', 'numeric', 'min:0'],
        ]);

        $anio    = (int) $data['anio'];
        $nombres = [1 => 'Formacol', 2 => 'Contiflex'];

        foreach ([1 => 'cia1', 2 => 'cia2'] as $cia => $key) {
            $presupuestoId = !empty($data["{$key}_id"]) ? (int) $data["{$key}_id"] : null;
            $mesesData     = $data["{$key}_mes"] ?? null;

            if (!$presupuestoId || !$mesesData) {
                continue;
            }

            $presupuesto = $this->repo->buscarPorId($presupuestoId);
            if (!$presupuesto) {
                continue;
            }

            $suma  = (float) array_sum(array_map('floatval', $mesesData));
            $anual = (float) $presupuesto->presupuesto;

            if (abs($suma - $anual) > 1) {
                $sumaFmt  = number_format($suma, 0, ',', '.');
                $anualFmt = number_format($anual, 0, ',', '.');
                return back()
                    ->withErrors(["La suma mensual de {$nombres[$cia]} (\${$sumaFmt}) no coincide con el presupuesto anual (\${$anualFmt})."])
                    ->withInput();
            }

            $this->repo->guardarMeses($presupuestoId, $mesesData);
        }

        return redirect()->route('presupuestos.index', ['anio' => $anio])
            ->with('success', 'Distribución mensual guardada correctamente.');
    }

    public function destroy(Presupuesto $presupuesto): RedirectResponse
    {
        $anio = $presupuesto->anio;
        $this->repo->eliminar($presupuesto);

        return redirect()->route('presupuestos.index', ['anio' => $anio])
            ->with('success', 'Presupuesto eliminado.');
    }
}
