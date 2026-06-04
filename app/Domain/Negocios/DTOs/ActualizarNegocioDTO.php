<?php

declare(strict_types=1);

namespace App\Domain\Negocios\DTOs;

class ActualizarNegocioDTO
{
    public function __construct(
        public readonly ?string $nombreNegocio = null,
        public readonly ?int $pipelineEstadoId = null,
        public readonly ?int $tipoNegocioId = null,
        public readonly ?string $descripcion = null,
        public readonly ?float $valorEstimado = null,
        public readonly ?int $probabilidadCierre = null,
        public readonly ?string $fechaEstimadaCierre = null,
        public readonly ?string $fechaCierreReal = null,
        public readonly ?int $motivoPerdidaId = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            nombreNegocio:       $data['nombre_negocio'] ?? null,
            pipelineEstadoId:    isset($data['pipeline_estado_id']) ? (int) $data['pipeline_estado_id'] : null,
            tipoNegocioId:       isset($data['tipo_negocio_id']) ? (int) $data['tipo_negocio_id'] : null,
            descripcion:         $data['descripcion'] ?? null,
            valorEstimado:       isset($data['valor_estimado']) ? (float) $data['valor_estimado'] : null,
            probabilidadCierre:  isset($data['probabilidad_cierre']) ? (int) $data['probabilidad_cierre'] : null,
            fechaEstimadaCierre: $data['fecha_estimada_cierre'] ?? null,
            fechaCierreReal:     $data['fecha_cierre_real'] ?? null,
            motivoPerdidaId:     isset($data['motivo_perdida_id']) ? (int) $data['motivo_perdida_id'] : null,
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'nombre_negocio'       => $this->nombreNegocio,
            'pipeline_estado_id'   => $this->pipelineEstadoId,
            'tipo_negocio_id'      => $this->tipoNegocioId,
            'descripcion'          => $this->descripcion,
            'valor_estimado'       => $this->valorEstimado,
            'probabilidad_cierre'  => $this->probabilidadCierre,
            'fecha_estimada_cierre' => $this->fechaEstimadaCierre,
            'fecha_cierre_real'    => $this->fechaCierreReal,
            'motivo_perdida_id'    => $this->motivoPerdidaId,
        ], fn ($v) => $v !== null);
    }
}
