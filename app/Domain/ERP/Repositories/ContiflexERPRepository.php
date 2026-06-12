<?php

declare(strict_types=1);

namespace App\Domain\ERP\Repositories;

use App\Domain\ERP\Contracts\ERPRepositoryInterface;
use App\Domain\ERP\Exceptions\ERPConnectionException;
use Illuminate\Support\Facades\DB;
use Throwable;

class ContiflexERPRepository implements ERPRepositoryInterface
{
    public function clientePorNit(string $nit): ?array
    {
        try {
            $row = DB::connection('erp_contiflex')
                ->table('clientes')
                ->where('NIT', $nit)
                ->first();

            return $row ? (array) $row : null;
        } catch (Throwable $e) {
            throw ERPConnectionException::queryFailed($e->getMessage());
        }
    }

    public function clientesPorNombre(string $nombre, int $limite = 20): array
    {
        try {
            return DB::connection('erp_contiflex')
                ->table('clientes')
                ->where('nombre', 'like', "%{$nombre}%")
                ->limit($limite)
                ->get()
                ->toArray();
        } catch (Throwable $e) {
            throw ERPConnectionException::queryFailed($e->getMessage());
        }
    }

    public function documentosPorCliente(string $nit, int $limite = 50): array
    {
        try {
            return DB::connection('erp_contiflex')
                ->table('documentos')
                ->where('nit_cliente', $nit)
                ->orderByDesc('fecha')
                ->limit($limite)
                ->get()
                ->toArray();
        } catch (Throwable $e) {
            throw ERPConnectionException::queryFailed($e->getMessage());
        }
    }

    public function saldoPorCliente(string $nit): ?array
    {
        try {
            return DB::connection('erp_contiflex')
                ->table('saldos_clientes')
                ->where('nit', $nit)
                ->first()?->toArray();
        } catch (Throwable $e) {
            throw ERPConnectionException::queryFailed($e->getMessage());
        }
    }

    public function clientesAtencionInmediata(int $limite = 20, ?string $filtroVendedor = null): array
    {
        try {
            return DB::connection('erp_contiflex')
                ->table('dbo.vw_CRM_Clientes_Prioritarios')
                ->where('HORIZONTE_PRESUPUESTO', 'P1 - PRESUPUESTO ACTIVO')
                ->when($filtroVendedor, fn ($q) => $q->where('NOMBRE_VENDEDOR', $filtroVendedor))
                ->orderByDesc('FACTURADO_ANIO_ACTUAL')
                ->limit($limite)
                ->get([
                    'NOMBRE_VENDEDOR', 'NIT', 'RAZON_SOCIAL', 'CIUDAD',
                    'FACTURADO_ANIO_ACTUAL', 'FACTURADO_ANIO_ANTERIOR',
                    'VARIACION_ANUAL_PORC', 'NUM_FACTURAS_ANIO_ACTUAL',
                    'DIAS_DESDE_ULTIMA_COMPRA', 'HORIZONTE_PRESUPUESTO',
                    'ACCION_PRESUPUESTAL',
                ])
                ->map(fn ($r) => (array) $r)
                ->toArray();
        } catch (Throwable $e) {
            throw ERPConnectionException::queryFailed($e->getMessage());
        }
    }

    public function clientesRescate(int $limite = 20, ?string $filtroVendedor = null): array
    {
        try {
            return DB::connection('erp_contiflex')
                ->table('dbo.vw_CRM_Clientes_Prioritarios')
                ->whereIn('HORIZONTE_PRESUPUESTO', [
                    'P2 - PRESUPUESTO EN RIESGO',
                    'P3 - PRESUPUESTO PASADO (RECUPERAR)',
                ])
                ->when($filtroVendedor, fn ($q) => $q->where('NOMBRE_VENDEDOR', $filtroVendedor))
                ->orderByDesc('FACTURADO_ANIO_ACTUAL')
                ->limit($limite)
                ->get([
                    'NOMBRE_VENDEDOR', 'NIT', 'RAZON_SOCIAL', 'CIUDAD',
                    'FACTURADO_ANIO_ACTUAL', 'FACTURADO_ANIO_ANTERIOR',
                    'DIAS_DESDE_ULTIMA_COMPRA', 'HORIZONTE_PRESUPUESTO',
                    'PRIORIDAD_COMERCIAL', 'ACCION_PRESUPUESTAL',
                ])
                ->map(fn ($r) => (array) $r)
                ->toArray();
        } catch (Throwable $e) {
            throw ERPConnectionException::queryFailed($e->getMessage());
        }
    }

