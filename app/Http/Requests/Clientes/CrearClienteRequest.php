<?php

declare(strict_types=1);

namespace App\Http\Requests\Clientes;

use Illuminate\Foundation\Http\FormRequest;

class CrearClienteRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', \App\Domain\Clientes\Models\Cliente::class) ?? false;
    }

    public function rules(): array
    {
        return [
            'razon_social' => ['required', 'string', 'max:255'],
            'nit'          => ['required', 'string', 'max:20', 'unique:clientes,nit', 'regex:/^\d{6,15}$/'],
            'email'        => ['nullable', 'email', 'max:255', 'unique:clientes,email'],
            'telefono'     => ['nullable', 'string', 'max:20'],
            'ciudad'       => ['nullable', 'string', 'max:100'],
            'direccion'    => ['nullable', 'string', 'max:255'],
            'estado'       => ['nullable', 'string', 'in:activo,inactivo,prospecto'],
            'notas'        => ['nullable', 'string'],
        ];
    }

    public function messages(): array
    {
        return [
            'razon_social.required' => 'La razón social es obligatoria.',
            'nit.required'          => 'El NIT es obligatorio.',
            'nit.unique'            => 'Ya existe un cliente con este NIT.',
            'nit.regex'             => 'El NIT debe contener solo dígitos (6-15).',
            'email.unique'          => 'Ya existe un cliente con este correo.',
            'estado.in'             => 'El estado debe ser: activo, inactivo o prospecto.',
        ];
    }
}
