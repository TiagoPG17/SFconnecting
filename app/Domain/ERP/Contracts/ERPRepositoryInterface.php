<?php

declare(strict_types=1);

namespace App\Domain\ERP\Contracts;

interface ERPRepositoryInterface
{
    public function clientePorNit(string $nit): ?array;

    public function clientesPorNombre(string $nombre, int $limite = 20): array;

    public function documentosPorCliente(string $nit, int $limite = 50): array;

    public function saldoPorCliente(string $nit): ?array;

    /** Retorna la cartera por cobrar del cliente (un registro por documento/cuota), con aging. */
    public function carteraPorNit(string $nit): array;

    public function clientesAtencionInmediata(int $limite = 20, ?string $filtroVendedor = null): array;

    public function clientesRescate(int $limite = 20, ?string $filtroVendedor = null): array;

    public function panoramaGerencial(int $compania = 0): array;

    public function clientesIntegrales(int $limite = 30): array;

    public function clientesEnFuga(int $limite = 50, ?string $filtroVendedor = null): array;

    public function clientesExpansion(int $limite = 50, ?string $filtroVendedor = null): array;

    public function clientesPresupuestoActivo(int $limite = 30, ?string $filtroVendedor = null): array;

    public function clientesPresupuestoEnRiesgo(int $limite = 30, ?string $filtroVendedor = null): array;

    public function clientesPresupuestoRecuperar(int $limite = 30, ?string $filtroVendedor = null): array;

    public function clientesLargoPlazo(int $limite = 30, ?string $filtroVendedor = null): array;

    public function panoramaPresupuestal(int $compania = 0): array;

    public function countClientesHuerfanos(int $compania, array $nitsExcluir = []): int;

    public function clientesHuerfanos(int $compania, array $nitsExcluir = [], int $porPagina = 50, int $offset = 0): array;

    public function clientesPorVendedorYHorizonte(
        string $vendedor,
        string $horizonte,
        int $compania = 0,
        int $limite = 100
    ): array;

    public function clientesPorVendedor(
        string $nombreVendedor,
        ?string $buscar = null,
        int $pagina = 1,
        int $porPagina = 20
    ): array;

    /** Retorna TODOS los clientes del vendedor sin paginación para sincronización bulk. */
    public function todosClientesPorVendedor(string $nombreVendedor): array;

    /**
     * Retorna ventas mensuales del cliente (últimos 5 años) agrupadas en tres datasets:
     * mensual (12 meses), trimestral (8 trimestres), anual (5 años).
     *
     * @return array{mensual: array, trimestral: array, anual: array}
     */
    public function ventasMensualesPorNit(string $nit): array;

    /** Retorna las últimas facturas del cliente por NIT (maestro). */
    public function facturasPorNit(string $nit, int $limite = 60): array;

    /** Retorna el detalle de ítems de una factura por ROWID_FACTURA. */
    public function detalleFactura(int $rowidFactura): array;

    public function isAvailable(): bool;
}
