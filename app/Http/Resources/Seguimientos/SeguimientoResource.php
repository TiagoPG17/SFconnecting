<?php

declare(strict_types=1);

namespace App\Http\Resources\Seguimientos;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SeguimientoResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'                => $this->id,
            'cliente_id'        => $this->cliente_id,
            'prospecto_id'      => $this->prospecto_id,
            'tipo'              => $this->tipo,
            'resultado'         => $this->resultado,
            'descripcion'       => $this->descripcion,
            'fecha_seguimiento' => $this->fecha_seguimiento?->toDateTimeString(),
            'proxima_fecha'     => $this->proxima_fecha?->toDateTimeString(),
            'tiene_proximo'     => $this->tieneSeguimientoFuturo(),
            'asesor'            => $this->whenLoaded('asesor', fn () => [
                'id'   => $this->asesor->id,
                'name' => $this->asesor->name,
            ]),
            'contacto' => $this->whenLoaded('contacto', fn () => $this->contacto ? [
                'id'     => $this->contacto->id,
                'nombre' => $this->contacto->nombre,
            ] : null),
            'created_at' => $this->created_at?->toDateTimeString(),
        ];
    }
}
