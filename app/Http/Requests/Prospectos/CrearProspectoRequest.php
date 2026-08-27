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
            'compania'              => ['nullable', 'integer', 'in:1,2'],
        ];
    }

    public function messages(): array
    {
        return [
            'empresa.required'            => 'El nombre de la empresa es obligatorio.',
            'empresa.max'                 => 'El nombre no puede superar los :max caracteres.',
            'contacto.required'           => 'El nombre del contacto es obligatorio.',
            'email.email'                 => 'El correo no tiene un formato válido.',
            'estado_pipeline_id.required' => 'Debes seleccionar un estado del pipeline.',
            'estado_pipeline_id.exists'   => 'El estado seleccionado no existe.',
            'valor_estimado.min'          => 'El valor no puede ser negativo.',
            'probabilidad_cierre.min'     => 'La probabilidad mínima es 0 %.',
            'probabilidad_cierre.max'     => 'La probabilidad máxima es 100 %.',
            'fecha_proximo_contacto.date'             => 'La fecha de contacto no es válida.',
            'fecha_proximo_contacto.after_or_equal'   => 'La fecha de contacto no puede ser en el pasado.',
        ];
    }
}
