<?php

declare(strict_types=1);

namespace App\Http\Requests\Prospectos;

use Illuminate\Foundation\Http\FormRequest;

class ConvertirProspectoRequest extends FormRequest
{
    public function authorize(): bool
    {
        $prospecto = $this->route('prospecto');

        return $this->user()->can('convertir', $prospecto);
    }

    public function rules(): array
    {
        return [
            'razon_social'            => ['nullable', 'string', 'max:200'],
            'nit'                     => ['nullable', 'string', 'max:20'],
            'ciudad'                  => ['nullable', 'string', 'max:100'],
            'direccion'               => ['nullable', 'string', 'max:255'],
            'datos_carga'             => ['nullable', 'array'],
            'solicita_cupo'                       => ['nullable', 'boolean'],
            'monto_solicitado'                    => ['required_if:solicita_cupo,true', 'nullable', 'numeric', 'min:0'],
            'plazo_solicitado_dias'                => ['required_if:solicita_cupo,true', 'nullable', 'integer', 'min:1'],
            'justificacion_cupo'                   => ['nullable', 'string', 'max:2000'],
            'referencias_comerciales'              => ['nullable', 'array', 'max:2'],
            'referencias_comerciales.*.empresa'    => ['required_with:referencias_comerciales', 'string', 'max:200'],
            'referencias_comerciales.*.telefono'   => ['nullable', 'string', 'max:30'],
            'referencias_comerciales.*.nit'        => ['nullable', 'string', 'max:20'],
            'inventario_consignacion'              => ['nullable', 'boolean'],
        ];
    }
}
