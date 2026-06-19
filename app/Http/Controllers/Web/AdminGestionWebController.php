<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web;

use App\Domain\Auditoria\Models\ActividadLog;
use App\Domain\Negocios\Models\Negocio;
use App\Domain\Prospectos\Models\Prospecto;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdminGestionWebController extends Controller
{
    public function index(Request $request): View
    {
        $tab    = $request->query('tab', 'negocios');
        $buscar = $request->query('buscar', '');

        $negocios = Negocio::withTrashed()
            ->with(['asesor:id,name', 'pipelineEstado:id,nombre'])
            ->when($buscar, fn ($q) => $q->where('nombre_negocio', 'like', "%{$buscar}%"))
            ->orderByDesc('updated_at')
            ->paginate(25, ['*'], 'page')
            ->withQueryString();

        $prospectos = Prospecto::withTrashed()
            ->with(['asesor:id,name', 'estadoPipeline:id,nombre'])
            ->when($buscar, fn ($q) => $q->where('empresa', 'like', "%{$buscar}%"))
            ->orderByDesc('updated_at')
            ->paginate(25, ['*'], 'page')
            ->withQueryString();

        return view('admin.gestion', compact('negocios', 'prospectos', 'tab', 'buscar'));
    }

    public function toggleNegocio(Negocio $negocio): RedirectResponse
    {
        $negocio->update(['activo' => !$negocio->activo]);

        $estado = $negocio->activo ? 'activado' : 'desactivado';

        ActividadLog::registrar("toggle_{$estado}", 'negocios', "Negocio \"{$negocio->nombre_negocio}\" {$estado} por admin");

        return back()->with('success', "Negocio \"{$negocio->nombre_negocio}\" {$estado}.");
    }

    public function destroyNegocio(Negocio $negocio): RedirectResponse
    {
        $nombre = $negocio->nombre_negocio;

        ActividadLog::registrar('eliminar', 'negocios', "Negocio \"{$nombre}\" eliminado por admin");

        $negocio->delete();

        return back()->with('success', "Negocio \"{$nombre}\" eliminado.");
    }

    public function restoreNegocio(int $id): RedirectResponse
    {
        $negocio = Negocio::withTrashed()->findOrFail($id);
        $negocio->restore();

        ActividadLog::registrar('restaurar', 'negocios', "Negocio \"{$negocio->nombre_negocio}\" restaurado por admin");

        return back()->with('success', "Negocio \"{$negocio->nombre_negocio}\" restaurado.");
    }

    public function toggleProspecto(Prospecto $prospecto): RedirectResponse
    {
        $prospecto->update(['activo' => !$prospecto->activo]);

        $estado = $prospecto->activo ? 'activado' : 'desactivado';

        ActividadLog::registrar("toggle_{$estado}", 'prospectos', "Prospecto \"{$prospecto->empresa}\" {$estado} por admin");

        return back()->with('success', "Prospecto \"{$prospecto->empresa}\" {$estado}.");
    }

    public function destroyProspecto(Prospecto $prospecto): RedirectResponse
    {
        $empresa = $prospecto->empresa;

        ActividadLog::registrar('eliminar', 'prospectos', "Prospecto \"{$empresa}\" eliminado por admin");

        $prospecto->delete();

        return back()->with('success', "Prospecto \"{$empresa}\" eliminado.");
    }

    public function restoreProspecto(int $id): RedirectResponse
    {
        $prospecto = Prospecto::withTrashed()->findOrFail($id);
        $prospecto->restore();

        ActividadLog::registrar('restaurar', 'prospectos', "Prospecto \"{$prospecto->empresa}\" restaurado por admin");

        return back()->with('success', "Prospecto \"{$prospecto->empresa}\" restaurado.");
    }
}
