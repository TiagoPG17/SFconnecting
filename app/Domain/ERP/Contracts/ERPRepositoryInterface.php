<?php

declare(strict_types=1);

namespace App\Domain\ERP\Contracts;

interface ERPRepositoryInterface
{
    public function clientePorNit(string $nit): ?array;

    public function clientesPorNombre(string $nombre, int $limite = 20): array;

    public function documentosPorCliente(string $nit, int $limite = 50): array;

    public function saldoPorCliente(string $nit): ?array;

    public function clientesAtencionInmediata(int $limite = 20): array;

    public function clientesRescate(int $limite = 20): array;

    public function panoramaGerencial(): array;

    public function clientesIntegrales(int $limite = 30): array;

    public function clientesEnFuga(int $limite = 50): array;

    public function clientesExpansion(int $limite = 50): array;

    public function clientesPresupuestoActivo(int $limite = 30): array;

    public function clientesPresupuestoEnRiesgo(int $limite = 30): array;

    public function clientesPresupuestoRecuperar(int $limite = 30): array;

    public function clientesLargoPlazo(int $limite = 30): array;

    public function panoramaPresupuestal(): array;

    public function isAvailable(): bool;
}
