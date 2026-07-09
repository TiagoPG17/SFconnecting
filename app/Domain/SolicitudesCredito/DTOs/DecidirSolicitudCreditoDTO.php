<?php

declare(strict_types=1);

namespace App\Domain\SolicitudesCredito\DTOs;

class DecidirSolicitudCreditoDTO
{
    public function __construct(
        public readonly string $decision,
        public readonly int $revisadoPor,
        public readonly ?string $comentarioRevision = null,
        public readonly ?string $condiciones = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            decision:           $data['decision'],
            revisadoPor:        (int) $data['revisado_por'],
            comentarioRevision: $data['comentario_revision'] ?? null,
            condiciones:        $data['condiciones'] ?? null,
        );
    }

    public function toArray(): array
    {
        return [
            'decision'              => $this->decision,
            'revisado_por'          => $this->revisadoPor,
            'comentario_revision'   => $this->comentarioRevision,
            'condiciones'           => $this->condiciones,
        ];
    }
}