    public function panoramaGerencial(int $compania = 0): array
    {
        try {
            $whereCompania = $compania > 0 ? "WHERE COMPANIA = {$compania}" : '';

            $sql = "
                SELECT
                    COMPANIA,
                    NOMBRE_VENDEDOR,
                    COUNT(*) AS total_clientes,
                    SUM(CASE WHEN HORIZONTE_PRESUPUESTO = 'P1 - PRESUPUESTO ACTIVO'
                             THEN 1 ELSE 0 END) AS vip_activos,
                    SUM(CASE WHEN HORIZONTE_PRESUPUESTO = 'P2 - PRESUPUESTO EN RIESGO'
                             THEN 1 ELSE 0 END) AS urgentes,
                    SUM(CASE WHEN HORIZONTE_PRESUPUESTO = 'P3 - PRESUPUESTO PASADO (RECUPERAR)'
                             THEN 1 ELSE 0 END) AS rescate,
                    SUM(CASE WHEN HORIZONTE_PRESUPUESTO = 'P4 - FUERA DE PRESUPUESTO'
                             THEN 1 ELSE 0 END) AS reactivacion,
                    SUM(FACTURADO_ANIO_ACTUAL) AS facturacion_total,
                    SUM(CASE WHEN HORIZONTE_PRESUPUESTO = 'P1 - PRESUPUESTO ACTIVO'
                             THEN FACTURADO_ANIO_ACTUAL ELSE 0 END) AS valor_vip,
                    SUM(CASE WHEN HORIZONTE_PRESUPUESTO IN (
                                'P2 - PRESUPUESTO EN RIESGO',
                                'P3 - PRESUPUESTO PASADO (RECUPERAR)')
                             THEN FACTURADO_ANIO_ACTUAL ELSE 0 END) AS valor_en_riesgo,
                    SUM(CASE WHEN HORIZONTE_PRESUPUESTO = 'P4 - FUERA DE PRESUPUESTO'
                             THEN VLR_NETO_FACTURADO ELSE 0 END) AS valor_dormido,
                    CAST(
                        SUM(CASE WHEN HORIZONTE_PRESUPUESTO IN (
                                    'P2 - PRESUPUESTO EN RIESGO',
                                    'P3 - PRESUPUESTO PASADO (RECUPERAR)')
                                 THEN 1.0 ELSE 0 END) * 100.0 / NULLIF(COUNT(*), 0)
                    AS decimal(5,2)) AS porc_riesgo
                FROM dbo.vw_CRM_Clientes_Prioritarios
                {$whereCompania}
                GROUP BY COMPANIA, NOMBRE_VENDEDOR
                HAVING MIN(DIAS_DESDE_ULTIMA_COMPRA) <= 365
                  AND SUM(FACTURADO_ANIO_ACTUAL) + SUM(FACTURADO_ANIO_ANTERIOR) > 0
                ORDER BY COMPANIA, SUM(FACTURADO_ANIO_ACTUAL) DESC
            ";

            return collect(DB::connection('erp_contiflex')->select($sql))
                ->map(fn ($r) => (array) $r)
                ->toArray();
        } catch (Throwable $e) {
            throw ERPConnectionException::queryFailed($e->getMessage());
        }
    }

