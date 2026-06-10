<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasApiTokens, HasFactory, HasRoles, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'activo',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password'          => 'hashed',
            'activo'            => 'boolean',
        ];
    }

    public function clientes(): HasMany
    {
        return $this->hasMany(\App\Domain\Clientes\Models\Cliente::class);
    }

    public function seguimientos(): HasMany
    {
        return $this->hasMany(\App\Domain\Seguimientos\Models\Seguimiento::class);
    }

    public function prospectos(): HasMany
    {
        return $this->hasMany(\App\Domain\Prospectos\Models\Prospecto::class, 'asesor_id');
    }

    public function negocios(): HasMany
    {
        return $this->hasMany(\App\Domain\Negocios\Models\Negocio::class, 'asesor_id');
    }

    public function equivalencia(): HasOne
    {
        return $this->hasOne(\App\Domain\Dashboard\Models\VendedorEquivalencia::class, 'asesor_id');
    }
}
