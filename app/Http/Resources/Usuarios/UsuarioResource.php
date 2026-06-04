<?php

declare(strict_types=1);

namespace App\Http\Resources\Usuarios;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UsuarioResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'         => $this->id,
            'name'       => $this->name,
            'email'      => $this->email,
            'activo'     => $this->activo,
            'roles'      => $this->getRoleNames(),
            'created_at' => $this->created_at?->format('Y-m-d'),
        ];
    }
}
