<?php

declare(strict_types=1);

namespace App\Domain\Dashboard\Repositories;

use App\Domain\Dashboard\Models\Presupuesto;
use App\Domain\Dashboard\Models\VendedorEquivalencia;
use App\Domain\Negocios\Models\Negocio;
use App\Domain\Seguimientos\Models\Seguimiento;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class DashboardGerencialRepository implements DashboardGerencialRepositoryInterface
{
    public function presupuestoPorAsesor(int $compania, int $anio): Collection
    {
        return Presupuesto::with('asesor:id,name')
            ->when($compania > 0, fn ($q) => $q->where('compania', $compania))
            ->where('anio', $anio)
            ->get();
    }

    public function codsPorAsesor(int $compania): Collection
    {
        return VendedorEquivalencia::when($compania > 0, fn ($q) => $q->where('compania', $compania))
            ->where('activo', true)
            ->get(['asesor_id', 'cod_vendedor_siesa']);
    }

    public function logradoVendedoresYtd(int $compania, array $meses): Collection
    {
        [$anio, $mesesNum] = $this->parsearMeses($meses);

        return DB::connection('erp_contiflex')
            ->table('vw_CRM_Ventas_Vendedor_Periodo')
            ->when($compania > 0, fn ($q) => $q->where('COMPANIA', $compania))
            ->where('ANIO', $anio)
            ->whereIn('MES', $mesesNum)
            ->select(DB::raw('LTRIM(RTRIM(COD_VENDEDOR)) AS COD_VENDEDOR'), DB::raw('SUM(VLR_NETO_FACTURADO) AS logrado'))
            ->groupBy(DB::raw('LTRIM(RTRIM(COD_VENDEDOR))'))
            ->get();
    }

    public function forecastPipelineAsesor(): Collection
    {
        return Negocio::query()
            ->join('sf_pipeline_estados as pe', 'pe.id', '=', 'sf_negocios.pipeline_estado_id')
            ->where('sf_negocios.activo', true)
            ->where('pe.es_final', false)
            ->whereNull('sf_negocios.deleted_at')
            ->groupBy('sf_negocios.asesor_id')
            ->select(
                'sf_negocios.asesor_id',
                DB::raw('SUM(sf_negocios.valor_estimado) AS forecast')
            )
            ->get();
    }

    public function cicloDeVenta(array $meses): Collection
    {
        [$anio, $mesesNum] = $this->parsearMeses($meses);

        return Negocio::join('sf_pipeline_estados as pe', 'pe.id', '=', 'sf_negocios.pipeline_estado_id')
            ->join('users as u', 'u.id', '=', 'sf_negocios.asesor_id')
            ->where('pe.es_ganado', true)
            ->whereNotNull('fecha_cierre_real')
            ->whereYear('fecha_cierre_real', $anio)
            ->whereIn(DB::raw('MONTH(fecha_cierre_real)'), $mesesNum)
            ->groupBy('u.id', 'u.name')
            ->select(
                'u.name as vendedor',
                DB::raw('ROUND(AVG(DATEDIFF(fecha_cierre_real, sf_negocios.created_at))) AS dias')
            )
            ->get();
    }

    public function motivosDePerdida(array $meses): Collection
    {
        [$anio, $mesesNum] = $this->parsearMeses($meses);

        return Negocio::join('sf_pipeline_estados as pe', 'pe.id', '=', 'sf_negocios.pipeline_estado_id')
            ->leftJoin('sf_maestros_comerciales as m', 'm.id', '=', 'sf_negocios.motivo_perdida_id')
            ->where('pe.es_perdido', true)
            ->whereYear('fecha_cierre_real', $anio)
            ->whereIn(DB::raw('MONTH(fecha_cierre_real)'), $mesesNum)
            ->groupBy('m.nombre', 'm.color')
            ->select(
                'm.nombre as motivo',
                'm.color',
                DB::raw('COUNT(*) AS valor'),
                DB::raw('COALESCE(SUM(valor_estimado), 0) AS valor_perdido')
            )
            ->orderByDesc('valor')
            ->get();
    }

    public function retencionChurn(int $compania): Collection
    {
        $banda = "CASE
            WHEN DIAS_DESDE_ULTIMA_COMPRA <= 90  THEN '1-Activo'
            WHEN DIAS_DESDE_ULTIMA_COMPRA <= 180 THEN '2-Tibio'
            WHEN DIAS_DESDE_ULTIMA_COMPRA <= 365 THEN '3-En riesgo'
            ELSE '4-Inactivo' END";

        $valor = "SUM(CASE
            WHEN DIAS_DESDE_ULTIMA_COMPRA <= 180 THEN FACTURADO_ANIO_ACTUAL
            ELSE FACTURADO_ANIO_ANTERIOR
        END)";

        return DB::connection('erp_contiflex')
            ->table('dbo.vw_CRM_Clientes_Prioritarios')
            ->when($compania > 0, fn ($q) => $q->where('COMPANIA', $compania))
            ->select(
                DB::raw("$banda AS banda"),
                DB::raw('COUNT(*) AS num_clientes'),
                DB::raw("$valor AS valor_en_banda")
            )
            ->groupBy(DB::raw($banda))
            ->orderBy('banda')
            ->get();
    }

    public function actividadEquipo(array $meses): Collection
    {
        [$anio, $mesesNum] = $this->parsearMeses($meses);

        return Seguimiento::join('users as u', 'u.id', '=', 'seguimientos.user_id')
            ->whereYear('fecha_seguimiento', $anio)
            ->whereIn(DB::raw('MONTH(fecha_seguimiento)'), $mesesNum)
            ->groupBy('u.id', 'u.name', 'seguimientos.tipo')
            ->select(
                'u.name as vendedor',
                'seguimientos.tipo',
                DB::raw('COUNT(*) AS num_actividades'),
                DB::raw("SUM(CASE WHEN resultado = 'exitoso' THEN 1 ELSE 0 END) AS exitosas")
            )
            ->orderBy('u.name')
            ->get();
    }

    public function facturadoDelMes(int $compania, int $anio, int $mes): Collection
    {
        return DB::connection('erp_contiflex')
            ->table('dbo.vw_CRM_Facturacion_Mes')
            ->when($compania > 0, fn ($q) => $q->where('Compania', $compania))
            ->where('Anio', $anio)
            ->where('Mes', $mes)
            ->selectRaw('
                Compania                       AS compania,
                SUM(ValorSubtotalLocal)        AS subtotal_mes,
                COUNT(DISTINCT RowidFactura)   AS documentos
            ')
            ->groupBy('Compania')
            ->orderBy('Compania')
            ->get();
    }

    public function pendienteDelMes(int $compania, int $anio, int $mes): Collection
    {
        return DB::connection('erp_contiflex')
            ->table('dbo.vw_CRM_Pedidos_Pendientes')
            ->when($compania > 0, fn ($q) => $q->where('Compania', $compania))
            ->whereYear('FechaPedido', $anio)
            ->whereMonth('FechaPedido', $mes)
            ->selectRaw('
                Compania                     AS compania,
                COUNT(DISTINCT NroDocumento) AS num_pedidos,
                SUM(ValorSubtotalLocal)      AS subtotal_pendiente
            ')
            ->groupBy('Compania')
            ->orderBy('Compania')
            ->get();
    }

    public function cierresProximos(int $compania): Collection
    {
        $cierre = "CASE WHEN CAST(FechaEntrega AS date) = CAST(DATEADD(DAY,1,GETDATE()) AS date)
                        THEN 'MAÑANA' ELSE 'PASADO MAÑANA' END";

        return DB::connection('erp_contiflex')
            ->table('dbo.vw_CRM_Pedidos_Pendientes')
            ->when($compania > 0, fn ($q) => $q->where('Compania', $compania))
            ->whereRaw('CAST(FechaEntrega AS date) IN (
                CAST(DATEADD(DAY,1,GETDATE()) AS date),
                CAST(DATEADD(DAY,2,GETDATE()) AS date)
            )')
            ->selectRaw("
                {$cierre}                    AS cierre,
                Compania                     AS compania,
                COUNT(DISTINCT NroDocumento) AS num_pedidos,
                SUM(ValorSubtotalLocal)      AS total_pendiente
            ")
            ->groupBy(DB::raw($cierre), 'Compania')
            ->orderBy('cierre')
            ->orderBy('Compania')
            ->get();
    }

    public function pedidosPorCerrarDetalle(int $compania): Collection
    {
        $cierre = "CASE WHEN CAST(FechaEntrega AS date) = CAST(DATEADD(DAY,1,GETDATE()) AS date)
                        THEN 'MAÑANA' ELSE 'PASADO MAÑANA' END";

        return DB::connection('erp_contiflex')
            ->table('dbo.vw_CRM_Pedidos_Pendientes')
            ->when($compania > 0, fn ($q) => $q->where('Compania', $compania))
            ->whereRaw('CAST(FechaEntrega AS date) IN (
                CAST(DATEADD(DAY,1,GETDATE()) AS date),
                CAST(DATEADD(DAY,2,GETDATE()) AS date)
            )')
            ->selectRaw("
                {$cierre}               AS cierre,
                Compania                AS compania,
                FechaEntrega             AS fecha_entrega,
                RazonSocialCliente       AS cliente,
                NombreVendedor           AS vendedor,
                NroDocumento             AS nro_documento,
                DescItem                 AS desc_item,
                CantPendiente            AS cant_pendiente,
                ValorSubtotalLocal       AS valor_subtotal,
                Estado                   AS estado
            ")
            ->orderBy('FechaEntrega')
            ->orderBy('RazonSocialCliente')
            ->limit(300)
            ->get();
    }

    public function facturacionMensualTendencia(int $compania, int $anio, int $mes, int $mesesAtras = 24): Collection
    {
        $hasta = \Carbon\Carbon::create($anio, $mes, 1)->endOfMonth();
        $desde = $hasta->copy()->subMonths($mesesAtras - 1)->startOfMonth();

        return DB::connection('erp_contiflex')
            ->table('dbo.vw_CRM_Facturacion_Mes')
            ->when($compania > 0, fn ($q) => $q->where('Compania', $compania))
            ->whereRaw('(Anio * 100 + Mes) BETWEEN ? AND ?', [
                $desde->year * 100 + $desde->month,
                $hasta->year * 100 + $hasta->month,
            ])
            ->selectRaw('
                Compania                     AS compania,
                Anio                         AS anio,
                Mes                          AS mes,
                SUM(ValorSubtotalLocal)      AS subtotal_mes,
                COUNT(DISTINCT RowidFactura) AS documentos
            ')
            ->groupBy('Compania', 'Anio', 'Mes')
            ->orderBy('Anio')
            ->orderBy('Mes')
            ->get();
    }

    public function facturacionPorCliente(int $compania, int $anio, int $mes, int $limite = 12): Collection
    {
        return DB::connection('erp_contiflex')
            ->table('dbo.vw_CRM_Facturacion_Mes as f')
            ->leftJoin('dbo.clientes as c', function ($join) {
                $join->on('c.COMPANIA', '=', 'f.Compania')
                     ->on('c.COD_INT_CLIENTE', '=', 'f.RowidClienteFact');
            })
            ->when($compania > 0, fn ($q) => $q->where('f.Compania', $compania))
            ->where('f.Anio', $anio)
            ->where('f.Mes', $mes)
            ->selectRaw("
                f.Compania                                                     AS compania,
                ISNULL(c.RAZON_SOCIAL, CAST(f.RowidClienteFact AS varchar(12))) AS cliente,
                COUNT(DISTINCT f.RowidFactura)                                  AS facturas,
                SUM(f.ValorSubtotalLocal)                                      AS facturado
            ")
            ->groupBy('f.Compania', DB::raw('ISNULL(c.RAZON_SOCIAL, CAST(f.RowidClienteFact AS varchar(12)))'))
            ->orderByDesc('facturado')
            ->limit($limite)
            ->get();
    }

    public function pedidosPendientesPorCliente(int $compania, int $anio, int $mes, int $limite = 12): Collection
    {
        return DB::connection('erp_contiflex')
            ->table('dbo.vw_CRM_Pedidos_Pendientes')
            ->when($compania > 0, fn ($q) => $q->where('Compania', $compania))
            ->whereYear('FechaPedido', $anio)
            ->whereMonth('FechaPedido', $mes)
            ->selectRaw('
                Compania                     AS compania,
                RazonSocialCliente           AS cliente,
                COUNT(DISTINCT NroDocumento) AS num_pedidos,
                SUM(CantPendiente)           AS cant_pendiente,
                SUM(ValorSubtotalLocal)      AS total_pendiente
            ')
            ->groupBy('Compania', 'RazonSocialCliente')
            ->orderByDesc('total_pendiente')
            ->limit($limite)
            ->get();
    }

    public function pedidosPendientesDetallePorCliente(int $compania, int $anio, int $mes, string $cliente): Collection
    {
        return DB::connection('erp_contiflex')
            ->table('dbo.vw_CRM_Pedidos_Pendientes')
            ->when($compania > 0, fn ($q) => $q->where('Compania', $compania))
            ->whereYear('FechaPedido', $anio)
            ->whereMonth('FechaPedido', $mes)
            ->where('RazonSocialCliente', $cliente)
            ->selectRaw('
                Compania             AS compania,
                RazonSocialCliente   AS cliente,
                NroDocumento         AS nro_documento,
                FechaPedido          AS fecha_pedido,
                FechaEntrega         AS fecha_entrega,
                DescItem             AS desc_item,
                CantPendiente        AS cant_pendiente,
                PrecioUnit           AS precio_unit,
                ValorSubtotalLocal   AS valor_subtotal,
                Estado               AS estado
            ')
            ->orderBy('NroDocumento')
            ->get();
    }

    public function facturacionPorVendedor(int $compania, int $anio, int $mes): Collection
    {
        return DB::connection('erp_contiflex')
            ->table('dbo.vw_CRM_Facturacion_Mes')
            ->when($compania > 0, fn ($q) => $q->where('Compania', $compania))
            ->where('Anio', $anio)
            ->where('Mes', $mes)
            ->selectRaw('
                Compania                     AS compania,
                CodVendedor                  AS cod_vendedor,
                SUM(ValorSubtotalLocal)      AS facturado,
                COUNT(DISTINCT RowidFactura) AS documentos
            ')
            ->groupBy('Compania', 'CodVendedor')
            ->orderByDesc('facturado')
            ->get();
    }

    private function parsearMeses(array $meses): array
    {
        $anio     = (int) substr($meses[0], 0, 4);
        $mesesNum = array_map(fn ($m) => (int) substr($m, 5, 2), $meses);

        return [$anio, $mesesNum];
    }
}
