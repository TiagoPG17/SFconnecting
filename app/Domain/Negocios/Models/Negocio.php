<?php

declare(strict_types=1);

namespace App\Domain\Negocios\Models;

use App\Domain\Clientes\Models\Cliente;
use App\Domain\Maestros\Models\MaestroComercial;
use App\Domain\Pipeline\Models\PipelineEstado;
use App\Domain\Prospectos\Models\Prospecto;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Negocio extends Model
{
    use SoftDeletes;

    protected $table = 'sf_negocios';

    protected $fillable = [
        'prospecto_id',
        'cliente_id',
        'pipeline_estado_id',
        'tipo_negocio_id',
        'nombre_negocio',
        'descripcion',
        'valor_estimado',
        'probabilidad_cierre',
        'fecha_estimada_cierre',
        'fecha_cierre_real',
        'motivo_perdida_id',
        'observacion_perdida',
        'asesor_id',
        'compania',
        'activo',
    ];

    protected $casts = [
        'valor_estimado'       => 'decimal:2',
        'probabilidad_cierre'  => 'integer',
        'fecha_estimada_cierre' => 'date',
        'fecha_cierre_real'    => 'date',
        'compania'             => 'integer',
        'activo'               => 'boolean',
        'deleted_at'           => 'datetime',
    ];

    public function prospecto(): BelongsTo
    {
        return $this->belongsTo(Prospecto::class, 'prospecto_id');
    }

    public function cliente(): BelongsTo
    {
        return $this->belongsTo(Cliente::class, 'cliente_id');
    }

    public function pipelineEstado(): BelongsTo
    {
        return $this->belongsTo(PipelineEstado::class, 'pipeline_estado_id');
    }

    public function tipoNegocio(): BelongsTo
    {
        return $this->belongsTo(MaestroComercial::class, 'tipo_negocio_id');
    }

    public function motivoPerdida(): BelongsTo
    {
        return $this->belongsTo(MaestroComercial::class, 'motivo_perdida_id');
    }

    public function asesor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'asesor_id');
    }

    public function auditoria(): MorphMany
    {
        return $this->morphMany(AuditoriaPipeline::class, 'auditable');
    }

    public function probabilidadEfectiva(): int
    {
        return $this->probabilidad_cierre
            ?? $this->pipelineEstado?->porcentaje_cierre
            ?? 0;
    }

    public function valorForecast(): float
    {
        return (float) $this->valor_estimado * ($this->probabilidadEfectiva() / 100);
    }

    public function companiaNombre(): ?string
    {
        return match ($this->compania) {
            1       => 'Formacol',
            2       => 'Contiflex',
            default => null,
        };
    }

    public function companiaSiglas(): ?string
    {
        return match ($this->compania) {
            1       => 'FC',
            2       => 'CX',
            default => null,
        };
    }

    public function scopeActivos($query)
    {
        return $query->where('sf_negocios.activo', true);
    }

    public function scopeDelAsesor($query, int $asesorId)
    {
        return $query->where('sf_negocios.asesor_id', $asesorId);
    }

    public function estaGanado(): bool
    {
        return $this->pipelineEstado?->es_ganado ?? false;
    }

    public function estaPerdido(): bool
    {
        return $this->pipelineEstado?->es_perdido ?? false;
    }
}
