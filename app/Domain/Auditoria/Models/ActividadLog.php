<?php

declare(strict_types=1);

namespace App\Domain\Auditoria\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ActividadLog extends Model
{
    public $timestamps = false;

    protected $table = 'actividad_log';

    protected $fillable = [
        'user_id',
        'accion',
        'modulo',
        'descripcion',
        'url',
        'metodo',
        'ip',
        'user_agent',
        'created_at',
    ];

    protected $casts = [
        'created_at' => 'datetime',
    ];

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public static function registrar(
        string $accion,
        string $modulo,
        ?string $descripcion = null,
        ?int $userId = null,
    ): void {
        $request = request();

        static::create([
            'user_id'     => $userId ?? auth()->id(),
            'accion'      => $accion,
            'modulo'      => $modulo,
            'descripcion' => $descripcion,
            'url'         => $request?->fullUrl(),
            'metodo'      => $request?->method(),
            'ip'          => $request?->ip(),
            'user_agent'  => substr($request?->userAgent() ?? '', 0, 300),
            'created_at'  => now(),
        ]);
    }
}
