<?php

declare(strict_types=1);

namespace App\Http\Requests\Maestros;

use Illuminate\Foundation\Http\FormRequest;

class GestionarPipelineEstadoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('maestros.gestionar') ?? false;
    }

    public function rules(): array
    {
        return [
            'nombre'            => ['required', 'string', 'max:100'],
            'tipo'              => ['required', 'string', 'in:prospecto,negocio'],
            'color'             => ['nullable', 'string', 'regex:/^#[0-9a-fA-F]{6}$/'],
            'icono'             => ['nullable', 'string', 'max:50'],
            'orden'             => ['nullable', 'integer', 'min:0'],
            'porcentaje_cierre' => ['nullable', 'integer', 'min:0', 'max:100'],
            'es_final'          => ['nullable', 'boolean'],
            'es_ganado'         => ['nullable', 'boolean'],
            'es_perdido'        => ['nullable', 'boolean'],
        ];
    }
}
