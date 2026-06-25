<?php

declare(strict_types=1);

namespace App\Domain\Dashboard\Repositories;

use App\Domain\Clientes\Models\Cliente;
use App\Domain\Dashboard\Models\Presupuesto;
use App\Domain\Dashboard\Models\VendedorEquivalencia;
use App\Domain\Pipeline\Models\PipelineEstado;
use App\Domain\Seguimientos\Models\Seguimiento;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class DashboardVendedorRepository implements DashboardVendedorRepositoryInterface
{
    public function companiasDelAsesor(int $asesorId): array
    {
        return VendedorEquivalencia::where('asesor_id', $asesorId)
            ->where('activo', true)
            ->orderBy('compania')
            ->pluck('compania')
            ->map(fn ($c) => (int) $c)
            ->toArray();
    }

    public function nombreVendedorSiesa(int $asesorId): ?string
    {
        return VendedorEquivalencia::where('asesor_id', $asesorId)
            ->where('activo', true)
            ->value('nombre_vendedor');
    }

    public function presupuestoVendedor(int $asesorId, int $compania, int $anio): ?object
    {
        return Presupuesto::where('asesor_id', $asesorId)
            ->where('compania', $compania)
            ->where('anio', $anio)
            ->first();
    }

    public function codVendedorSiesa(int $asesorId, int $compania): ?string
    {
        return VendedorEquivalencia::where('asesor_id', $asesorId)
            ->where('compania', $compania)
            ->where('activo', true)
            ->value('cod_vendedor_siesa');
    }

    public function logradoYtd(string $codVendedor, int $compania, array $meses): float
    {
        [$anio, $mesesNum] = $this->parsearMeses($meses);

        return (float) DB::connection('erp_contiflex')
            ->table('vw_CRM_Ventas_Vendedor_Periodo')
            ->where('COMPANIA', $compania)
            ->whereRaw('LTRIM(RTRIM(COD_VENDEDOR)) = ?', [trim($codVendedor)])
            ->where('ANIO', $anio)
            ->whereIn('MES', $mesesNum)
            ->sum('VLR_NETO_FACTURADO');
    }

    public function proximasActividades(int $asesorId, int $limit): Collection
    {
        return Seguimiento::with(['cliente:id,razon_social', 'prospecto:id,empresa'])
            ->delAsesor($asesorId)
            ->pendientes()
            ->whereNotNull('proxima_fecha')
            ->orderBy('proxima_fecha')
            ->limit($limit)
            ->get();
    }

    public function etapasPipelinePersonal(int $asesorId): Collection
    {
        return PipelineEstado::negocio()
            ->orderBy('orden')
            ->withCount(['negocios as num_negocios' => fn ($q) =>
                $q->where('asesor_id', $asesorId)->where('activo', true)])
            ->withSum(['negocios as valor_etapa' => fn ($q) =>
                $q->where('asesor_id', $asesorId)->where('activo', true)], 'valor_estimado')
            ->get(['id', 'nombre', 'color', 'orden', 'porcentaje_cierre']);
    }

    public function diasPromEtapa(int $estadoId, int $asesorId): float
    {
        return (float) DB::table('sf_negocios as n')
            ->leftJoinSub(
                DB::table('sf_auditoria_pipeline')
                    ->select('auditable_id', DB::raw('MAX(created_at) as ultimo'))
                    ->where('auditable_type', 'App\\Domain\\Negocios\\Models\\Negocio')
                    ->where('evento', 'cambio_estado')
                    ->groupBy('auditable_id'),
                'ult', 'ult.auditable_id', '=', 'n.id'
            )
            ->where('n.pipeline_estado_id', $estadoId)
            ->where('n.asesor_id', $asesorId)
            ->where('n.activo', true)
            ->whereNull('n.deleted_at')
            ->avg(DB::raw('DATEDIFF(NOW(), ult.ultimo)')) ?? 0;
    }

    public function clientesSinContacto30(int $asesorId): Collection
    {
        return Cliente::delAsesor($asesorId)
            ->activos()
            ->select('clientes.id', 'clientes.nit', 'clientes.razon_social', 'clientes.ciudad')
            ->selectRaw('MAX(seguimientos.fecha_seguimiento) AS ultimo_contacto')
            ->selectRaw('DATEDIFF(NOW(), MAX(seguimientos.fecha_seguimiento)) AS dias_sin_contacto')
            ->leftJoin('seguimientos', 'seguimientos.cliente_id', '=', 'clientes.id')
            ->groupBy('clientes.id', 'clientes.nit', 'clientes.razon_social', 'clientes.ciudad')
            ->havingRaw('dias_sin_contacto IS NULL OR dias_sin_contacto > 30')
            ->get();
    }

    public function recenciaClientes(array $nits, int $compania): Collection
    {
        return DB::connection('erp_contiflex')
            ->table('vw_CRM_Ventas_por_Vendedor_Cliente')
            ->where('COMPANIA', $compania)
            ->whereIn('NIT', $nits)
            ->select('NIT', 'VLR_NETO_FACTURADO', 'DIAS_DESDE_ULTIMA_COMPRA')
            ->get();
    }

    public function rankingVendedores(int $compania, array $meses): Collection
    {
        [$anio, $mesesNum] = $this->parsearMeses($meses);

        $placeholders = implode(',', array_fill(0, count($mesesNum), '?'));

        $ventas = DB::connection('erp_contiflex')
            ->table('CRM_stg_facturas_venta AS f')
            ->select(
                'f.f461_id_cia AS COMPANIA',
                'f.f461_rowid_tercero_vendedor AS ROWID_VENDEDOR',
                DB::raw('LTRIM(RTRIM(MAX(f.f461_cod_vendedor))) AS COD_VENDEDOR'),
                DB::raw("SUM(CASE
                    WHEN f.f461_id_concepto = 501 THEN  (f.f461_vlr_bruto - f.f461_vlr_dscto)
                    WHEN f.f461_id_concepto = 502 THEN -(f.f461_vlr_bruto - f.f461_vlr_dscto)
                    ELSE 0
                END) AS logrado"),
            )
            ->where('f.f461_id_cia', $compania)
            ->whereRaw('YEAR(f.f461_id_fecha) = ?', [$anio])
            ->whereRaw("MONTH(f.f461_id_fecha) IN ({$placeholders})", $mesesNum)
            ->groupBy('f.f461_id_cia', 'f.f461_rowid_tercero_vendedor');

        $nombres = DB::connection('erp_contiflex')
            ->table('vw_CRM_Detalle_Mensual_Vendedor')
            ->select('ROWID_VENDEDOR', DB::raw('MAX(NOMBRE_VENDEDOR) AS NOMBRE_VENDEDOR'))
            ->groupBy('ROWID_VENDEDOR');

        return DB::connection('erp_contiflex')
            ->query()
            ->fromSub($ventas, 's')
            ->leftJoinSub($nombres, 'd', 'd.ROWID_VENDEDOR', '=', 's.ROWID_VENDEDOR')
            ->select(
                's.COD_VENDEDOR',
                DB::raw("ISNULL(d.NOMBRE_VENDEDOR, 'VENDEDOR ' + CAST(s.ROWID_VENDEDOR AS varchar(12))) AS NOMBRE_VENDEDOR"),
                's.logrado',
            )
            ->orderByDesc('s.logrado')
            ->get();
    }

    private function parsearMeses(array $meses): array
    {
        // $meses = ['2026-04','2026-05','2026-06'] → anio=2026, mesesNum=[4,5,6]
        $anio      = (int) substr($meses[0], 0, 4);
        $mesesNum  = array_map(fn ($m) => (int) substr($m, 5, 2), $meses);
        return [$anio, $mesesNum];
    }
}
