<?php

declare(strict_types=1);

namespace App\Http\Requests\SolicitudesCredito;

use Illuminate\Foundation\Http\FormRequest;

class DecidirSolicitudCreditoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('revisar', $this->route('solicitudCredito'));
    }

    public function rules(): array
    {
        return [
            'decision'            => ['required', 'in:aprobada,aprobada_condiciones,rechazada'],
            'comentario_revision' => ['nullable', 'string', 'max:2000'],
            'condiciones'         => ['required_if:decision,aprobada_condiciones', 'nullable', 'string', 'max:2000'],
        ];
    }
}
