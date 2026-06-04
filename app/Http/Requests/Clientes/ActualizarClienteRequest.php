<?php

declare(strict_types=1);

namespace App\Http\Requests\Clientes;

use App\Domain\Clientes\Models\Cliente;
use Illuminate\Foundation\Http\FormRequest;

class ActualizarClienteRequest extends FormRequest
{
    public function authorize(): bool
    {
        $cliente = $this->route('cliente');

        return $this->user()?->can('update', $cliente) ?? false;
    }

    public function rules(): array
    {
        $clienteId = $this->route('cliente')?->id;

        return [
            'razon_social' => ['sometimes', 'string', 'max:255'],
            'email'        => ['sometimes', 'nullable', 'email', 'max:255', "unique:clientes,email,{$clienteId}"],
            'telefono'     => ['sometimes', 'nullable', 'string', 'max:20'],
            'ciudad'       => ['sometimes', 'nullable', 'string', 'max:100'],
            'direccion'    => ['sometimes', 'nullable', 'string', 'max:255'],
            'estado'       => ['sometimes', 'string', 'in:activo,inactivo,prospecto'],
            'notas'        => ['sometimes', 'nullable', 'string'],
        ];
    }

    public function messages(): array
    {
        return [
            'email.unique'  => 'Ya existe un cliente con este correo.',
            'estado.in'     => 'El estado debe ser: activo, inactivo o prospecto.',
        ];
    }
}
