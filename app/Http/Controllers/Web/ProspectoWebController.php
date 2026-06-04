<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web;

use App\Domain\Maestros\Repositories\MaestroRepositoryInterface;
use App\Domain\Prospectos\Models\Prospecto;
use App\Domain\Prospectos\Repositories\ProspectoRepositoryInterface;
use App\Domain\Seguimientos\Repositories\SeguimientoRepositoryInterface;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProspectoWebController extends Controller
{
    public function __construct(
        private readonly ProspectoRepositoryInterface $repo,
        private readonly MaestroRepositoryInterface $maestros,
        private readonly SeguimientoRepositoryInterface $seguimientos,
    ) {}

    public function index(Request $request): View
    {
        $this->authorize('viewAny', Prospecto::class);

        $filtros = $request->only(['estado_pipeline_id', 'asesor_id', 'prioridad_id', 'buscar']);

        if (auth()->user()->hasRole('comercial')) {
            $filtros['asesor_id'] = auth()->id();
        }

        $prospectos = $this->repo->paginar($filtros);
        $estados    = $this->maestros->pipelineEstadosPorTipo('prospecto');

        return view('prospectos.index', compact('prospectos', 'estados'));
    }

    public function show(Prospecto $prospecto): View
    {
        $this->authorize('view', $prospecto);

        $prospecto->load(['estadoPipeline', 'origen', 'prioridad', 'asesor', 'clienteConvertido', 'auditoria.usuario']);
        $seguimientos = $this->seguimientos->porProspecto($prospecto->id);

        return view('prospectos.show', compact('prospecto', 'seguimientos'));
    }

    public function create(): View
    {
        $this->authorize('create', Prospecto::class);

        $estados    = $this->maestros->pipelineEstadosPorTipo('prospecto');
        $origenes   = $this->maestros->porTipo('fuente_lead');
        $prioridades = $this->maestros->porTipo('prioridad');

        return view('prospectos.create', compact('estados', 'origenes', 'prioridades'));
    }

    public function edit(Prospecto $prospecto): View
    {
        $this->authorize('update', $prospecto);

        $estados    = $this->maestros->pipelineEstadosPorTipo('prospecto');
        $origenes   = $this->maestros->porTipo('fuente_lead');
        $prioridades = $this->maestros->porTipo('prioridad');

        return view('prospectos.edit', compact('prospecto', 'estados', 'origenes', 'prioridades'));
    }

    public function kanban(): View
    {
        $this->authorize('viewAny', Prospecto::class);

        $columnas = $this->repo->kanbanPorTipo('prospecto');

        return view('prospectos.kanban', compact('columnas'));
    }
}
