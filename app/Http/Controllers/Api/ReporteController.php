<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Domain\Reportes\Repositories\ReporteRepositoryInterface;
use App\Http\Controllers\Controller;
use App\Http\Responses\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ReporteController extends Controller
{
    public function __construct(
        private readonly ReporteRepositoryInterface $repo,
    ) {}

    private function verificarAcceso(): void
    {
        abort_unless(
            request()->user()?->hasRole(['admin', 'gerente']),
            403,
            'Solo admin y gerente pueden acceder a reportes.'
        );
    }

    public function clientes(Request $request): JsonResponse
    {
        $this->verificarAcceso();
        return ApiResponse::success($this->repo->reporteClientes(
            $request->get('desde'),
            $request->get('hasta'),
        ));
    }

    public function seguimientos(Request $request): JsonResponse
    {
        $this->verificarAcceso();
        return ApiResponse::success($this->repo->reporteSeguimientos(
            $request->get('desde'),
            $request->get('hasta'),
        ));
    }

    public function prospectos(Request $request): JsonResponse
    {
        $this->verificarAcceso();
        return ApiResponse::success(
            $this->repo->reporteProspectos($request->only(['asesor_id', 'desde', 'hasta']))
        );
    }

    public function negocios(Request $request): JsonResponse
    {
        $this->verificarAcceso();
        return ApiResponse::success(
            $this->repo->reporteNegocios($request->only(['asesor_id', 'desde', 'hasta']))
        );
    }

    public function forecast(Request $request): JsonResponse
    {
        $this->verificarAcceso();
        return ApiResponse::success(
            $this->repo->reporteForecast($request->only(['asesor_id', 'mes', 'anio']))
        );
    }

    public function conversion(Request $request): JsonResponse
    {
        $this->verificarAcceso();
        return ApiResponse::success(
            $this->repo->reporteConversion($request->only(['asesor_id', 'desde', 'hasta']))
        );
    }
}
