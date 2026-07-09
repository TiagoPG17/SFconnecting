<?php

declare(strict_types=1);

namespace App\Domain\SolicitudesCredito\DTOs;

class CrearSolicitudCreditoDTO
{
    public function __construct(
        public readonly int $negocioId,
        public readonly int $asesorId,
        public readonly float $montoSolicitado,
        public readonly ?int $plazoSolicitadoDias = null,
        public readonly ?string $justificacion = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            negocioId:           (int) $data['negocio_id'],
            asesorId:            (int) $data['asesor_id'],
            montoSolicitado:     (float) $data['monto_solicitado'],
            plazoSolicitadoDias: isset($data['plazo_solicitado_dias']) ? (int) $data['plazo_solicitado_dias'] : null,
            justificacion:       $data['justificacion'] ?? null,
        );
    }

    public function toArray(): array
    {
        return [
            'negocio_id'             => $this->negocioId,
            'asesor_id'               => $this->asesorId,
            'monto_solicitado'        => $this->montoSolicitado,
            'plazo_solicitado_dias'   => $this->plazoSolicitadoDias,
            'justificacion'           => $this->justificacion,
        ];
    }
}
