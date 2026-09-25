<?php

declare(strict_types=1);

namespace App\Domain\SolicitudesCotizacion\Models;

use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Vista de solo lectura sobre dbo.CRM_stg_Solicitudes_Cotizacion (BD Contiflex),
 * materializada por hora desde SGP vía sp_CRM_Actualizar_Solicitudes_Cotizacion.
 * Nunca se borra físicamente: usa estado_registro (ACTIVO/ELIMINADO) + fecha_baja.
 */
class SolicitudCotizacion extends Model
{
    protected $connection = 'erp_contiflex';

    protected $table = 'dbo.CRM_stg_Solicitudes_Cotizacion';

    protected $primaryKey = 'nro_solicitud';

    protected $keyType = 'string';

    public $incrementing = false;

    public $timestamps = false;

    protected $casts = [
        'fecha_solicitud'    => 'date',
        'nro_opciones'       => 'integer',
        'escalas'            => 'integer',
        'escalas_con_precio' => 'integer',
        'partes'             => 'integer',
        'fecha_baja'         => 'datetime',
        'fecha_sync'         => 'datetime',
    ];

    /**
     * Ojo: no se llama "escalas" porque la tabla ya tiene una columna real con ese
     * nombre (el conteo) — Eloquent resolvería el atributo entero, no la relación.
     */
    public function escalasDetalle(): HasMany
    {
        return $this->hasMany(SolicitudCotizacionEscala::class, 'nro_solicitud', 'nro_solicitud');
    }

    public function scopeActivo($query)
    {
        return $query->where('estado_registro', 'ACTIVO');
    }

    public function scopeEliminado($query)
    {
        return $query->where('estado_registro', 'ELIMINADO');
    }

    public function scopeSinClienteAsignado($query)
    {
        return $query->whereIn('situacion_cliente', ['VARIOS', 'NO REGISTRADO']);
    }

    public function scopeClienteAsignado($query)
    {
        return $query->where('situacion_cliente', 'CLIENTE ASIGNADO');
    }

    public function scopeDelVendedor($query, ?string $vendedor)
    {
        return $vendedor ? $query->where('vendedor', $vendedor) : $query;
    }

    public function scopeBuscarCliente($query, ?string $termino)
    {
        if (! $termino) {
            return $query;
        }

        return $query->where(function ($q) use ($termino) {
            $q->where('nit', $termino)->orWhere('cliente', 'like', '%' . $termino . '%');
        });
    }

    public function scopeMesActual($query)
    {
        return $query->whereYear('fecha_solicitud', now()->year)
            ->whereMonth('fecha_solicitud', now()->month);
    }

    public function scopeDesde($query, CarbonInterface $fecha)
    {
        // "Ymd" (sin separadores) es el único formato que SQL Server interpreta sin
        // ambigüedad sin importar el idioma/DATEFORMAT de la sesión — pasar un Carbon
        // crudo o "Y-m-d H:i:s" revienta con "conversión de nvarchar a datetime".
        return $query->where('fecha_solicitud', '>=', $fecha->format('Ymd'));
    }
}
