<?php

declare(strict_types=1);

namespace App\Domain\Dashboard\Models;

use App\Domain\Clientes\Models\Cliente;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ReemplazoClienteMovido extends Model
{
    protected $table = 'sf_reemplazo_clientes_movidos';

    protected $fillable = [
        'vendedor_equivalencia_id',
        'cliente_id',
        'titular_user_id',
        'revertido_en',
    ];

    protected $casts = [
        'revertido_en' => 'datetime',
    ];

    public function cliente(): BelongsTo
    {
        return $this->belongsTo(Cliente::class);
    }
}
