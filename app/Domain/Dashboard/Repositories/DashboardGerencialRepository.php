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
            ->where('compania', $compania)
            ->where('anio', $anio)
            ->get();
    }

    public function codsPorAsesor(int $compania): Collection
    {
        return VendedorEquivalencia::where('compania', $compania)
            ->where('activo', true)
            ->get(['asesor_id', 'cod_vendedor_siesa']);
    }

    public function logradoVendedoresYtd(int $compania, int $anio): Collection
    {
        return DB::connection('erp_contiflex')
            ->table('vw_CRM_Ventas_Vendedor_Periodo')
            ->where('COMPANIA', $compania)
            ->where('ANIO', $anio)
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
                DB::raw('SUM(sf_negocios.valor_estimado * sf_negocios.probabilidad_cierre / 100.0) AS forecast')
            )
            ->get();
    }

    public function cicloDeVenta(int $anio): Collection
    {
        return Negocio::join('sf_pipeline_estados as pe', 'pe.id', '=', 'sf_negocios.pipeline_estado_id')
            ->join('users as u', 'u.id', '=', 'sf_negocios.asesor_id')
            ->where('pe.es_ganado', true)
            ->whereNotNull('fecha_cierre_real')
            ->whereYear('fecha_cierre_real', $anio)
            ->groupBy('u.id', 'u.name')
            ->select(
                'u.name as vendedor',
                DB::raw('ROUND(AVG(DATEDIFF(fecha_cierre_real, sf_negocios.created_at))) AS dias')
            )
            ->get();
    }

    public function motivosDePerdida(int $anio): Collection
    {
        return Negocio::join('sf_pipeline_estados as pe', 'pe.id', '=', 'sf_negocios.pipeline_estado_id')
            ->leftJoin('sf_maestros_comerciales as m', 'm.id', '=', 'sf_negocios.motivo_perdida_id')
            ->where('pe.es_perdido', true)
            ->whereYear('fecha_cierre_real', $anio)
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

        return DB::connection('erp_contiflex')
            ->table('vw_CRM_Ventas_por_Vendedor_Cliente')
            ->where('COMPANIA', $compania)
            ->where('VLR_NETO_FACTURADO', '>', 0)
            ->select(
                DB::raw("$banda AS banda"),
                DB::raw('COUNT(*) AS num_clientes'),
                DB::raw('SUM(VLR_NETO_FACTURADO) AS valor_en_banda')
            )
            ->groupBy(DB::raw($banda))
            ->orderBy('banda')
            ->get();
    }

    public function actividadEquipo(int $anio): Collection
    {
        return Seguimiento::join('users as u', 'u.id', '=', 'seguimientos.user_id')
            ->whereYear('fecha_seguimiento', $anio)
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
}
