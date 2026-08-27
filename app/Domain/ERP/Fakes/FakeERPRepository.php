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
    private array $cartera = [];
    private array $clientesVendedor = [];
    private array $facturas = [];
    private array $existenciasMP = [];

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

    public function carteraPorNit(string $nit): array
    {
        $this->checkAvailability();

        return $this->cartera[$nit] ?? [];
    }

    public function clientesAtencionInmediata(int $limite = 20, ?string $filtroVendedor = null): array
    {
        $this->checkAvailability();
        return [];
    }

    public function clientesRescate(int $limite = 20, ?string $filtroVendedor = null): array
    {
        $this->checkAvailability();
        return [];
    }

    public function panoramaGerencial(int $compania = 0): array
    {
        $this->checkAvailability();
        return [];
    }

    public function clientesIntegrales(int $limite = 30): array
    {
        $this->checkAvailability();
        return [];
    }

    public function clientesEnFuga(int $limite = 50, ?string $filtroVendedor = null): array
    {
        $this->checkAvailability();
        return [];
    }

    public function clientesExpansion(int $limite = 50, ?string $filtroVendedor = null): array
    {
        $this->checkAvailability();
        return [];
    }

    public function clientesPresupuestoActivo(int $limite = 30, ?string $filtroVendedor = null): array
    {
        $this->checkAvailability();
        return [];
    }

    public function clientesPresupuestoEnRiesgo(int $limite = 30, ?string $filtroVendedor = null): array
    {
        $this->checkAvailability();
        return [];
    }

    public function clientesPresupuestoRecuperar(int $limite = 30, ?string $filtroVendedor = null): array
    {
        $this->checkAvailability();
        return [];
    }

    public function clientesLargoPlazo(int $limite = 30, ?string $filtroVendedor = null): array
    {
        $this->checkAvailability();
        return [];
    }

    public function panoramaPresupuestal(int $compania = 0): array
    {
        $this->checkAvailability();
        return [];
    }

    public function clientesHuerfanos(int $compania, array $nitsExcluir = [], int $porPagina = 50, int $offset = 0): array
    {
        $this->checkAvailability();
        return [];
    }

    public function clientesPorVendedorYHorizonte(
        string $vendedor,
        string $horizonte,
        int $compania = 0,
        int $limite = 100
    ): array {
        $this->checkAvailability();
        return [];
    }

    public function clientesPorVendedor(
        string $nombreVendedor,
        ?string $buscar = null,
        int $pagina = 1,
        int $porPagina = 20
    ): array {
        $this->checkAvailability();

        $todos = array_filter(
            $this->clientesVendedor[$nombreVendedor] ?? [],
            fn ($c) => !$buscar
                || str_contains(strtolower($c['RAZON_SOCIAL'] ?? ''), strtolower($buscar))
                || str_contains($c['NIT'] ?? '', $buscar)
        );

        $todos  = array_values($todos);
        $total  = count($todos);
        $data   = array_slice($todos, ($pagina - 1) * $porPagina, $porPagina);

        return compact('data', 'total', 'pagina', 'porPagina');
    }

    public function todosClientesPorVendedor(string $nombreVendedor): array
    {
        $this->checkAvailability();

        return array_values($this->clientesVendedor[$nombreVendedor] ?? []);
    }

    public function ventasMensualesPorNit(string $nit): array
    {
        $this->checkAvailability();

        return ['mensual' => [], 'trimestral' => [], 'anual' => []];
    }

    public function comparativoAnualPorNit(string $nit, int $cantidadAnios = 3): array
    {
        $this->checkAvailability();

        return ['anios' => [], 'mensual' => [], 'trimestral' => [], 'anual' => [], 'totales' => []];
    }

    public function facturasPorNit(string $nit, int $limite = 20): array
    {
        $this->checkAvailability();

        return array_slice($this->facturas[$nit] ?? [], 0, $limite);
    }

    public function detalleFactura(int $rowidFactura): array
    {
        $this->checkAvailability();

        return [];
    }

    public function countClientesHuerfanos(int $compania, array $nitsExcluir = []): int
    {
        $this->checkAvailability();

        return 0;
    }

    public function existenciasMP(
        ?string $buscar = null,
        int $pagina = 1,
        int $porPagina = 50,
        ?string $ordenarPor = null,
        string $direccion = 'asc',
        ?int $diasVencer = null
    ): array {
        $this->checkAvailability();

        $todos = array_filter(
            $this->existenciasMP,
            fn (array $r) => (!$buscar
                || str_contains(strtolower($r['DescItem'] ?? ''), strtolower($buscar))
                || str_contains(strtolower($r['Referencia'] ?? ''), strtolower($buscar))
                || str_contains(strtolower($r['Lote'] ?? ''), strtolower($buscar)))
                && ($diasVencer === null
                    || ($r['DiasParaVencer'] !== null && $r['DiasParaVencer'] >= 0 && $r['DiasParaVencer'] <= $diasVencer))
        );

        $todos = array_values($todos);

        if ($ordenarPor) {
            usort($todos, function (array $a, array $b) use ($ordenarPor, $direccion) {
                $va = $a[$ordenarPor] ?? '';
                $vb = $b[$ordenarPor] ?? '';
                $cmp = is_numeric($va) && is_numeric($vb) ? ($va <=> $vb) : strcasecmp((string) $va, (string) $vb);

                return $direccion === 'desc' ? -$cmp : $cmp;
            });
        }

        $total = count($todos);
        $data  = array_slice($todos, ($pagina - 1) * $porPagina, $porPagina);

        return compact('data', 'total', 'pagina', 'porPagina');
    }

    public function agregarExistenciasMP(array $filas): void
    {
        $this->existenciasMP = $filas;
    }

    public function actualizarLoteExistenciaMP(
        int $compania,
        string $codBodega,
        string $referencia,
        string $lote,
        array $campos
    ): bool {
        $this->checkAvailability();

        foreach ($this->existenciasMP as &$fila) {
            if ((int) ($fila['Compania'] ?? 0) === $compania
                && ($fila['CodBodega'] ?? null) === $codBodega
                && ($fila['Referencia'] ?? null) === $referencia
                && ($fila['Lote'] ?? null) === $lote
            ) {
                $fila = array_merge($fila, $campos);

                return true;
            }
        }

        return false;
    }

    public function agregarClientesDeVendedor(string $nombreVendedor, array $clientes): void
    {
        $this->clientesVendedor[$nombreVendedor] = $clientes;
    }

    public function agregarFacturas(string $nit, array $facturas): void
    {
        $this->facturas[$nit] = $facturas;
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

    public function agregarCartera(string $nit, array $filas): void
    {
        $this->cartera[$nit] = $filas;
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