    public function clientesIntegrales(int $limite = 30): array
    {
        try {
            $sql = "
                ;WITH clientes_por_nit AS (
                    SELECT
                        NIT,
                        COUNT(DISTINCT COMPANIA) AS num_companias,
                        STUFF((
                            SELECT ', ' + CAST(c2.COMPANIA AS varchar(10))
                            FROM dbo.CRM_Consolidado_Ventas_cliente c2
                            WHERE c2.NIT = c1.NIT
                            GROUP BY c2.COMPANIA
                            ORDER BY c2.COMPANIA
                            FOR XML PATH(''), TYPE
                        ).value('.', 'nvarchar(max)'), 1, 2, '') AS companias_donde_compra
                    FROM dbo.CRM_Consolidado_Ventas_cliente c1
                    WHERE NIT IS NOT NULL
                    GROUP BY NIT
                )
                SELECT TOP {$limite}
                    cp.COMPANIA,
                    cp.NIT,
                    cp.RAZON_SOCIAL,
                    cp.NOMBRE_VENDEDOR,
                    cp.CIUDAD,
                    cp.VLR_NETO_FACTURADO,
                    cp.FACTURADO_ANIO_ACTUAL,
                    cp.ULTIMA_FACTURA,
                    cp.DIAS_DESDE_ULTIMA_COMPRA,
                    cp.HORIZONTE_PRESUPUESTO,
                    cp.ACCION_PRESUPUESTAL,
                    cn.companias_donde_compra,
                    CASE
                        WHEN cn.num_companias > 1 THEN 'YA INTEGRAL'
                        WHEN cp.COMPANIA = 1      THEN 'SOLO COMPAÑÍA 1 (plástico)'
                        WHEN cp.COMPANIA = 2      THEN 'SOLO COMPAÑÍA 2 (etiqueta)'
                        ELSE 'OTRA'
                    END AS perfil_cruzado
                FROM dbo.vw_CRM_Clientes_Prioritarios cp
                    INNER JOIN clientes_por_nit cn ON cn.NIT = cp.NIT
                WHERE cp.HORIZONTE_PRESUPUESTO IN (
                    'P1 - PRESUPUESTO ACTIVO',
                    'P2 - PRESUPUESTO EN RIESGO'
                )
                ORDER BY cp.FACTURADO_ANIO_ACTUAL DESC, cp.VLR_NETO_FACTURADO DESC
            ";

            return collect(DB::connection('erp_contiflex')->select($sql))
                ->map(fn ($r) => (array) $r)
                ->toArray();
        } catch (Throwable $e) {
            throw ERPConnectionException::queryFailed($e->getMessage());
        }
    }

    public function clientesEnFuga(int $limite = 50, ?string $filtroVendedor = null): array
    {
        try {
            return DB::connection('erp_contiflex')
                ->table('dbo.CRM_Consolidado_Ventas_cliente')
                ->whereBetween('DIAS_DESDE_ULTIMA_COMPRA', [90, 180])
                ->where('VLR_NETO_FACTURADO', '>', 100_000_000)
                ->when($filtroVendedor, fn ($q) => $q->where('NOMBRE_VENDEDOR', $filtroVendedor))
                ->orderByDesc('VLR_NETO_FACTURADO')
                ->limit($limite)
                ->get(['NIT', 'RAZON_SOCIAL', 'NOMBRE_VENDEDOR',
                       'VLR_NETO_FACTURADO', 'ULTIMA_FACTURA',
                       'DIAS_DESDE_ULTIMA_COMPRA'])
                ->map(fn ($r) => (array) $r)
                ->toArray();
        } catch (Throwable $e) {
            throw ERPConnectionException::queryFailed($e->getMessage());
        }
    }

