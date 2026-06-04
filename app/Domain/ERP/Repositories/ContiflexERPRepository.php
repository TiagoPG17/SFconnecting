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
            return DB::connection('erp_contiflex')
                ->table('clientes')
                ->where('nit', $nit)
                ->first()?->toArray();
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

    public function clientesAtencionInmediata(int $limite = 20): array
    {
        try {
            return DB::connection('erp_contiflex')
                ->table('dbo.vw_CRM_Clientes_Prioritarios')
                ->where('HORIZONTE_PRESUPUESTO', 'P1 - PRESUPUESTO ACTIVO')
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

    public function clientesRescate(int $limite = 20): array
    {
        try {
            return DB::connection('erp_contiflex')
                ->table('dbo.vw_CRM_Clientes_Prioritarios')
                ->whereIn('HORIZONTE_PRESUPUESTO', [
                    'P2 - PRESUPUESTO EN RIESGO',
                    'P3 - PRESUPUESTO PASADO (RECUPERAR)',
                ])
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

    public function panoramaGerencial(): array
    {
        try {
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
                GROUP BY COMPANIA, NOMBRE_VENDEDOR
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

    public function clientesEnFuga(int $limite = 50): array
    {
        try {
            return DB::connection('erp_contiflex')
                ->table('dbo.CRM_Consolidado_Ventas_cliente')
                ->whereBetween('DIAS_DESDE_ULTIMA_COMPRA', [90, 180])
                ->where('VLR_NETO_FACTURADO', '>', 100_000_000)
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

    public function clientesExpansion(int $limite = 50): array
    {
        try {
            return DB::connection('erp_contiflex')
                ->table('dbo.CRM_Consolidado_Ventas_cliente')
                ->where('DIAS_DESDE_ULTIMA_COMPRA', '<=', 30)
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

    public function clientesPresupuestoActivo(int $limite = 30): array
    {
        try {
            return DB::connection('erp_contiflex')
                ->table('dbo.vw_CRM_Clientes_Prioritarios')
                ->where('HORIZONTE_PRESUPUESTO', 'P1 - PRESUPUESTO ACTIVO')
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

    public function clientesPresupuestoEnRiesgo(int $limite = 30): array
    {
        try {
            return DB::connection('erp_contiflex')
                ->table('dbo.vw_CRM_Clientes_Prioritarios')
                ->where('HORIZONTE_PRESUPUESTO', 'P2 - PRESUPUESTO EN RIESGO')
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

    public function clientesPresupuestoRecuperar(int $limite = 30): array
    {
        try {
            return DB::connection('erp_contiflex')
                ->table('dbo.vw_CRM_Clientes_Prioritarios')
                ->where('HORIZONTE_PRESUPUESTO', 'P3 - PRESUPUESTO PASADO (RECUPERAR)')
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

    public function clientesLargoPlazo(int $limite = 30): array
    {
        try {
            return DB::connection('erp_contiflex')
                ->table('dbo.vw_CRM_Clientes_Prioritarios')
                ->where('HORIZONTE_PRESUPUESTO', 'P4 - FUERA DE PRESUPUESTO')
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

    public function panoramaPresupuestal(): array
    {
        try {
            $sql = "
                SELECT
                    COMPANIA,
                    NOMBRE_VENDEDOR,
                    SUM(FACTURADO_ANIO_ACTUAL)   AS facturado_anio_actual,
                    SUM(FACTURADO_ANIO_ANTERIOR)  AS facturado_anio_anterior,
                    CASE WHEN SUM(FACTURADO_ANIO_ANTERIOR) > 0
                         THEN CAST(
                             (SUM(FACTURADO_ANIO_ACTUAL) - SUM(FACTURADO_ANIO_ANTERIOR)) * 100.0 /
                              SUM(FACTURADO_ANIO_ANTERIOR)
                         AS decimal(8,2))
                         ELSE NULL END AS variacion_porc,
                    SUM(CASE WHEN HORIZONTE_PRESUPUESTO = 'P1 - PRESUPUESTO ACTIVO'
                             THEN 1 ELSE 0 END) AS p1_activos,
                    SUM(CASE WHEN HORIZONTE_PRESUPUESTO = 'P2 - PRESUPUESTO EN RIESGO'
                             THEN 1 ELSE 0 END) AS p2_en_riesgo,
                    SUM(CASE WHEN HORIZONTE_PRESUPUESTO = 'P3 - PRESUPUESTO PASADO (RECUPERAR)'
                             THEN 1 ELSE 0 END) AS p3_recuperar,
                    SUM(CASE WHEN HORIZONTE_PRESUPUESTO = 'P4 - FUERA DE PRESUPUESTO'
                             THEN 1 ELSE 0 END) AS p4_largo_plazo,
                    SUM(CASE WHEN HORIZONTE_PRESUPUESTO = 'P2 - PRESUPUESTO EN RIESGO'
                             THEN IMPACTO_PRESUPUESTO_ESTIMADO ELSE 0 END) AS valor_a_rescatar_p2,
                    SUM(CASE WHEN HORIZONTE_PRESUPUESTO = 'P3 - PRESUPUESTO PASADO (RECUPERAR)'
                             THEN FACTURADO_ANIO_ANTERIOR ELSE 0 END) AS valor_potencial_p3
                FROM dbo.vw_CRM_Clientes_Prioritarios
                GROUP BY COMPANIA, NOMBRE_VENDEDOR
                HAVING MIN(DIAS_DESDE_ULTIMA_COMPRA) <= 90
                ORDER BY COMPANIA, SUM(FACTURADO_ANIO_ACTUAL) DESC
            ";

            return collect(DB::connection('erp_contiflex')->select($sql))
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
