<?php

declare(strict_types=1);

namespace App\Http\Requests\Usuarios;

use Illuminate\Foundation\Http\FormRequest;
use Spatie\Permission\Models\Role;

class CrearUsuarioRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('usuarios.gestionar') ?? false;
    }

    public function rules(): array
    {
        $rolesValidos = Role::pluck('name')->implode(',');

        return [
            'name'     => ['required', 'string', 'max:255'],
            'email'    => ['required', 'email', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'rol'      => ['required', 'string', "in:{$rolesValidos}"],
        ];
    }
}
