<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Domain\Dashboard\Services\DashboardService;
use App\Http\Controllers\Controller;
use App\Http\Responses\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function __construct(
        private readonly DashboardService $service,
    ) {}

    public function kpis(Request $request): JsonResponse
    {
        $user     = $request->user();
        $esAsesor = $user->hasRole('comercial');

        $kpis = $this->service->kpis($user->id, $esAsesor);

        return ApiResponse::success($kpis);
    }
}
