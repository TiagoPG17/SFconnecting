<?php

declare(strict_types=1);

namespace App\Domain\Dashboard\Repositories;

use Illuminate\Support\Collection;

interface DashboardGerencialRepositoryInterface
{
    public function presupuestoPorAsesor(int $compania, int $anio): Collection;

    public function codsPorAsesor(int $compania): Collection;

    public function logradoVendedoresYtd(int $compania, int $anio): Collection;

    public function forecastPipelineAsesor(): Collection;

    public function cicloDeVenta(int $anio): Collection;

    public function motivosDePerdida(int $anio): Collection;

    public function retencionChurn(int $compania): Collection;

    public function actividadEquipo(int $anio): Collection;
}
