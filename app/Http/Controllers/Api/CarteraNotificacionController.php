<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Domain\ERP\Contracts\ERPRepositoryInterface;
use App\Domain\ERP\Exceptions\ERPConnectionException;
use App\Http\Controllers\Controller;
use App\Http\Responses\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

/**
 * Consumida por n8n (autenticada con VerifyN8nToken) en vez de conectarse
 * directo al SQL Server del ERP. Ver docs/n8n/gestion-cartera-notificacion.md.
 */
class CarteraNotificacionController extends Controller
{
    public function __construct(
        private readonly ERPRepositoryInterface $erp,
    ) {}

    public function pendientes(Request $request): JsonResponse
    {
        $compania          = (int) $request->query('compania', 0);
        $fechaCumplimiento = $request->query('fecha_cumplimiento');

        try {
            if (! $this->erp->isAvailable()) {
                return ApiResponse::serverError('ERP no disponible temporalmente.');
            }

            return ApiResponse::success(
                $this->erp->notificacionesCarteraPendientes($compania, $fechaCumplimiento)
            );
        } catch (ERPConnectionException $e) {
            Log::error('api.cartera.pendientes: fallo al consultar el ERP', ['exception' => $e->getMessage()]);

            return ApiResponse::serverError('ERP no disponible temporalmente.');
        }
    }

    public function notificar(int $compania, string $nroDocumento): JsonResponse
    {
        try {
            $resuelto = $this->erp->marcarNotificacionCarteraResuelta($compania, $nroDocumento);

            return ApiResponse::success(['resuelto' => $resuelto]);
        } catch (ERPConnectionException $e) {
            Log::error('api.cartera.notificar: fallo al marcar como notificado', [
                'compania'      => $compania,
                'nro_documento' => $nroDocumento,
                'exception'     => $e->getMessage(),
            ]);

            return ApiResponse::serverError('No se pudo marcar como notificado.');
        }
    }
}
