<?php

declare(strict_types=1);

namespace App\Domain\SolicitudesCredito\Models;

use App\Domain\Clientes\Models\Cliente;
use App\Domain\Negocios\Models\AuditoriaPipeline;
use App\Domain\Negocios\Models\Negocio;
use App\Domain\Pipeline\Models\PipelineEstado;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class SolicitudCredito extends Model
{
    use SoftDeletes;

    protected $table = 'sf_solicitudes_credito';

    protected $fillable = [
        'negocio_id',
        'cliente_id',
        'pipeline_estado_id',
        'asesor_id',
        'revisado_por',
        'monto_solicitado',
        'plazo_solicitado_dias',
        'justificacion',
        'dossier_erp',
        'comentario_revision',
        'condiciones',
        'revisado_en',
    ];

    protected $casts = [
        'monto_solicitado'      => 'decimal:2',
        'plazo_solicitado_dias' => 'integer',
        'dossier_erp'           => 'array',
        'revisado_en'           => 'datetime',
        'deleted_at'            => 'datetime',
    ];

    public function negocio(): BelongsTo
    {
        return $this->belongsTo(Negocio::class, 'negocio_id');
    }

    public function cliente(): BelongsTo
    {
        return $this->belongsTo(Cliente::class, 'cliente_id');
    }

    public function pipelineEstado(): BelongsTo
    {
        return $this->belongsTo(PipelineEstado::class, 'pipeline_estado_id');
    }

    public function asesor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'asesor_id');
    }

    public function revisor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'revisado_por');
    }

    public function auditoria(): MorphMany
    {
        return $this->morphMany(AuditoriaPipeline::class, 'auditable');
    }

    public function estaAprobada(): bool
    {
        return $this->pipelineEstado?->es_ganado ?? false;
    }

    public function estaRechazada(): bool
    {
        return $this->pipelineEstado?->es_perdido ?? false;
    }

    public function estaFinalizada(): bool
    {
        return $this->pipelineEstado?->es_final ?? false;
    }
}
