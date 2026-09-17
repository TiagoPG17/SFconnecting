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

    /** Canasta (resumen): pedidos pendientes agrupados por mes de compromiso. Usa el $mes dado sobre el año actual real (el filtro Año del resto del bloque no le aplica todavía). Si $incluirFuturos, desde ese mes en adelante; si no, solo ese mes exacto. */
    public function canastaResumen(int $compania, int $mes, bool $incluirFuturos): Collection;

    /** Canasta (detalle): líneas de pedidos pendientes por fecha de compromiso, con el mismo criterio de rango que canastaResumen(). */
    public function canastaDetalle(int $compania, int $mes, bool $incluirFuturos, int $limite = 500): Collection;
}
