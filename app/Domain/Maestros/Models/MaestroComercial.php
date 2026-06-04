<?php

declare(strict_types=1);

namespace App\Domain\Maestros\Models;

use Illuminate\Database\Eloquent\Model;

class MaestroComercial extends Model
{
    protected $table = 'sf_maestros_comerciales';

    protected $fillable = [
        'tipo',
        'nombre',
        'slug',
        'color',
        'icono',
        'orden',
        'activo',
    ];

    protected $casts = [
        'activo' => 'boolean',
        'orden'  => 'integer',
    ];

    public function scopeActivos($query)
    {
        return $query->where('activo', true);
    }

    public function scopeDesTipo($query, string $tipo)
    {
        return $query->where('tipo', $tipo);
    }

    public function scopeOrdenados($query)
    {
        return $query->orderBy('orden');
    }
}
