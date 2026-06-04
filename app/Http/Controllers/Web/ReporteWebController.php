<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web;

use App\Domain\Reportes\Repositories\ReporteRepositoryInterface;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ReporteWebController extends Controller
{
    public function __construct(
        private readonly ReporteRepositoryInterface $repo,
    ) {}

    public function index(Request $request): View
    {
        abort_unless(auth()->user()?->hasAnyRole(['admin', 'gerente']), 403);

        $tipo    = $request->get('tipo', 'forecast');
        $filtros = $request->only(['mes', 'anio', 'asesor_id', 'desde', 'hasta']);

        $datos = match ($tipo) {
            'prospectos'  => $this->repo->reporteProspectos($filtros),
            'negocios'    => $this->repo->reporteNegocios($filtros),
            'conversion'  => $this->repo->reporteConversion($filtros),
            default       => $this->repo->reporteForecast($filtros),
        };

        return view('reportes.index', compact('tipo', 'datos', 'filtros'));
    }
}
