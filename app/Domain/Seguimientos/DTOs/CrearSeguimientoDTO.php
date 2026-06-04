<?php

declare(strict_types=1);

namespace App\Domain\Seguimientos\DTOs;

use Carbon\Carbon;

class CrearSeguimientoDTO
{
    public function __construct(
        public readonly ?int $clienteId,
        public readonly int $userId,
        public readonly string $tipo,
        public readonly string $resultado,
        public readonly string $descripcion,
        public readonly Carbon $fechaSeguimiento,
        public readonly ?int $contactoId = null,
        public readonly ?Carbon $proximaFecha = null,
        public readonly ?int $prospectoId = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            clienteId:        isset($data['cliente_id']) ? (int) $data['cliente_id'] : null,
            userId:           (int) $data['user_id'],
            tipo:             $data['tipo'],
            resultado:        $data['resultado'],
            descripcion:      $data['descripcion'],
            fechaSeguimiento: Carbon::parse($data['fecha_seguimiento']),
            contactoId:       isset($data['contacto_id']) ? (int) $data['contacto_id'] : null,
            proximaFecha:     isset($data['proxima_fecha']) ? Carbon::parse($data['proxima_fecha']) : null,
            prospectoId:      isset($data['prospecto_id']) ? (int) $data['prospecto_id'] : null,
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'cliente_id'        => $this->clienteId,
            'prospecto_id'      => $this->prospectoId,
            'user_id'           => $this->userId,
            'contacto_id'       => $this->contactoId,
            'tipo'              => $this->tipo,
            'resultado'         => $this->resultado,
            'descripcion'       => $this->descripcion,
            'fecha_seguimiento' => $this->fechaSeguimiento->toDateTimeString(),
            'proxima_fecha'     => $this->proximaFecha?->toDateTimeString(),
        ], fn ($v) => $v !== null);
    }
}
