<?php

declare(strict_types=1);

namespace App\Http\Requests\Maestros;

use Illuminate\Foundation\Http\FormRequest;

class GestionarMaestroRequest extends FormRequest
{
    private const TIPOS_VALIDOS = [
        'tipo_negocio', 'prioridad', 'fuente_lead',
        'motivo_perdida', 'tipo_actividad', 'clasificacion', 'segmento',
    ];

    public function authorize(): bool
    {
        return $this->user()?->can('maestros.gestionar') ?? false;
    }

    public function rules(): array
    {
        $tipos = implode(',', self::TIPOS_VALIDOS);

        return [
            'nombre' => ['required', 'string', 'max:100'],
            'tipo'   => ['required', 'string', "in:{$tipos}"],
            'color'  => ['nullable', 'string', 'regex:/^#[0-9a-fA-F]{6}$/'],
            'icono'  => ['nullable', 'string', 'max:50'],
            'orden'  => ['nullable', 'integer', 'min:0'],
        ];
    }
}
