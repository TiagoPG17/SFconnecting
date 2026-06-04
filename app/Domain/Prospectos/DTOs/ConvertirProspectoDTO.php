<?php

declare(strict_types=1);

namespace App\Domain\Prospectos\DTOs;

class ConvertirProspectoDTO
{
    public function __construct(
        public readonly int $usuarioId,
        public readonly ?string $razonSocial = null,
        public readonly ?string $nit = null,
        public readonly ?string $ciudad = null,
        public readonly ?string $direccion = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            usuarioId:   (int) $data['usuario_id'],
            razonSocial: $data['razon_social'] ?? null,
            nit:         $data['nit'] ?? null,
            ciudad:      $data['ciudad'] ?? null,
            direccion:   $data['direccion'] ?? null,
        );
    }
}
