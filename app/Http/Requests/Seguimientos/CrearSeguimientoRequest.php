<?php

declare(strict_types=1);

namespace App\Http\Requests\Seguimientos;

use Illuminate\Foundation\Http\FormRequest;

class CrearSeguimientoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'cliente_id'        => ['required_without:prospecto_id', 'nullable', 'integer', 'exists:clientes,id'],
            'prospecto_id'      => ['required_without:cliente_id', 'nullable', 'integer', 'exists:sf_prospectos,id'],
            'contacto_id'       => ['nullable', 'integer', 'exists:contactos,id'],
            'tipo'              => ['required', 'string', 'in:llamada,reunion,email,visita,whatsapp,otro'],
            'resultado'         => ['required', 'string', 'in:exitoso,no_contactado,pendiente,cancelado'],
            'descripcion'       => ['required', 'string', 'min:10'],
            'fecha_seguimiento' => ['required', 'date'],
            'proxima_fecha'     => ['nullable', 'date', 'after:now'],
        ];
    }

    public function messages(): array
    {
        return [
            'cliente_id.required_without'   => 'El cliente es obligatorio.',
            'cliente_id.exists'             => 'El cliente seleccionado no existe.',
            'prospecto_id.required_without' => 'Debe indicar un cliente o un prospecto.',
            'prospecto_id.exists'           => 'El prospecto seleccionado no existe.',
            'tipo.in'                       => 'El tipo de seguimiento no es válido.',
            'resultado.in'                  => 'El resultado no es válido.',
            'descripcion.min'               => 'La descripción debe tener al menos 10 caracteres.',
            'proxima_fecha.after'           => 'La próxima fecha debe ser en el futuro.',
        ];
    }
}
