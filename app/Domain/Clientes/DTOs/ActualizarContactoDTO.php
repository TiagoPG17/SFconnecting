<?php

declare(strict_types=1);

namespace App\Domain\Clientes\DTOs;

class ActualizarContactoDTO
{
    public function __construct(
        public readonly ?string $nombre,
        public readonly ?string $cargo,
        public readonly ?string $email,
        public readonly ?string $telefono,
        public readonly ?bool $principal,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            nombre: $data['nombre'] ?? null,
            cargo: $data['cargo'] ?? null,
            email: $data['email'] ?? null,
            telefono: $data['telefono'] ?? null,
            principal: isset($data['principal']) ? (bool) $data['principal'] : null,
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'nombre'    => $this->nombre,
            'cargo'     => $this->cargo,
            'email'     => $this->email,
            'telefono'  => $this->telefono,
            'principal' => $this->principal,
        ], fn ($v) => $v !== null);
    }
}
