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

    /** Resumen de pedidos con entrega mañana/pasado mañana, agrupado por cierre y compañía. */
    public function cierresProximos(int $compania): Collection;

    /** Detalle de pedidos con entrega mañana/pasado mañana (agenda de despacho). */
    public function pedidosPorCerrarDetalle(int $compania): Collection;

    /** Tendencia de facturación mensual: N meses terminando en el año/mes dado, agrupada por compañía. */
    public function facturacionMensualTendencia(int $compania, int $anio, int $mes, int $mesesAtras = 24): Collection;

    /** Ranking de clientes por facturación del mes/año dado. */
    public function facturacionPorCliente(int $compania, int $anio, int $mes, int $limite = 12): Collection;

    /** Facturación del mes/año dado, agrupada por vendedor y compañía. */
    public function facturacionPorVendedor(int $compania, int $anio, int $mes): Collection;

    /** Canasta futura (resumen): pedidos pendientes agrupados por mes de compromiso, desde el mes actual en adelante. */
    public function canastaFuturaResumen(int $compania): Collection;

    /** Canasta futura (detalle): líneas de pedidos pendientes desde el mes actual en adelante, por fecha de compromiso. */
    public function canastaFuturaDetalle(int $compania, int $limite = 500): Collection;
}
