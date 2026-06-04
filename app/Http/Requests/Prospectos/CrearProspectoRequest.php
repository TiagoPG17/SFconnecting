<?php

declare(strict_types=1);

namespace App\Http\Requests\Prospectos;

use Illuminate\Foundation\Http\FormRequest;

class CrearProspectoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', \App\Domain\Prospectos\Models\Prospecto::class);
    }

    public function rules(): array
    {
        return [
            'empresa'               => ['required', 'string', 'max:200'],
            'contacto'              => ['required', 'string', 'max:150'],
            'email'                 => ['nullable', 'email', 'max:150'],
            'telefono'              => ['nullable', 'string', 'max:30'],
            'estado_pipeline_id'    => ['required', 'integer', 'exists:sf_pipeline_estados,id'],
            'origen_id'             => ['nullable', 'integer', 'exists:sf_maestros_comerciales,id'],
            'prioridad_id'          => ['nullable', 'integer', 'exists:sf_maestros_comerciales,id'],
            'valor_estimado'        => ['nullable', 'numeric', 'min:0'],
            'probabilidad_cierre'   => ['nullable', 'integer', 'min:0', 'max:100'],
            'fecha_proximo_contacto' => ['nullable', 'date', 'after_or_equal:today'],
            'observaciones'         => ['nullable', 'string', 'max:2000'],
        ];
    }
}
