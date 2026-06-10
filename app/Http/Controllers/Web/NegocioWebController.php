<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web;

use App\Domain\Clientes\Repositories\ClienteRepositoryInterface;
use App\Domain\Maestros\Repositories\MaestroRepositoryInterface;
use App\Domain\Negocios\Models\Negocio;
use App\Domain\Negocios\Repositories\NegocioRepositoryInterface;
use App\Domain\Prospectos\Repositories\ProspectoRepositoryInterface;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\View\View;

class NegocioWebController extends Controller
{
    public function __construct(
        private readonly NegocioRepositoryInterface $repo,
        private readonly MaestroRepositoryInterface $maestros,
        private readonly ClienteRepositoryInterface $clientes,
        private readonly ProspectoRepositoryInterface $prospectos,
    ) {}

    public function index(Request $request): View
    {
        $this->authorize('viewAny', Negocio::class);

        $filtros = $request->only(['pipeline_estado_id', 'asesor_id', 'tipo_negocio_id', 'buscar']);

        if (auth()->user()->hasRole('comercial')) {
            $filtros['asesor_id'] = auth()->id();
        }

        $negocios = $this->repo->paginar($filtros);
        $estados  = $this->maestros->pipelineEstadosPorTipo('negocio');

        return view('negocios.index', compact('negocios', 'estados'));
    }

    public function show(Negocio $negocio): View
    {
        $this->authorize('view', $negocio);

        $negocio->load(['pipelineEstado', 'tipoNegocio', 'motivoPerdida', 'asesor', 'prospecto', 'cliente', 'auditoria.usuario']);

        return view('negocios.show', compact('negocio'));
    }

    public function create(): View
    {
        $this->authorize('create', Negocio::class);

        $estados    = $this->maestros->pipelineEstadosPorTipo('negocio');
        $tipos      = $this->maestros->porTipo('tipo_negocio');
        $clientes   = $this->clientes->buscar('', 200);
        $prospectos = $this->prospectos->paginar(['activo' => true], 200)->items();

        return view('negocios.create', compact('estados', 'tipos', 'clientes', 'prospectos'));
    }

    public function edit(Negocio $negocio): View
    {
        $this->authorize('update', $negocio);

        $estados  = $this->maestros->pipelineEstadosPorTipo('negocio');
        $tipos    = $this->maestros->porTipo('tipo_negocio');
        $motivos  = $this->maestros->porTipo('motivo_perdida');

        return view('negocios.edit', compact('negocio', 'estados', 'tipos', 'motivos'));
    }

    public function kanban(): View
    {
        $this->authorize('viewAny', Negocio::class);

        $asesorId = auth()->user()->hasRole('comercial') ? auth()->id() : null;
        $columnas = $this->repo->kanban($asesorId);

        return view('negocios.kanban', compact('columnas'));
    }

}
