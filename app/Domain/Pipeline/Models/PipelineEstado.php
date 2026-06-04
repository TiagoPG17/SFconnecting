<?php

declare(strict_types=1);

namespace App\Domain\Pipeline\Models;

use Database\Factories\PipelineEstadoFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PipelineEstado extends Model
{
    /** @use HasFactory<PipelineEstadoFactory> */
    use HasFactory;
    protected $table = 'sf_pipeline_estados';

    protected $fillable = [
        'nombre',
        'slug',
        'tipo',
        'color',
        'icono',
        'orden',
        'porcentaje_cierre',
        'es_final',
        'es_ganado',
        'es_perdido',
        'activo',
    ];

    protected $casts = [
        'porcentaje_cierre' => 'integer',
        'es_final'          => 'boolean',
        'es_ganado'         => 'boolean',
        'es_perdido'        => 'boolean',
        'activo'            => 'boolean',
        'orden'             => 'integer',
    ];

    public function prospectos(): HasMany
    {
        return $this->hasMany(
            \App\Domain\Prospectos\Models\Prospecto::class,
            'estado_pipeline_id'
        );
    }

    public function negocios(): HasMany
    {
        return $this->hasMany(
            \App\Domain\Negocios\Models\Negocio::class,
            'pipeline_estado_id'
        );
    }

    public function scopeActivos($query)
    {
        return $query->where('activo', true);
    }

    public function scopeNegocio($query)
    {
        return $query->where('tipo', 'negocio')->where('activo', true);
    }

    public function scopeDesTipo($query, string $tipo)
    {
        return $query->where('tipo', $tipo);
    }

    public function scopeOrdenados($query)
    {
        return $query->orderBy('orden');
    }

    public function esGanado(): bool
    {
        return $this->es_ganado;
    }

    public function esPerdido(): bool
    {
        return $this->es_perdido;
    }

    public function esFinal(): bool
    {
        return $this->es_final;
    }

    protected static function newFactory(): PipelineEstadoFactory
    {
        return PipelineEstadoFactory::new();
    }
}
