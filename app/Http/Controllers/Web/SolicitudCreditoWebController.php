<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web;

use App\Domain\Maestros\Repositories\MaestroRepositoryInterface;
use App\Domain\Negocios\Repositories\NegocioRepositoryInterface;
use App\Domain\SolicitudesCredito\Models\SolicitudCredito;
use App\Domain\SolicitudesCredito\Repositories\SolicitudCreditoRepositoryInterface;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SolicitudCreditoWebController extends Controller
{
    public function __construct(
        private readonly SolicitudCreditoRepositoryInterface $repo,
        private readonly MaestroRepositoryInterface $maestros,
        private readonly NegocioRepositoryInterface $negocios,
    ) {}

    public function index(Request $request): View
    {
        $this->authorize('viewAny', SolicitudCredito::class);

        $filtros = $request->only(['pipeline_estado_id', 'cliente_id', 'buscar']);

        if (auth()->user()->hasRole('comercial')) {
            $filtros['asesor_id'] = auth()->id();
        }

        $solicitudes = $this->repo->paginar($filtros);
        $estados     = $this->maestros->pipelineEstadosPorTipo('solicitud_credito');

        return view('solicitudes-credito.index', compact('solicitudes', 'estados'));
    }

    public function show(SolicitudCredito $solicitudCredito): View
    {
        $this->authorize('view', $solicitudCredito);

        $solicitudCredito->load(['negocio', 'cliente', 'pipelineEstado', 'asesor', 'revisor', 'auditoria.usuario']);

        return view('solicitudes-credito.show', ['solicitud' => $solicitudCredito]);
    }

    public function create(Request $request): View
    {
        $this->authorize('create', SolicitudCredito::class);

        $negocioId = $request->integer('negocio_id') ?: null;
        $negocio   = $negocioId ? $this->negocios->buscarPorId($negocioId) : null;

        return view('solicitudes-credito.create', compact('negocio'));
    }
}
