<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web;

use App\Domain\Clientes\Repositories\ClienteRepositoryInterface;
use App\Domain\Dashboard\Models\VendedorEquivalencia;
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

        $estados           = $this->maestros->pipelineEstadosPorTipo('negocio');
        $tipos             = $this->maestros->porTipo('tipo_negocio');
        $motivos           = $this->maestros->porTipo('motivo_perdida');
        $estadosPerdidoIds = $estados->where('es_perdido', true)->pluck('id')->values()->toArray();

        $companiasAsesor = VendedorEquivalencia::where('asesor_id', auth()->id())
            ->where('activo', true)
            ->pluck('compania')
            ->unique()
            ->values()
            ->toArray();

        return view('negocios.create', compact('estados', 'tipos', 'motivos', 'estadosPerdidoIds', 'companiasAsesor'));
    }

    public function edit(Negocio $negocio): View
    {
        $this->authorize('update', $negocio);

        $estados           = $this->maestros->pipelineEstadosPorTipo('negocio');
        $tipos             = $this->maestros->porTipo('tipo_negocio');
        $motivos           = $this->maestros->porTipo('motivo_perdida');
        $estadosPerdidoIds = $estados->where('es_perdido', true)->pluck('id')->values()->toArray();

        return view('negocios.edit', compact('negocio', 'estados', 'tipos', 'motivos', 'estadosPerdidoIds'));
    }

    public function kanban(): View
    {
        $this->authorize('viewAny', Negocio::class);

        $asesorId = auth()->user()->hasRole('comercial') ? auth()->id() : null;
        $columnas = $this->repo->kanban($asesorId);
        $motivos  = $this->maestros->porTipo('motivo_perdida');

        return view('negocios.kanban', compact('columnas', 'motivos'));
    }

}
