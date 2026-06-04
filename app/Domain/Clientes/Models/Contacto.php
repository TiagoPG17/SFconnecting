<?php

declare(strict_types=1);

namespace App\Domain\Clientes\Models;

use Database\Factories\ContactoFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Contacto extends Model
{
    /** @use HasFactory<ContactoFactory> */
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'cliente_id',
        'nombre',
        'cargo',
        'email',
        'telefono',
        'principal',
    ];

    protected $casts = [
        'principal'  => 'boolean',
        'deleted_at' => 'datetime',
    ];

    public function cliente(): BelongsTo
    {
        return $this->belongsTo(Cliente::class);
    }

    protected static function newFactory(): ContactoFactory
    {
        return ContactoFactory::new();
    }
}
