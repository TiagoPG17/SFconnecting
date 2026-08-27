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

    /** Facturado del mes/año dado, agrupado por compañía (dbo.vw_CRM_Facturacion_Mes). */
    public function facturadoDelMes(int $compania, int $anio, int $mes): Collection;

    /** Pedidos pendientes del mes/año dado (por fecha de pedido), agrupado por compañía. */
    public function pendienteDelMes(int $compania, int $anio, int $mes): Collection;

    /** Resumen de pedidos con entrega mañana/pasado mañana, agrupado por cierre y compañía. */
    public function cierresProximos(int $compania): Collection;

    /** Detalle de pedidos con entrega mañana/pasado mañana (agenda de despacho). */
    public function pedidosPorCerrarDetalle(int $compania): Collection;

    /** Tendencia de facturación mensual: N meses terminando en el año/mes dado, agrupada por compañía. */
    public function facturacionMensualTendencia(int $compania, int $anio, int $mes, int $mesesAtras = 24): Collection;

    /** Ranking de clientes por facturación del mes/año dado. */
    public function facturacionPorCliente(int $compania, int $anio, int $mes, int $limite = 12): Collection;

    /** Ranking de clientes por valor de pedidos pendientes del mes/año dado (por fecha de pedido). */
    public function pedidosPendientesPorCliente(int $compania, int $anio, int $mes, int $limite = 12): Collection;

    /** Drill-down: líneas de pedidos pendientes de un cliente puntual en el mes/año dado. */
    public function pedidosPendientesDetallePorCliente(int $compania, int $anio, int $mes, string $cliente): Collection;

    /** Facturación del mes/año dado, agrupada por vendedor y compañía. */
    public function facturacionPorVendedor(int $compania, int $anio, int $mes): Collection;
}
