<?php

declare(strict_types=1);

namespace App\Http\Requests\SolicitudesCredito;

use Illuminate\Foundation\Http\FormRequest;

class CrearSolicitudCreditoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', \App\Domain\SolicitudesCredito\Models\SolicitudCredito::class);
    }

    public function rules(): array
    {
        return [
            'negocio_id'                       => ['required_without:cliente_id', 'nullable', 'integer', 'exists:sf_negocios,id'],
            'cliente_id'                       => ['required_without:negocio_id', 'nullable', 'integer', 'exists:clientes,id'],
            'monto_solicitado'                 => ['required', 'numeric', 'min:0'],
            'plazo_solicitado_dias'            => ['nullable', 'integer', 'min:1'],
            'justificacion'                    => ['nullable', 'string', 'max:2000'],
            'referencias_comerciales'          => ['nullable', 'array', 'max:2'],
            'referencias_comerciales.*.empresa' => ['required_with:referencias_comerciales', 'string', 'max:200'],
            'referencias_comerciales.*.telefono' => ['nullable', 'string', 'max:30'],
            'referencias_comerciales.*.nit'      => ['nullable', 'string', 'max:20'],
            'inventario_consignacion'          => ['nullable', 'boolean'],
        ];
    }
}
