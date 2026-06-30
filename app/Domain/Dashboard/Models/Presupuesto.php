<?php

declare(strict_types=1);

namespace App\Domain\Dashboard\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Presupuesto extends Model
{
    protected $table = 'sf_presupuesto';

    protected $fillable = ['asesor_id', 'compania', 'anio', 'presupuesto'];

    protected $casts = [
        'presupuesto' => 'decimal:2',
        'anio'        => 'integer',
        'compania'    => 'integer',
    ];

    public function asesor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'asesor_id');
    }

    public function meses(): HasMany
    {
        return $this->hasMany(PresupuestoMensual::class)->orderBy('mes');
    }
}
