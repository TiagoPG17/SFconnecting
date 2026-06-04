<?php

declare(strict_types=1);

namespace App\Http\Requests\Usuarios;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Spatie\Permission\Models\Role;

class ActualizarUsuarioRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('usuarios.gestionar') ?? false;
    }

    public function rules(): array
    {
        $rolesValidos = Role::pluck('name')->implode(',');
        $userId       = $this->route('usuario');

        return [
            'name'     => ['required', 'string', 'max:255'],
            'email'    => ['required', 'email', Rule::unique('users', 'email')->ignore($userId)],
            'password' => ['nullable', 'string', 'min:8', 'confirmed'],
            'rol'      => ['required', 'string', "in:{$rolesValidos}"],
        ];
    }
}
