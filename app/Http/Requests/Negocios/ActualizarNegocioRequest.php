<?php

declare(strict_types=1);

namespace App\Http\Requests\Negocios;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ActualizarNegocioRequest extends FormRequest
{
    public function authorize(): bool
    {
        $negocio = $this->route('negocio');

        return $this->user()->can('update', $negocio);
    }

    public function rules(): array
    {
        $negocio = $this->route('negocio');
        $requiereNuevoTipo = $negocio
            && $negocio->tipo_negocio_id !== null
            && ! $negocio->tipoNegocio?->activo;

        return [
            'nombre_negocio'       => ['sometimes', 'string', 'max:200'],
            'pipeline_estado_id'   => ['sometimes', 'integer', 'exists:sf_pipeline_estados,id'],
            'tipo_negocio_id'      => $requiereNuevoTipo
                ? ['required', 'integer', Rule::exists('sf_maestros_comerciales', 'id')->where('tipo', 'tipo_negocio')->where('activo', true)]
                : ['sometimes', 'nullable', 'integer', 'exists:sf_maestros_comerciales,id'],
            'sector_id'            => ['sometimes', 'nullable', 'integer', 'exists:sf_maestros_comerciales,id'],
            'descripcion'          => ['sometimes', 'nullable', 'string', 'max:2000'],
            'valor_estimado'       => ['sometimes', 'numeric', 'min:0'],
            'probabilidad_cierre'  => ['sometimes', 'nullable', 'integer', 'min:0', 'max:100'],
            'fecha_estimada_cierre' => ['sometimes', 'nullable', 'date'],
            'motivo_perdida_id'    => ['sometimes', 'nullable', 'integer', 'exists:sf_maestros_comerciales,id'],
            'observacion_perdida'  => ['sometimes', 'nullable', 'string', 'max:1000'],
        ];
    }

    public function messages(): array
    {
        return [
            'tipo_negocio_id.required' => 'El tipo de negocio de este registro fue desactivado. Selecciona uno nuevo para guardar los cambios.',
            'tipo_negocio_id.exists'   => 'El tipo de negocio seleccionado no es válido o está desactivado.',
        ];
    }
}
