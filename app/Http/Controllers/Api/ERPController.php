<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Domain\ERP\Contracts\ERPRepositoryInterface;
use App\Domain\ERP\Exceptions\ERPConnectionException;
use App\Http\Controllers\Controller;
use App\Http\Responses\ApiResponse;
use Illuminate\Http\JsonResponse;

class ERPController extends Controller
{
    public function __construct(
        private readonly ERPRepositoryInterface $erp,
    ) {}

    public function buscarPorNit(string $nit): JsonResponse
    {
        try {
            $cliente = $this->erp->clientePorNit($nit);

            if ($cliente === null) {
                return ApiResponse::notFound("NIT {$nit} no encontrado en el ERP.");
            }

            return ApiResponse::success($cliente);
        } catch (ERPConnectionException $e) {
            return ApiResponse::serverError('ERP no disponible temporalmente.');
        }
    }

    public function disponible(): JsonResponse
    {
        return ApiResponse::success(['disponible' => $this->erp->isAvailable()]);
    }
}