    public function clientesExpansion(int $limite = 50, ?string $filtroVendedor = null): array
    {
        try {
            return DB::connection('erp_contiflex')
                ->table('dbo.CRM_Consolidado_Ventas_cliente')
                ->where('DIAS_DESDE_ULTIMA_COMPRA', '<=', 30)
                ->when($filtroVendedor, fn ($q) => $q->where('NOMBRE_VENDEDOR', $filtroVendedor))
                ->orderByDesc('VLR_NETO_FACTURADO')
                ->limit($limite)
                ->get(['NIT', 'RAZON_SOCIAL', 'NOMBRE_VENDEDOR',
                       'VLR_NETO_FACTURADO', 'NUM_FACTURAS',
                       'DIAS_DESDE_ULTIMA_COMPRA'])
                ->map(fn ($r) => (array) $r)
                ->toArray();
        } catch (Throwable $e) {
            throw ERPConnectionException::queryFailed($e->getMessage());
        }
    }

    public function clientesPresupuestoActivo(int $limite = 30, ?string $filtroVendedor = null): array
    {
        try {
            return DB::connection('erp_contiflex')
                ->table('dbo.vw_CRM_Clientes_Prioritarios')
                ->where('HORIZONTE_PRESUPUESTO', 'P1 - PRESUPUESTO ACTIVO')
                ->when($filtroVendedor, fn ($q) => $q->where('NOMBRE_VENDEDOR', $filtroVendedor))
                ->orderByDesc('FACTURADO_ANIO_ACTUAL')
                ->limit($limite)
                ->get([
                    'NOMBRE_VENDEDOR', 'NIT', 'RAZON_SOCIAL', 'CIUDAD',
                    'FACTURADO_ANIO_ACTUAL', 'FACTURADO_ANIO_ANTERIOR',
                    'VARIACION_ANUAL_PORC', 'NUM_FACTURAS_ANIO_ACTUAL',
                    'DIAS_DESDE_ULTIMA_COMPRA', 'HORIZONTE_PRESUPUESTO',
                    'ACCION_PRESUPUESTAL',
                ])
                ->map(fn ($r) => (array) $r)
                ->toArray();
        } catch (Throwable $e) {
            throw ERPConnectionException::queryFailed($e->getMessage());
        }
    }

    public function clientesPresupuestoEnRiesgo(int $limite = 30, ?string $filtroVendedor = null): array
    {
        try {
            return DB::connection('erp_contiflex')
                ->table('dbo.vw_CRM_Clientes_Prioritarios')
                ->where('HORIZONTE_PRESUPUESTO', 'P2 - PRESUPUESTO EN RIESGO')
                ->when($filtroVendedor, fn ($q) => $q->where('NOMBRE_VENDEDOR', $filtroVendedor))
                ->orderByDesc('FACTURADO_ANIO_ACTUAL')
                ->limit($limite)
                ->get([
                    'NOMBRE_VENDEDOR', 'NIT', 'RAZON_SOCIAL', 'CIUDAD',
                    'FACTURADO_ANIO_ACTUAL', 'DIAS_DESDE_ULTIMA_COMPRA',
                    'IMPACTO_PRESUPUESTO_ESTIMADO', 'HORIZONTE_PRESUPUESTO',
                    'PRIORIDAD_COMERCIAL', 'ACCION_PRESUPUESTAL',
                ])
                ->map(fn ($r) => (array) $r)
                ->toArray();
        } catch (Throwable $e) {
            throw ERPConnectionException::queryFailed($e->getMessage());
        }
    }

    public function clientesPresupuestoRecuperar(int $limite = 30, ?string $filtroVendedor = null): array
    {
        try {
            return DB::connection('erp_contiflex')
                ->table('dbo.vw_CRM_Clientes_Prioritarios')
                ->where('HORIZONTE_PRESUPUESTO', 'P3 - PRESUPUESTO PASADO (RECUPERAR)')
                ->when($filtroVendedor, fn ($q) => $q->where('NOMBRE_VENDEDOR', $filtroVendedor))
                ->orderByDesc('FACTURADO_ANIO_ANTERIOR')
                ->limit($limite)
                ->get([
                    'NOMBRE_VENDEDOR', 'NIT', 'RAZON_SOCIAL', 'CIUDAD',
                    'FACTURADO_ANIO_ANTERIOR', 'DIAS_DESDE_ULTIMA_COMPRA',
                    'HORIZONTE_PRESUPUESTO', 'PRIORIDAD_COMERCIAL',
                    'ACCION_PRESUPUESTAL',
                ])
                ->map(fn ($r) => (array) $r)
                ->toArray();
        } catch (Throwable $e) {
            throw ERPConnectionException::queryFailed($e->getMessage());
        }
    }

