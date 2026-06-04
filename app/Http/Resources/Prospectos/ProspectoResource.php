<?php

declare(strict_types=1);

namespace App\Http\Resources\Prospectos;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProspectoResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'                    => $this->id,
            'codigo'                => $this->codigo,
            'empresa'               => $this->empresa,
            'contacto'              => $this->contacto,
            'email'                 => $this->email,
            'telefono'              => $this->telefono,
            'valor_estimado'        => $this->valor_estimado,
            'probabilidad_efectiva' => $this->probabilidadEfectiva(),
            'probabilidad_cierre'   => $this->probabilidad_cierre,
            'fecha_proximo_contacto' => $this->fecha_proximo_contacto?->format('Y-m-d'),
            'observaciones'         => $this->observaciones,
            'activo'                => $this->activo,
            'esta_convertido'       => $this->estaConvertido(),
            'fecha_conversion'      => $this->fecha_conversion?->format('Y-m-d H:i:s'),
            'estado_pipeline'       => $this->whenLoaded('estadoPipeline', fn () => [
                'id'               => $this->estadoPipeline->id,
                'nombre'           => $this->estadoPipeline->nombre,
                'color'            => $this->estadoPipeline->color,
                'icono'            => $this->estadoPipeline->icono,
                'porcentaje_cierre' => $this->estadoPipeline->porcentaje_cierre,
            ]),
            'origen'  => $this->whenLoaded('origen', fn () => [
                'id'     => $this->origen?->id,
                'nombre' => $this->origen?->nombre,
            ]),
            'prioridad' => $this->whenLoaded('prioridad', fn () => [
                'id'     => $this->prioridad?->id,
                'nombre' => $this->prioridad?->nombre,
                'color'  => $this->prioridad?->color,
            ]),
            'asesor' => $this->whenLoaded('asesor', fn () => [
                'id'   => $this->asesor->id,
                'name' => $this->asesor->name,
            ]),
            'created_at' => $this->created_at?->format('Y-m-d H:i:s'),
            'updated_at' => $this->updated_at?->format('Y-m-d H:i:s'),
        ];
    }
}
