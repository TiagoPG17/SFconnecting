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