    public function clientesLargoPlazo(int $limite = 30, ?string $filtroVendedor = null): array
    {
        try {
            return DB::connection('erp_contiflex')
                ->table('dbo.vw_CRM_Clientes_Prioritarios')
                ->where('HORIZONTE_PRESUPUESTO', 'P4 - FUERA DE PRESUPUESTO')
                ->when($filtroVendedor, fn ($q) => $q->where('NOMBRE_VENDEDOR', $filtroVendedor))
                ->orderByDesc('VLR_NETO_FACTURADO')
                ->limit($limite)
                ->get([
                    'NOMBRE_VENDEDOR', 'NIT', 'RAZON_SOCIAL', 'CIUDAD',
                    'VLR_NETO_FACTURADO', 'DIAS_DESDE_ULTIMA_COMPRA',
                    'ULTIMA_FACTURA', 'HORIZONTE_PRESUPUESTO',
                    'ACCION_PRESUPUESTAL',
                ])
                ->map(fn ($r) => (array) $r)
                ->toArray();
        } catch (Throwable $e) {
            throw ERPConnectionException::queryFailed($e->getMessage());
        }
    }

    public function panoramaPresupuestal(int $compania = 0): array
    {
        try {
            $whereCompania       = $compania > 0 ? "AND COMPANIA = {$compania}" : '';
            $whereCompaniaCartera = $compania > 0 ? "WHERE COMPANIA = {$compania}" : '';

            $sql = "
                WITH ventas_reales AS (
                    SELECT
                        COMPANIA,
                        NOMBRE_VENDEDOR,
                        SUM(CASE WHEN LEFT(ANIO_MES, 4) = CAST(YEAR(GETDATE())     AS varchar) THEN VALOR ELSE 0 END) AS facturado_anio_actual,
                        SUM(CASE WHEN LEFT(ANIO_MES, 4) = CAST(YEAR(GETDATE()) - 1 AS varchar) THEN VALOR ELSE 0 END) AS facturado_anio_anterior
                    FROM dbo.vw_CRM_Detalle_Mensual_Vendedor
                    WHERE LEFT(ANIO_MES, 4) IN (
                        CAST(YEAR(GETDATE()) AS varchar),
                        CAST(YEAR(GETDATE()) - 1 AS varchar)
                    )
                      AND NOMBRE_VENDEDOR IS NOT NULL
                      AND LTRIM(RTRIM(NOMBRE_VENDEDOR)) <> ''
                      {$whereCompania}
                    GROUP BY COMPANIA, NOMBRE_VENDEDOR
                    HAVING SUM(VALOR) > 0
                ),
                cartera AS (
                    SELECT
                        COMPANIA,
                        NOMBRE_VENDEDOR,
                        SUM(CASE WHEN HORIZONTE_PRESUPUESTO = 'P1 - PRESUPUESTO ACTIVO'            THEN 1 ELSE 0 END) AS p1_activos,
                        SUM(CASE WHEN HORIZONTE_PRESUPUESTO = 'P2 - PRESUPUESTO EN RIESGO'         THEN 1 ELSE 0 END) AS p2_en_riesgo,
                        SUM(CASE WHEN HORIZONTE_PRESUPUESTO = 'P3 - PRESUPUESTO PASADO (RECUPERAR)' THEN 1 ELSE 0 END) AS p3_recuperar,
                        SUM(CASE WHEN HORIZONTE_PRESUPUESTO = 'P4 - FUERA DE PRESUPUESTO'          THEN 1 ELSE 0 END) AS p4_largo_plazo,
                        SUM(CASE WHEN HORIZONTE_PRESUPUESTO = 'P2 - PRESUPUESTO EN RIESGO'
                                 THEN IMPACTO_PRESUPUESTO_ESTIMADO ELSE 0 END) AS valor_a_rescatar_p2,
                        SUM(CASE WHEN HORIZONTE_PRESUPUESTO = 'P3 - PRESUPUESTO PASADO (RECUPERAR)'
                                 THEN FACTURADO_ANIO_ANTERIOR ELSE 0 END) AS valor_potencial_p3
                    FROM dbo.vw_CRM_Clientes_Prioritarios
                    {$whereCompaniaCartera}
                    GROUP BY COMPANIA, NOMBRE_VENDEDOR
                )
                SELECT
                    v.COMPANIA,
                    v.NOMBRE_VENDEDOR,
                    v.facturado_anio_actual,
                    v.facturado_anio_anterior,
                    CASE WHEN v.facturado_anio_anterior > 0
                         THEN CAST(
                             (v.facturado_anio_actual - v.facturado_anio_anterior) * 100.0 /
                              v.facturado_anio_anterior
                         AS decimal(8,2))
                         ELSE NULL END AS variacion_porc,
                    ISNULL(c.p1_activos,        0) AS p1_activos,
                    ISNULL(c.p2_en_riesgo,      0) AS p2_en_riesgo,
                    ISNULL(c.p3_recuperar,      0) AS p3_recuperar,
                    ISNULL(c.p4_largo_plazo,    0) AS p4_largo_plazo,
                    ISNULL(c.valor_a_rescatar_p2, 0) AS valor_a_rescatar_p2,
                    ISNULL(c.valor_potencial_p3,  0) AS valor_potencial_p3
                FROM ventas_reales v
                LEFT JOIN cartera c
                    ON  c.COMPANIA = v.COMPANIA
                    AND UPPER(LTRIM(RTRIM(c.NOMBRE_VENDEDOR))) = UPPER(LTRIM(RTRIM(v.NOMBRE_VENDEDOR)))
                ORDER BY v.COMPANIA, v.facturado_anio_actual DESC
            ";

            return collect(DB::connection('erp_contiflex')->select($sql))
                ->map(fn ($r) => (array) $r)
                ->toArray();
        } catch (Throwable $e) {
            throw ERPConnectionException::queryFailed($e->getMessage());
        }
    }

