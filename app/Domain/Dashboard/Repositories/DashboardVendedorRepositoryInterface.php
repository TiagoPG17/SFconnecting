<?php

declare(strict_types=1);

namespace App\Domain\Dashboard\Repositories;

use Illuminate\Support\Collection;

interface DashboardVendedorRepositoryInterface
{
    public function companiasDelAsesor(int $asesorId): array;

    public function nombreVendedorSiesa(int $asesorId): ?string;

    public function presupuestoVendedor(int $asesorId, int $compania, int $anio): ?object;

    public function codVendedorSiesa(int $asesorId, int $compania): ?string;

    public function logradoYtd(string $nombreVendedor, int $compania, array $meses): float;

    public function proximasActividades(int $asesorId, int $limit): Collection;

    public function etapasPipelinePersonal(int $asesorId): Collection;

    public function diasPromEtapa(int $estadoId, int $asesorId): float;

    public function clientesSinContacto30(int $asesorId): Collection;

    public function recenciaClientes(array $nits, int $compania): Collection;

    public function rankingVendedores(int $compania, array $meses): Collection;
}
