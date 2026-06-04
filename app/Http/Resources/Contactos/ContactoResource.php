<?php

declare(strict_types=1);

namespace App\Http\Resources\Contactos;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ContactoResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'         => $this->id,
            'cliente_id' => $this->cliente_id,
            'nombre'     => $this->nombre,
            'cargo'      => $this->cargo,
            'email'      => $this->email,
            'telefono'   => $this->telefono,
            'principal'  => $this->principal,
            'created_at' => $this->created_at?->toDateTimeString(),
        ];
    }
}