    public function countClientesHuerfanos(int $compania, array $nitsExcluir = []): int
    {
        try {
            return DB::connection('erp_contiflex')
                ->table('dbo.CRM_Consolidado_Ventas_cliente')
                ->where('COMPANIA', $compania)
                ->where(fn ($q) => $q
                    ->whereNull('NOMBRE_VENDEDOR')
                    ->orWhereRaw("LTRIM(RTRIM(NOMBRE_VENDEDOR)) = ''")
                    ->orWhere('NOMBRE_VENDEDOR', 'like', '%VACANTE%')
                )
                ->where('DIAS_DESDE_ULTIMA_COMPRA', '>=', 365)
                ->where('VLR_NETO_FACTURADO', '>', 0)
                ->when($nitsExcluir, fn ($q) => $q->whereNotIn('NIT', $nitsExcluir))
                ->count();
        } catch (Throwable $e) {
            return 0;
        }
    }

    public function clientesHuerfanos(int $compania, array $nitsExcluir = [], int $limite = 100): array
    {
        try {
            return DB::connection('erp_contiflex')
                ->table('dbo.CRM_Consolidado_Ventas_cliente')
                ->where('COMPANIA', $compania)
                ->where(fn ($q) => $q
                    ->whereNull('NOMBRE_VENDEDOR')
                    ->orWhereRaw("LTRIM(RTRIM(NOMBRE_VENDEDOR)) = ''")
                    ->orWhere('NOMBRE_VENDEDOR', 'like', '%VACANTE%')
                )
                ->where('DIAS_DESDE_ULTIMA_COMPRA', '>=', 365)
                ->where('VLR_NETO_FACTURADO', '>', 0)
                ->when($nitsExcluir, fn ($q) => $q->whereNotIn('NIT', $nitsExcluir))
                ->orderByDesc('VLR_NETO_FACTURADO')
                ->limit($limite)
                ->get(['NIT', 'RAZON_SOCIAL', 'CIUDAD', 'VLR_NETO_FACTURADO', 'DIAS_DESDE_ULTIMA_COMPRA', 'ULTIMA_FACTURA'])
                ->map(fn ($r) => (array) $r)
                ->toArray();
        } catch (Throwable $e) {
            throw ERPConnectionException::queryFailed($e->getMessage());
        }
    }

