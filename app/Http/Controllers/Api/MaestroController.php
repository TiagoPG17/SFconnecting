<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Domain\Maestros\Models\MaestroComercial;
use App\Domain\Maestros\Repositories\MaestroRepositoryInterface;
use App\Domain\Pipeline\Models\PipelineEstado;
use App\Http\Controllers\Controller;
use App\Http\Requests\Maestros\GestionarMaestroRequest;
use App\Http\Requests\Maestros\GestionarPipelineEstadoRequest;
use App\Http\Responses\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MaestroController extends Controller
{
    private const TIPOS_VALIDOS = [
        'tipo_negocio',
        'prioridad',
        'fuente_lead',
        'motivo_perdida',
        'tipo_actividad',
        'clasificacion',
        'segmento',
    ];

    public function __construct(
        private readonly MaestroRepositoryInterface $repo,
    ) {}

    public function porTipo(string $tipo): JsonResponse
    {
        if (! in_array($tipo, self::TIPOS_VALIDOS, true)) {
            return ApiResponse::error("Tipo '{$tipo}' no válido.", [], 404);
        }

        return ApiResponse::success($this->repo->porTipo($tipo));
    }

    public function todos(Request $request): JsonResponse
    {
        $maestros = $this->repo->todosActivos();

        return ApiResponse::success($maestros->groupBy('tipo'));
    }

    public function pipelineEstados(string $tipo): JsonResponse
    {
        if (! in_array($tipo, ['prospecto', 'negocio'], true)) {
            return ApiResponse::error("Tipo de pipeline '{$tipo}' no válido.", [], 404);
        }

        return ApiResponse::success($this->repo->pipelineEstadosPorTipo($tipo));
    }

    // ─── CRUD Maestros Comerciales ─────────────────────────────────────

    public function storeMaestro(GestionarMaestroRequest $request): JsonResponse
    {
        $maestro = $this->repo->crearMaestro($request->validated());

        return ApiResponse::created($maestro, 'Maestro creado exitosamente.');
    }

    public function updateMaestro(GestionarMaestroRequest $request, MaestroComercial $maestro): JsonResponse
    {
        $actualizado = $this->repo->actualizarMaestro($maestro, $request->validated());

        return ApiResponse::success($actualizado, 'Maestro actualizado.');
    }

    public function toggleMaestro(MaestroComercial $maestro): JsonResponse
    {
        abort_unless(request()->user()?->can('maestros.gestionar'), 403);

        $actualizado = $this->repo->toggleMaestro($maestro);
        $accion      = $actualizado->activo ? 'activado' : 'desactivado';

        return ApiResponse::success($actualizado, "Maestro {$accion}.");
    }

    // ─── CRUD Pipeline Estados ─────────────────────────────────────────

    public function storePipelineEstado(GestionarPipelineEstadoRequest $request): JsonResponse
    {
        $estado = $this->repo->crearPipelineEstado($request->validated());

        return ApiResponse::created($estado, 'Estado del pipeline creado exitosamente.');
    }

    public function updatePipelineEstado(GestionarPipelineEstadoRequest $request, PipelineEstado $estado): JsonResponse
    {
        $actualizado = $this->repo->actualizarPipelineEstado($estado, $request->validated());

        return ApiResponse::success($actualizado, 'Estado actualizado.');
    }

    public function togglePipelineEstado(PipelineEstado $estado): JsonResponse
    {
        abort_unless(request()->user()?->can('maestros.gestionar'), 403);

        $actualizado = $this->repo->togglePipelineEstado($estado);
        $accion      = $actualizado->activo ? 'activado' : 'desactivado';

        return ApiResponse::success($actualizado, "Estado {$accion}.");
    }
}
