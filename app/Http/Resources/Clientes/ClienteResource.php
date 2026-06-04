<?php

declare(strict_types=1);

namespace App\Http\Resources\Clientes;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ClienteResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'           => $this->id,
            'razon_social' => $this->razon_social,
            'nit'          => $this->nit,
            'email'        => $this->email,
            'telefono'     => $this->telefono,
            'ciudad'       => $this->ciudad,
            'direccion'    => $this->direccion,
            'estado'       => $this->estado,
            'notas'        => $this->notas,
            'asesor'       => $this->whenLoaded('asesor', fn () => [
                'id'   => $this->asesor->id,
                'name' => $this->asesor->name,
            ]),
            'contactos_count' => $this->whenCounted('contactos'),
            'created_at'      => $this->created_at?->toDateTimeString(),
            'updated_at'      => $this->updated_at?->toDateTimeString(),
        ];
    }
}