    public function clientesPorVendedor(
        string $nombreVendedor,
        ?string $buscar = null,
        int $pagina = 1,
        int $porPagina = 20
    ): array {
        try {
            $base = DB::connection('erp_contiflex')
                ->table('dbo.vw_CRM_Clientes_Prioritarios')
                ->where('NOMBRE_VENDEDOR', $nombreVendedor)
                ->when($buscar, fn ($q) => $q->where(function ($q2) use ($buscar) {
                    $q2->where('RAZON_SOCIAL', 'like', "%{$buscar}%")
                       ->orWhere('NIT', 'like', "%{$buscar}%");
                }));

            $total = (clone $base)->count();

            $data = $base
                ->orderBy('RAZON_SOCIAL')
                ->skip(($pagina - 1) * $porPagina)
                ->take($porPagina)
                ->get([
                    'NIT', 'RAZON_SOCIAL', 'CIUDAD',
                    'FACTURADO_ANIO_ACTUAL', 'DIAS_DESDE_ULTIMA_COMPRA',
                    'HORIZONTE_PRESUPUESTO', 'ACCION_PRESUPUESTAL',
                ])
                ->map(fn ($r) => (array) $r)
                ->toArray();

            return compact('data', 'total', 'pagina', 'porPagina');
        } catch (Throwable $e) {
            throw ERPConnectionException::queryFailed($e->getMessage());
        }
    }

    public function todosClientesPorVendedor(string $nombreVendedor): array
    {
        try {
            return DB::connection('erp_contiflex')
                ->table('dbo.vw_CRM_Clientes_Prioritarios')
                ->where('NOMBRE_VENDEDOR', $nombreVendedor)
                ->orderBy('RAZON_SOCIAL')
                ->get(['NIT', 'RAZON_SOCIAL', 'CIUDAD'])
                ->unique('NIT')
                ->values()
                ->map(fn ($r) => (array) $r)
                ->toArray();
        } catch (Throwable $e) {
            throw ERPConnectionException::queryFailed($e->getMessage());
        }
    }

    public function ventasMensualesPorNit(string $nit): array
    {
        try {
            $rows = DB::connection('erp_contiflex')
                ->table('dbo.vw_CRM_Ventas_Mensuales_cliente')
                ->where('NIT', $nit)
                ->whereRaw('ANIO >= YEAR(GETDATE()) - 4')
                ->select([
                    'ANIO', 'MES', 'ANIO_MES', 'TRIMESTRE', 'ANIO_TRIMESTRE',
                    DB::raw('SUM(CAST(VLR_NETO_FACTURADO AS float)) AS total'),
                ])
                ->groupBy('ANIO', 'MES', 'ANIO_MES', 'TRIMESTRE', 'ANIO_TRIMESTRE')
                ->orderBy('ANIO')
                ->orderBy('MES')
                ->get();

            return $this->construirDatasetsSiesa($rows);
        } catch (Throwable $e) {
            throw ERPConnectionException::queryFailed($e->getMessage());
        }
    }

