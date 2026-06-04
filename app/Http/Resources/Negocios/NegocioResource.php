<?php

declare(strict_types=1);

namespace App\Http\Resources\Negocios;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class NegocioResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'                    => $this->id,
            'nombre_negocio'        => $this->nombre_negocio,
            'descripcion'           => $this->descripcion,
            'valor_estimado'        => $this->valor_estimado,
            'valor_forecast'        => $this->valorForecast(),
            'probabilidad_efectiva' => $this->probabilidadEfectiva(),
            'probabilidad_cierre'   => $this->probabilidad_cierre,
            'fecha_estimada_cierre' => $this->fecha_estimada_cierre?->format('Y-m-d'),
            'fecha_cierre_real'     => $this->fecha_cierre_real?->format('Y-m-d'),
            'esta_ganado'           => $this->estaGanado(),
            'esta_perdido'          => $this->estaPerdido(),
            'activo'                => $this->activo,
            'pipeline_estado' => $this->whenLoaded('pipelineEstado', fn () => [
                'id'               => $this->pipelineEstado->id,
                'nombre'           => $this->pipelineEstado->nombre,
                'color'            => $this->pipelineEstado->color,
                'icono'            => $this->pipelineEstado->icono,
                'porcentaje_cierre' => $this->pipelineEstado->porcentaje_cierre,
                'es_final'         => $this->pipelineEstado->es_final,
            ]),
            'tipo_negocio' => $this->whenLoaded('tipoNegocio', fn () => [
                'id'     => $this->tipoNegocio?->id,
                'nombre' => $this->tipoNegocio?->nombre,
            ]),
            'motivo_perdida' => $this->whenLoaded('motivoPerdida', fn () => [
                'id'     => $this->motivoPerdida?->id,
                'nombre' => $this->motivoPerdida?->nombre,
            ]),
            'asesor' => $this->whenLoaded('asesor', fn () => [
                'id'   => $this->asesor->id,
                'name' => $this->asesor->name,
            ]),
            'prospecto_id' => $this->prospecto_id,
            'cliente_id'   => $this->cliente_id,
            'created_at'   => $this->created_at?->format('Y-m-d H:i:s'),
            'updated_at'   => $this->updated_at?->format('Y-m-d H:i:s'),
        ];
    }
}
