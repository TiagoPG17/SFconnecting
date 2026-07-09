<?php

declare(strict_types=1);

namespace App\Domain\Negocios\DTOs;

class CrearNegocioDTO
{
    public function __construct(
        public readonly string $nombreNegocio,
        public readonly int $pipelineEstadoId,
        public readonly int $asesorId,
        public readonly ?int $prospectoId = null,
        public readonly ?int $clienteId = null,
        public readonly ?int $tipoNegocioId = null,
        public readonly ?string $descripcion = null,
        public readonly float $valorEstimado = 0,
        public readonly ?int $probabilidadCierre = null,
        public readonly ?string $fechaEstimadaCierre = null,
        public readonly ?int $compania = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            nombreNegocio:       $data['nombre_negocio'],
            pipelineEstadoId:    (int) $data['pipeline_estado_id'],
            asesorId:            (int) $data['asesor_id'],
            prospectoId:         isset($data['prospecto_id']) ? (int) $data['prospecto_id'] : null,
            clienteId:           isset($data['cliente_id']) ? (int) $data['cliente_id'] : null,
            tipoNegocioId:       isset($data['tipo_negocio_id']) ? (int) $data['tipo_negocio_id'] : null,
            descripcion:         $data['descripcion'] ?? null,
            valorEstimado:       isset($data['valor_estimado']) ? (float) $data['valor_estimado'] : 0,
            probabilidadCierre:  isset($data['probabilidad_cierre']) ? (int) $data['probabilidad_cierre'] : null,
            fechaEstimadaCierre: $data['fecha_estimada_cierre'] ?? null,
            compania:            isset($data['compania']) ? (int) $data['compania'] : null,
        );
    }

    public function toArray(): array
    {
        return [
            'nombre_negocio'       => $this->nombreNegocio,
            'pipeline_estado_id'   => $this->pipelineEstadoId,
            'asesor_id'            => $this->asesorId,
            'prospecto_id'         => $this->prospectoId,
            'cliente_id'           => $this->clienteId,
            'tipo_negocio_id'      => $this->tipoNegocioId,
            'descripcion'          => $this->descripcion,
            'valor_estimado'       => $this->valorEstimado,
            'probabilidad_cierre'  => $this->probabilidadCierre,
            'fecha_estimada_cierre' => $this->fechaEstimadaCierre,
            'compania'             => $this->compania,
        ];
    }
}
