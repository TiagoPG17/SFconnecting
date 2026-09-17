<?php

declare(strict_types=1);

namespace App\Domain\Dashboard\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VendedorEquivalencia extends Model
{
    protected $table = 'sf_vendedor_equivalencia';

    protected $fillable = [
        'asesor_id',
        'compania',
        'cod_vendedor_siesa',
        'rowid_vendedor_siesa',
        'nombre_vendedor',
        'activo',
        'es_reemplazo',
    ];

    protected $casts = [
        'activo'       => 'boolean',
        'compania'     => 'integer',
        'es_reemplazo' => 'boolean',
    ];

    public function asesor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'asesor_id');
    }
}
