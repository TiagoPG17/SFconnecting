<?php

declare(strict_types=1);

namespace App\Http\Requests\Negocios;

use Illuminate\Foundation\Http\FormRequest;

class CrearNegocioRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', \App\Domain\Negocios\Models\Negocio::class);
    }

    public function rules(): array
    {
        return [
            'nombre_negocio'       => ['required', 'string', 'max:200'],
            'pipeline_estado_id'   => ['required', 'integer', 'exists:sf_pipeline_estados,id'],
            'prospecto_id'         => ['nullable', 'integer', 'exists:sf_prospectos,id'],
            'cliente_id'           => ['nullable', 'integer', 'exists:clientes,id'],
            'tipo_negocio_id'      => ['nullable', 'integer', 'exists:sf_maestros_comerciales,id'],
            'descripcion'          => ['nullable', 'string', 'max:2000'],
            'valor_estimado'       => ['nullable', 'numeric', 'min:0'],
            'probabilidad_cierre'  => ['nullable', 'integer', 'min:0', 'max:100'],
            'fecha_estimada_cierre' => ['nullable', 'date'],
        ];
    }
}
