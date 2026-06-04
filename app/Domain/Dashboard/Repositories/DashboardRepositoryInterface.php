<?php

declare(strict_types=1);

namespace App\Domain\Dashboard\Repositories;

interface DashboardRepositoryInterface
{
    /** @return array{total: int, activos: int, inactivos: int, prospectos: int} */
    public function estadisticasClientes(?int $userId = null): array;

    public function totalSeguimientosUltimoDias(int $dias = 30, ?int $userId = null): int;

    public function totalProximosSeguimientos(int $dias = 7, ?int $userId = null): int;

    public function totalClientesSinSeguimientoReciente(int $dias = 30, ?int $userId = null): int;

    public function estadisticasPipeline(?int $userId = null): array;

    public function forecastResumen(?int $userId = null): array;

    public function negociosProximosCierre(int $dias = 30, ?int $userId = null): array;

    public function topAsesores(int $limite = 5): array;

    public function tasaConversion(?int $userId = null): array;

    public function agingNegocios(?int $userId = null): array;
}
