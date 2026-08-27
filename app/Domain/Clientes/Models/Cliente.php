<?php

declare(strict_types=1);

namespace App\Domain\Clientes\Models;

use App\Models\User;
use Database\Factories\ClienteFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Cliente extends Model
{
    /** @use HasFactory<ClienteFactory> */
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'razon_social',
        'nit',
        'compania',
        'email',
        'telefono',
        'ciudad',
        'direccion',
        'estado',
        'notas',
        'user_id',
        'datos_carga',
        'revisado_contabilidad_en',
        'revisado_contabilidad_por',
    ];

    protected $casts = [
        'deleted_at'               => 'datetime',
        'compania'                 => 'integer',
        'datos_carga'              => 'array',
        'revisado_contabilidad_en' => 'datetime',
    ];

    public function asesor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function revisadoContabilidadPor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'revisado_contabilidad_por');
    }

    public function contactos(): HasMany
    {
        return $this->hasMany(Contacto::class);
    }

    public function seguimientos(): HasMany
    {
        return $this->hasMany(\App\Domain\Seguimientos\Models\Seguimiento::class);
    }

    public function scopeDelAsesor($query, int $asesorId)
    {
        return $query->where('clientes.user_id', $asesorId);
    }

    public function scopeActivos($query)
    {
        return $query->where('clientes.estado', 'activo');
    }

    public function scopePendientesContabilidad($query, int $compania)
    {
        return $query->where('clientes.compania', $compania)
            ->whereNull('clientes.revisado_contabilidad_en')
            ->whereNotNull('clientes.datos_carga');
    }

    public function estaActivo(): bool
    {
        return $this->estado === 'activo';
    }

    public function estaInactivo(): bool
    {
        return $this->estado === 'inactivo';
    }

    public function pendienteContabilidad(): bool
    {
        return is_null($this->revisado_contabilidad_en) && ! is_null($this->datos_carga);
    }

    public function companiaNombre(): ?string
    {
        return match ($this->compania) {
            1       => 'Formacol',
            2       => 'Contiflex',
            default => null,
        };
    }

    protected static function newFactory(): ClienteFactory
    {
        return ClienteFactory::new();
    }
}
