<?php

declare(strict_types=1);

namespace App\Domain\Clientes\DTOs;

class ActualizarClienteDTO
{
    public function __construct(
        public readonly ?string $razonSocial = null,
        public readonly ?string $email = null,
        public readonly ?string $telefono = null,
        public readonly ?string $ciudad = null,
        public readonly ?string $direccion = null,
        public readonly ?string $estado = null,
        public readonly ?string $notas = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            razonSocial: $data['razon_social'] ?? null,
            email:       $data['email'] ?? null,
            telefono:    $data['telefono'] ?? null,
            ciudad:      $data['ciudad'] ?? null,
            direccion:   $data['direccion'] ?? null,
            estado:      $data['estado'] ?? null,
            notas:       $data['notas'] ?? null,
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'razon_social' => $this->razonSocial,
            'email'        => $this->email,
            'telefono'     => $this->telefono,
            'ciudad'       => $this->ciudad,
            'direccion'    => $this->direccion,
            'estado'       => $this->estado,
            'notas'        => $this->notas,
        ], fn ($v) => $v !== null);
    }
}
