<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web;

use App\Domain\Maestros\Repositories\MaestroRepositoryInterface;
use App\Domain\Pipeline\Models\PipelineEstado;
use App\Http\Controllers\Controller;
use Illuminate\View\View;

class MaestroWebController extends Controller
{
    public function __construct(
        private readonly MaestroRepositoryInterface $repo,
    ) {}

    public function index(): View
    {
        abort_unless(auth()->user()?->hasAnyRole(['admin', 'gerente']), 403);

        $puedeGestionar = auth()->user()?->can('maestros.gestionar') ?? false;

        $maestros = $puedeGestionar
            ? $this->repo->todos()->groupBy('tipo')
            : $this->repo->todosActivos()->groupBy('tipo');

        $pipeline = $puedeGestionar
            ? $this->repo->pipelineEstadosTodos()->groupBy('tipo')
            : PipelineEstado::activos()->ordenados()->get()->groupBy('tipo');

        return view('maestros.index', compact('maestros', 'pipeline', 'puedeGestionar'));
    }
}