    private function construirDatasetsSiesa(\Illuminate\Support\Collection $rows): array
    {
        $mensual = [];
        for ($i = 11; $i >= 0; $i--) {
            $fecha     = now()->subMonths($i);
            $anioMes   = $fecha->format('Y-m');
            $row       = $rows->firstWhere('ANIO_MES', $anioMes);
            $mensual[] = [
                'label' => ucfirst($fecha->locale('es')->isoFormat('MMM YY')),
                'total' => $row ? (float) $row->total : 0.0,
            ];
        }

        $trimestral = [];
        for ($i = 7; $i >= 0; $i--) {
            $fecha        = now()->subQuarters($i);
            $y            = $fecha->year;
            $q            = (int) ceil($fecha->month / 3);
            $key          = "{$y}-Q{$q}";
            $total        = $rows
                ->filter(fn ($r) => $r->ANIO_TRIMESTRE === $key)
                ->sum(fn ($r) => (float) $r->total);
            $trimestral[] = ['label' => "Q{$q} {$y}", 'total' => $total];
        }

        $anual = [];
        for ($i = 4; $i >= 0; $i--) {
            $year    = now()->year - $i;
            $total   = $rows
                ->filter(fn ($r) => (int) $r->ANIO === $year)
                ->sum(fn ($r) => (float) $r->total);
            $anual[] = ['label' => (string) $year, 'total' => $total];
        }

        return compact('mensual', 'trimestral', 'anual');
    }

    public function facturasPorNit(string $nit, int $limite = 20): array
    {
        try {
            $sql = "
                SELECT TOP {$limite}
                    f.ROWID_FACTURA,
                    f.COMPANIA,
                    f.CONCEPTO,
                    f.TIPO,
                    f.NUM_DOCTO,
                    CONVERT(varchar(10), f.FECHA, 23) AS FECHA,
                    f.COD_VENDEDOR,
                    COUNT(d.RENGLON)       AS NUM_ITEMS,
                    ISNULL(SUM(d.VLR_NETO), 0) AS VLR_NETO
                FROM dbo.CRM_stg_facturas_venta f
                INNER JOIN clientes c ON c.NIT = f.NIT
                LEFT JOIN dbo.CRM_stg_facturas_venta_detalle d ON d.ROWID_FACTURA = f.ROWID_FACTURA
                WHERE f.NIT = ?
                GROUP BY f.ROWID_FACTURA, f.COMPANIA, f.CONCEPTO, f.TIPO,
                         f.NUM_DOCTO, f.FECHA, f.COD_VENDEDOR
                ORDER BY f.FECHA DESC
            ";

            return collect(DB::connection('erp_contiflex')->select($sql, [$nit]))
                ->map(fn ($r) => (array) $r)
                ->toArray();
        } catch (Throwable $e) {
            throw ERPConnectionException::queryFailed($e->getMessage());
        }
    }

    public function detalleFactura(int $rowidFactura): array
    {
        try {
            $sql = "
                SELECT
                    d.RENGLON,
                    d.COD_PRODUCTO,
                    i.NOMBRE_PRODUCTO,
                    d.REFERENCIA,
                    d.UNIDAD,
                    d.CANTIDAD,
                    d.PRECIO_UNIT,
                    d.VLR_BRUTO,
                    d.VLR_DSCTO,
                    d.VLR_IMP,
                    d.VLR_NETO
                FROM dbo.CRM_stg_facturas_venta_detalle d
                INNER JOIN dbo.CRM_dim_item i ON i.COD_PRODUCTO = d.COD_PRODUCTO
                WHERE d.ROWID_FACTURA = ?
                ORDER BY d.RENGLON
            ";

            return collect(DB::connection('erp_contiflex')->select($sql, [$rowidFactura]))
                ->map(fn ($r) => (array) $r)
                ->toArray();
        } catch (Throwable $e) {
            throw ERPConnectionException::queryFailed($e->getMessage());
        }
    }

    public function isAvailable(): bool
    {
        try {
            DB::connection('erp_contiflex')->getPdo();

            return true;
        } catch (Throwable) {
            return false;
        }
    }
}
