<?php

declare(strict_types=1);

namespace App\Http\Resources\SolicitudesCredito;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SolicitudCreditoResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'                     => $this->id,
            'monto_solicitado'       => $this->monto_solicitado,
            'plazo_solicitado_dias'  => $this->plazo_solicitado_dias,
            'justificacion'          => $this->justificacion,
            'dossier_erp'            => $this->dossier_erp,
            'comentario_revision'    => $this->comentario_revision,
            'condiciones'            => $this->condiciones,
            'revisado_en'            => $this->revisado_en?->format('Y-m-d H:i:s'),
            'esta_aprobada'          => $this->estaAprobada(),
            'esta_rechazada'         => $this->estaRechazada(),
            'esta_finalizada'        => $this->estaFinalizada(),
            'pipeline_estado' => $this->whenLoaded('pipelineEstado', fn () => [
                'id'       => $this->pipelineEstado->id,
                'nombre'   => $this->pipelineEstado->nombre,
                'color'    => $this->pipelineEstado->color,
                'icono'    => $this->pipelineEstado->icono,
                'es_final' => $this->pipelineEstado->es_final,
            ]),
            'negocio' => $this->whenLoaded('negocio', fn () => [
                'id'             => $this->negocio->id,
                'nombre_negocio' => $this->negocio->nombre_negocio,
            ]),
            'cliente' => $this->whenLoaded('cliente', fn () => [
                'id'           => $this->cliente->id,
                'razon_social' => $this->cliente->razon_social,
                'nit'          => $this->cliente->nit,
            ]),
            'asesor' => $this->whenLoaded('asesor', fn () => [
                'id'   => $this->asesor->id,
                'name' => $this->asesor->name,
            ]),
            'revisor' => $this->whenLoaded('revisor', fn () => $this->revisor ? [
                'id'   => $this->revisor->id,
                'name' => $this->revisor->name,
            ] : null),
            'created_at' => $this->created_at?->format('Y-m-d H:i:s'),
            'updated_at' => $this->updated_at?->format('Y-m-d H:i:s'),
        ];
    }
}
