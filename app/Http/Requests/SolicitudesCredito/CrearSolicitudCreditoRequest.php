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
            'negocio_id'            => ['required', 'integer', 'exists:sf_negocios,id'],
            'monto_solicitado'      => ['required', 'numeric', 'min:0'],
            'plazo_solicitado_dias' => ['nullable', 'integer', 'min:1'],
            'justificacion'         => ['nullable', 'string', 'max:2000'],
        ];
    }
}
