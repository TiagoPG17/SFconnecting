<?php

declare(strict_types=1);

namespace App\Domain\Clientes\DTOs;

class CrearContactoDTO
{
    public function __construct(
        public readonly int $clienteId,
        public readonly string $nombre,
        public readonly ?string $cargo,
        public readonly ?string $email,
        public readonly ?string $telefono,
        public readonly bool $principal,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            clienteId: (int) $data['cliente_id'],
            nombre: $data['nombre'],
            cargo: $data['cargo'] ?? null,
            email: $data['email'] ?? null,
            telefono: $data['telefono'] ?? null,
            principal: (bool) ($data['principal'] ?? false),
        );
    }

    public function toArray(): array
    {
        return [
            'cliente_id' => $this->clienteId,
            'nombre'     => $this->nombre,
            'cargo'      => $this->cargo,
            'email'      => $this->email,
            'telefono'   => $this->telefono,
            'principal'  => $this->principal,
        ];
    }
}
