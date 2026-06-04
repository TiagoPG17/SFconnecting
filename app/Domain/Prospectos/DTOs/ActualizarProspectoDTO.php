<?php

declare(strict_types=1);

namespace App\Domain\Prospectos\DTOs;

class ActualizarProspectoDTO
{
    public function __construct(
        public readonly ?string $empresa = null,
        public readonly ?string $contacto = null,
        public readonly ?string $email = null,
        public readonly ?string $telefono = null,
        public readonly ?int $estadoPipelineId = null,
        public readonly ?int $origenId = null,
        public readonly ?int $prioridadId = null,
        public readonly ?float $valorEstimado = null,
        public readonly ?int $probabilidadCierre = null,
        public readonly ?string $fechaProximoContacto = null,
        public readonly ?string $observaciones = null,
        public readonly ?bool $activo = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            empresa:              $data['empresa'] ?? null,
            contacto:             $data['contacto'] ?? null,
            email:                $data['email'] ?? null,
            telefono:             $data['telefono'] ?? null,
            estadoPipelineId:     isset($data['estado_pipeline_id']) ? (int) $data['estado_pipeline_id'] : null,
            origenId:             isset($data['origen_id']) ? (int) $data['origen_id'] : null,
            prioridadId:          isset($data['prioridad_id']) ? (int) $data['prioridad_id'] : null,
            valorEstimado:        isset($data['valor_estimado']) ? (float) $data['valor_estimado'] : null,
            probabilidadCierre:   isset($data['probabilidad_cierre']) ? (int) $data['probabilidad_cierre'] : null,
            fechaProximoContacto: $data['fecha_proximo_contacto'] ?? null,
            observaciones:        $data['observaciones'] ?? null,
            activo:               isset($data['activo']) ? (bool) $data['activo'] : null,
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'empresa'               => $this->empresa,
            'contacto'              => $this->contacto,
            'email'                 => $this->email,
            'telefono'              => $this->telefono,
            'estado_pipeline_id'    => $this->estadoPipelineId,
            'origen_id'             => $this->origenId,
            'prioridad_id'          => $this->prioridadId,
            'valor_estimado'        => $this->valorEstimado,
            'probabilidad_cierre'   => $this->probabilidadCierre,
            'fecha_proximo_contacto' => $this->fechaProximoContacto,
            'observaciones'         => $this->observaciones,
            'activo'                => $this->activo,
        ], fn ($v) => $v !== null);
    }
}
