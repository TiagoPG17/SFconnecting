<?php

declare(strict_types=1);

namespace App\Http\Requests\Prospectos;

use App\Domain\Prospectos\Models\Prospecto;
use Illuminate\Foundation\Http\FormRequest;

class ActualizarProspectoRequest extends FormRequest
{
    public function authorize(): bool
    {
        $prospecto = $this->route('prospecto');

        return $this->user()->can('update', $prospecto);
    }

    public function rules(): array
    {
        return [
            'empresa'               => ['sometimes', 'string', 'max:200'],
            'contacto'              => ['sometimes', 'string', 'max:150'],
            'email'                 => ['sometimes', 'nullable', 'email', 'max:150'],
            'telefono'              => ['sometimes', 'nullable', 'string', 'max:30'],
            'estado_pipeline_id'    => ['sometimes', 'integer', 'exists:sf_pipeline_estados,id'],
            'origen_id'             => ['sometimes', 'nullable', 'integer', 'exists:sf_maestros_comerciales,id'],
            'prioridad_id'          => ['sometimes', 'nullable', 'integer', 'exists:sf_maestros_comerciales,id'],
            'valor_estimado'        => ['sometimes', 'nullable', 'numeric', 'min:0'],
            'probabilidad_cierre'   => ['sometimes', 'nullable', 'integer', 'min:0', 'max:100'],
            'fecha_proximo_contacto' => ['sometimes', 'nullable', 'date'],
            'observaciones'         => ['sometimes', 'nullable', 'string', 'max:2000'],
            'activo'                => ['sometimes', 'boolean'],
        ];
    }
}
