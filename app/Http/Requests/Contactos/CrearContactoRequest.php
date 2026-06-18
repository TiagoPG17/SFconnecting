<?php

declare(strict_types=1);

namespace App\Http\Requests\Contactos;

use App\Domain\Clientes\Models\Cliente;
use App\Domain\Clientes\Models\Contacto;
use Illuminate\Foundation\Http\FormRequest;

class CrearContactoRequest extends FormRequest
{
    public function authorize(): bool
    {
        /** @var Cliente|null $cliente */
        $cliente = $this->route('cliente');

        return $cliente !== null && $this->user()?->can('create', [Contacto::class, $cliente]) === true;
    }

    public function rules(): array
    {
        return [
            'nombre'    => ['required', 'string', 'max:255'],
            'cargo'     => ['required', 'string', 'max:100'],
            'email'     => ['required', 'email', 'max:255'],
            'telefono'  => ['required', 'string', 'max:20'],
            'principal' => ['nullable', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'nombre.required'   => 'El nombre del contacto es obligatorio.',
            'cargo.required'    => 'El cargo es obligatorio.',
            'email.required'    => 'El correo electrónico es obligatorio.',
            'email.email'       => 'El correo electrónico no es válido.',
            'telefono.required' => 'El teléfono es obligatorio.',
        ];
    }
}
