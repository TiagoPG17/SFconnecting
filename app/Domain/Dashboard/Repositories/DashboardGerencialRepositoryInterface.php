<?php

declare(strict_types=1);

namespace App\Domain\Dashboard\Repositories;

use Illuminate\Support\Collection;

interface DashboardGerencialRepositoryInterface
{
    public function presupuestoPorAsesor(int $compania, int $anio): Collection;

    public function codsPorAsesor(int $compania): Collection;

    public function logradoVendedoresYtd(int $compania, array $meses): Collection;

    public function forecastPipelineAsesor(): Collection;

    public function cicloDeVenta(array $meses): Collection;

    public function motivosDePerdida(array $meses): Collection;

    public function retencionChurn(int $compania): Collection;

    public function actividadEquipo(array $meses): Collection;
}
