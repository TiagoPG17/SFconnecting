<?php

declare(strict_types=1);

namespace App\Domain\Prospectos\Models;

use App\Domain\Clientes\Models\Cliente;
use App\Domain\Maestros\Models\MaestroComercial;
use App\Domain\Pipeline\Models\PipelineEstado;
use App\Models\User;
use Database\Factories\ProspectoFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Prospecto extends Model
{
    /** @use HasFactory<ProspectoFactory> */
    use HasFactory, SoftDeletes;

    protected $table = 'sf_prospectos';

    protected $fillable = [
        'codigo',
        'empresa',
        'contacto',
        'email',
        'telefono',
        'origen_id',
        'estado_pipeline_id',
        'prioridad_id',
        'valor_estimado',
        'probabilidad_cierre',
        'fecha_proximo_contacto',
        'asesor_id',
        'observaciones',
        'convertido_cliente_id',
        'fecha_conversion',
        'convertido_por',
        'activo',
    ];

    protected $casts = [
        'valor_estimado'        => 'decimal:2',
        'probabilidad_cierre'   => 'integer',
        'fecha_proximo_contacto' => 'date',
        'fecha_conversion'      => 'datetime',
        'activo'                => 'boolean',
        'deleted_at'            => 'datetime',
    ];

    public function estadoPipeline(): BelongsTo
    {
        return $this->belongsTo(PipelineEstado::class, 'estado_pipeline_id');
    }

    public function origen(): BelongsTo
    {
        return $this->belongsTo(MaestroComercial::class, 'origen_id');
    }

    public function prioridad(): BelongsTo
    {
        return $this->belongsTo(MaestroComercial::class, 'prioridad_id');
    }

    public function asesor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'asesor_id');
    }

    public function clienteConvertido(): BelongsTo
    {
        return $this->belongsTo(Cliente::class, 'convertido_cliente_id');
    }

    public function convertidoPor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'convertido_por');
    }

    public function auditoria(): MorphMany
    {
        return $this->morphMany(
            \App\Domain\Negocios\Models\AuditoriaPipeline::class,
            'auditable'
        );
    }

    public function estaConvertido(): bool
    {
        return $this->convertido_cliente_id !== null;
    }

    public function probabilidadEfectiva(): int
    {
        return $this->probabilidad_cierre
            ?? $this->estadoPipeline?->porcentaje_cierre
            ?? 0;
    }

    protected static function newFactory(): ProspectoFactory
    {
        return ProspectoFactory::new();
    }
}
