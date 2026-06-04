<?php

declare(strict_types=1);

namespace App\Domain\Prospectos\DTOs;

class CrearProspectoDTO
{
    public function __construct(
        public readonly string $empresa,
        public readonly string $contacto,
        public readonly int $estadoPipelineId,
        public readonly int $asesorId,
        public readonly ?string $email = null,
        public readonly ?string $telefono = null,
        public readonly ?int $origenId = null,
        public readonly ?int $prioridadId = null,
        public readonly ?float $valorEstimado = null,
        public readonly ?int $probabilidadCierre = null,
        public readonly ?string $fechaProximoContacto = null,
        public readonly ?string $observaciones = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            empresa:              $data['empresa'],
            contacto:             $data['contacto'],
            estadoPipelineId:     (int) $data['estado_pipeline_id'],
            asesorId:             (int) $data['asesor_id'],
            email:                $data['email'] ?? null,
            telefono:             $data['telefono'] ?? null,
            origenId:             isset($data['origen_id']) ? (int) $data['origen_id'] : null,
            prioridadId:          isset($data['prioridad_id']) ? (int) $data['prioridad_id'] : null,
            valorEstimado:        isset($data['valor_estimado']) ? (float) $data['valor_estimado'] : null,
            probabilidadCierre:   isset($data['probabilidad_cierre']) ? (int) $data['probabilidad_cierre'] : null,
            fechaProximoContacto: $data['fecha_proximo_contacto'] ?? null,
            observaciones:        $data['observaciones'] ?? null,
        );
    }

    public function toArray(): array
    {
        return [
            'empresa'               => $this->empresa,
            'contacto'              => $this->contacto,
            'estado_pipeline_id'    => $this->estadoPipelineId,
            'asesor_id'             => $this->asesorId,
            'email'                 => $this->email,
            'telefono'              => $this->telefono,
            'origen_id'             => $this->origenId,
            'prioridad_id'          => $this->prioridadId,
            'valor_estimado'        => $this->valorEstimado,
            'probabilidad_cierre'   => $this->probabilidadCierre,
            'fecha_proximo_contacto' => $this->fechaProximoContacto,
            'observaciones'         => $this->observaciones,
        ];
    }
}
