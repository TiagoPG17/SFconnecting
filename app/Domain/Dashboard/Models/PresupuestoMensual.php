<?php

declare(strict_types=1);

namespace App\Domain\Dashboard\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PresupuestoMensual extends Model
{
    protected $table = 'sf_presupuesto_mensual';

    protected $fillable = ['presupuesto_id', 'mes', 'valor'];

    protected $casts = [
        'valor' => 'decimal:2',
        'mes'   => 'integer',
    ];

    public function presupuesto(): BelongsTo
    {
        return $this->belongsTo(Presupuesto::class);
    }
}
