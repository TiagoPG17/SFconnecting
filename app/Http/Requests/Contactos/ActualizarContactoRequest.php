<?php

declare(strict_types=1);

namespace App\Http\Requests\Contactos;

use Illuminate\Foundation\Http\FormRequest;

class ActualizarContactoRequest extends FormRequest
{
    public function authorize(): bool
    {
        $contacto = $this->route('contacto');

        return $contacto !== null && $this->user()?->can('update', $contacto) === true;
    }

    public function rules(): array
    {
        return [
            'nombre'    => ['sometimes', 'required', 'string', 'max:255'],
            'cargo'     => ['nullable', 'string', 'max:100'],
            'email'     => ['nullable', 'email', 'max:255'],
            'telefono'  => ['nullable', 'string', 'max:20'],
            'principal' => ['nullable', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'nombre.required' => 'El nombre del contacto es obligatorio.',
            'email.email'     => 'El correo electrónico no es válido.',
        ];
    }
}
