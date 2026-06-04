<?php

declare(strict_types=1);

namespace App\Domain\Reportes\Repositories;

interface ReporteRepositoryInterface
{
    public function reporteClientes(?string $desde = null, ?string $hasta = null): array;

    public function reporteSeguimientos(?string $desde = null, ?string $hasta = null): array;

    public function reporteProspectos(array $filtros = []): array;

    public function reporteNegocios(array $filtros = []): array;

    public function reporteForecast(array $filtros = []): array;

    public function reporteConversion(array $filtros = []): array;
}
