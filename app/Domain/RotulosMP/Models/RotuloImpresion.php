<?php

declare(strict_types=1);

namespace App\Domain\RotulosMP\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RotuloImpresion extends Model
{
    protected $table = 'sf_rotulos_mp_impresiones';

    protected $fillable = [
        'documento', 'renglon_id', 'referencia', 'lote', 'unidad', 'cantidad_total',
        'peso_costal', 'num_rotulos', 'impresora', 'impresora_ip', 'user_id',
    ];

    protected $casts = [
        'renglon_id'  => 'integer',
        'num_rotulos' => 'integer',
    ];

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
