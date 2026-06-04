<?php

declare(strict_types=1);

namespace App\Domain\ERP\Fakes;

use App\Domain\ERP\Contracts\ERPRepositoryInterface;
use App\Domain\ERP\Exceptions\ERPConnectionException;

class FakeERPRepository implements ERPRepositoryInterface
{
    private bool $available = true;
    private bool $throwOnQuery = false;
    private array $clientes = [];
    private array $documentos = [];
    private array $saldos = [];

    public function clientePorNit(string $nit): ?array
    {
        $this->checkAvailability();

        return $this->clientes[$nit] ?? null;
    }

    public function clientesPorNombre(string $nombre, int $limite = 20): array
    {
        $this->checkAvailability();

        return array_filter(
            $this->clientes,
            fn(array $c) => str_contains(strtolower($c['nombre'] ?? ''), strtolower($nombre))
        );
    }

    public function documentosPorCliente(string $nit, int $limite = 50): array
    {
        $this->checkAvailability();

        return array_slice($this->documentos[$nit] ?? [], 0, $limite);
    }

    public function saldoPorCliente(string $nit): ?array
    {
        $this->checkAvailability();

        return $this->saldos[$nit] ?? null;
    }

    public function clientesAtencionInmediata(int $limite = 20): array
    {
        $this->checkAvailability();
        return [];
    }

    public function clientesRescate(int $limite = 20): array
    {
        $this->checkAvailability();
        return [];
    }

    public function panoramaGerencial(): array
    {
        $this->checkAvailability();
        return [];
    }

    public function clientesIntegrales(int $limite = 30): array
    {
        $this->checkAvailability();
        return [];
    }

    public function clientesEnFuga(int $limite = 50): array
    {
        $this->checkAvailability();
        return [];
    }

    public function clientesExpansion(int $limite = 50): array
    {
        $this->checkAvailability();
        return [];
    }

    public function clientesPresupuestoActivo(int $limite = 30): array
    {
        $this->checkAvailability();
        return [];
    }

    public function clientesPresupuestoEnRiesgo(int $limite = 30): array
    {
        $this->checkAvailability();
        return [];
    }

    public function clientesPresupuestoRecuperar(int $limite = 30): array
    {
        $this->checkAvailability();
        return [];
    }

    public function clientesLargoPlazo(int $limite = 30): array
    {
        $this->checkAvailability();
        return [];
    }

    public function panoramaPresupuestal(): array
    {
        $this->checkAvailability();
        return [];
    }

    public function isAvailable(): bool
    {
        return $this->available;
    }

    // — métodos de configuración para tests —

    public function simularDesconexion(): void
    {
        $this->available = false;
    }

    public function simularConexion(): void
    {
        $this->available = true;
    }

    public function simularErrorEnConsulta(): void
    {
        $this->throwOnQuery = true;
    }

    public function agregarCliente(string $nit, array $datos): void
    {
        $this->clientes[$nit] = array_merge(['nit' => $nit], $datos);
    }

    public function agregarDocumentos(string $nit, array $documentos): void
    {
        $this->documentos[$nit] = $documentos;
    }

    public function agregarSaldo(string $nit, array $saldo): void
    {
        $this->saldos[$nit] = $saldo;
    }

    private function checkAvailability(): void
    {
        if (! $this->available) {
            throw ERPConnectionException::unavailable();
        }

        if ($this->throwOnQuery) {
            throw ERPConnectionException::queryFailed('Error simulado en test');
        }
    }
}
