<?php

declare(strict_types=1);

namespace App\Http\Requests\Seguimientos;

use Illuminate\Foundation\Http\FormRequest;

class ActualizarSeguimientoRequest extends FormRequest
{
    public function authorize(): bool
    {
        $seguimiento = $this->route('seguimiento');

        return $seguimiento !== null && $this->user()?->can('update', $seguimiento) === true;
    }

    public function rules(): array
    {
        return [
            'tipo'              => ['sometimes', 'required', 'string', 'in:llamada,reunion,email,visita,whatsapp,otro'],
            'resultado'         => ['sometimes', 'required', 'string', 'in:exitoso,no_contactado,pendiente,cancelado'],
            'descripcion'       => ['sometimes', 'required', 'string', 'min:10'],
            'fecha_seguimiento' => ['sometimes', 'required', 'date'],
            'proxima_fecha'     => ['nullable', 'date', 'after:now'],
            'contacto_id'       => ['nullable', 'integer', 'exists:contactos,id'],
        ];
    }

    public function messages(): array
    {
        return [
            'tipo.in'             => 'El tipo de seguimiento no es válido.',
            'resultado.in'        => 'El resultado no es válido.',
            'descripcion.min'     => 'La descripción debe tener al menos 10 caracteres.',
            'proxima_fecha.after' => 'La próxima fecha debe ser en el futuro.',
        ];
    }
}
