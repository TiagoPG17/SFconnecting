<?php

declare(strict_types=1);

namespace App\Domain\Clientes\DTOs;

class CrearClienteDTO
{
    public function __construct(
        public readonly string $razonSocial,
        public readonly string $nit,
        public readonly int $userId,
        public readonly int $compania,
        public readonly ?string $email = null,
        public readonly ?string $telefono = null,
        public readonly ?string $ciudad = null,
        public readonly ?string $direccion = null,
        public readonly string $estado = 'prospecto',
        public readonly ?string $notas = null,
        public readonly ?array $datosCarga = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            razonSocial: $data['razon_social'],
            nit:         $data['nit'],
            userId:      (int) $data['user_id'],
            compania:    (int) $data['compania'],
            email:       $data['email'] ?? null,
            telefono:    $data['telefono'] ?? null,
            ciudad:      $data['ciudad'] ?? null,
            direccion:   $data['direccion'] ?? null,
            estado:      $data['estado'] ?? 'prospecto',
            notas:       $data['notas'] ?? null,
            datosCarga:  $data['datos_carga'] ?? null,
        );
    }

    public function enriquecerConERP(array $erpData): self
    {
        return new self(
            razonSocial: $this->razonSocial ?: ($erpData['razon_social'] ?? $this->razonSocial),
            nit:         $this->nit,
            userId:      $this->userId,
            compania:    $this->compania,
            email:       $this->email    ?: ($erpData['email']    ?? $this->email),
            telefono:    $this->telefono ?: ($erpData['telefono'] ?? $this->telefono),
            ciudad:      $this->ciudad   ?: ($erpData['ciudad']   ?? $this->ciudad),
            direccion:   $this->direccion ?: ($erpData['direccion'] ?? $this->direccion),
            estado:      $this->estado,
            notas:       $this->notas,
            datosCarga:  $this->datosCarga,
        );
    }

    public function toArray(): array
    {
        return [
            'razon_social' => $this->razonSocial,
            'nit'          => $this->nit,
            'user_id'      => $this->userId,
            'compania'     => $this->compania,
            'email'        => $this->email,
            'telefono'     => $this->telefono,
            'ciudad'       => $this->ciudad,
            'direccion'    => $this->direccion,
            'estado'       => $this->estado,
            'notas'        => $this->notas,
            'datos_carga'  => $this->datosCarga,
        ];
    }
}
