<?php

declare(strict_types=1);

namespace App\Domain\SolicitudesCotizacion\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Vista de solo lectura sobre dbo.CRM_stg_Solicitudes_Cotizacion_Escala (BD Contiflex).
 * Grano real: una fila por (nro_solicitud, escala) — Eloquent no soporta llave
 * compuesta nativa, así que $primaryKey solo se usa para relaciones, nunca con
 * find()/save() sobre este modelo (es de solo lectura).
 */
class SolicitudCotizacionEscala extends Model
{
    protected $connection = 'erp_contiflex';

    protected $table = 'dbo.CRM_stg_Solicitudes_Cotizacion_Escala';

    protected $primaryKey = 'nro_solicitud';

    protected $keyType = 'string';

    public $incrementing = false;

    public $timestamps = false;

    protected $casts = [
        'escala'               => 'integer',
        'fecha_solicitud'      => 'date',
        'costo_unitario_cop'   => 'decimal:2',
        'precio_unitario'      => 'decimal:4',
        'trm'                  => 'decimal:2',
        'precio_unitario_cop'  => 'decimal:2',
        'precio_sobre_costo'   => 'decimal:2',
        'valor_total_escala'   => 'decimal:2',
        'fecha_precio'         => 'date',
        'veces_cotizada'       => 'integer',
        'fecha_baja'           => 'datetime',
        'fecha_sync'           => 'datetime',
    ];

    public function solicitud(): BelongsTo
    {
        return $this->belongsTo(SolicitudCotizacion::class, 'nro_solicitud', 'nro_solicitud');
    }

    public function scopeActivo($query)
    {
        return $query->where('estado_registro', 'ACTIVO');
    }

    public function scopeConPrecio($query)
    {
        return $query->where('situacion_escala', 'CON PRECIO');
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
}
