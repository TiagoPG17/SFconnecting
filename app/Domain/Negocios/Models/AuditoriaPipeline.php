<?php

declare(strict_types=1);

namespace App\Domain\Negocios\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class AuditoriaPipeline extends Model
{
    protected $table = 'sf_auditoria_pipeline';

    public $timestamps = true;
    public const UPDATED_AT = null;

    protected $fillable = [
        'auditable_type',
        'auditable_id',
        'evento',
        'estado_anterior',
        'estado_nuevo',
        'datos_anteriores',
        'datos_nuevos',
        'usuario_id',
    ];

    protected $casts = [
        'datos_anteriores' => 'array',
        'datos_nuevos'     => 'array',
    ];

    public function auditable(): MorphTo
    {
        return $this->morphTo();
    }

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'usuario_id');
    }
}
