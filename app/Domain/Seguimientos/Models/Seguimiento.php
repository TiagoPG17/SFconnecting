<?php

declare(strict_types=1);

namespace App\Domain\Seguimientos\Models;

use App\Domain\Clientes\Models\Cliente;
use App\Domain\Clientes\Models\Contacto;
use App\Domain\Prospectos\Models\Prospecto;
use App\Models\User;
use Database\Factories\SeguimientoFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Seguimiento extends Model
{
    /** @use HasFactory<SeguimientoFactory> */
    use HasFactory;

    protected $fillable = [
        'cliente_id',
        'prospecto_id',
        'user_id',
        'contacto_id',
        'tipo',
        'resultado',
        'descripcion',
        'fecha_seguimiento',
        'proxima_fecha',
        'proxima_tipo',
    ];

    protected $casts = [
        'fecha_seguimiento' => 'datetime',
        'proxima_fecha'     => 'datetime',
    ];

    public function cliente(): BelongsTo
    {
        return $this->belongsTo(Cliente::class);
    }

    public function prospecto(): BelongsTo
    {
        return $this->belongsTo(Prospecto::class);
    }

    public function asesor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function contacto(): BelongsTo
    {
        return $this->belongsTo(Contacto::class);
    }

    public function scopePendientes($query)
    {
        return $query->where('resultado', 'pendiente');
    }

    public function scopeDelAsesor($query, int $asesorId)
    {
        return $query->where('user_id', $asesorId);
    }

    public function tieneSeguimientoFuturo(): bool
    {
        return $this->proxima_fecha !== null && $this->proxima_fecha->isFuture();
    }

    protected static function newFactory(): SeguimientoFactory
    {
        return SeguimientoFactory::new();
    }
}
