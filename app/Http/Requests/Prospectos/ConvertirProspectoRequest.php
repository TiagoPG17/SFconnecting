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
            'razon_social' => ['nullable', 'string', 'max:200'],
            'nit'          => ['nullable', 'string', 'max:20'],
            'ciudad'       => ['nullable', 'string', 'max:100'],
            'direccion'    => ['nullable', 'string', 'max:255'],
        ];
    }
}
