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
        public readonly ?array $datosCarga = null,
        public readonly bool $solicitaCupo = false,
        public readonly ?float $montoSolicitado = null,
        public readonly ?int $plazoSolicitadoDias = null,
        public readonly ?string $justificacionCupo = null,
        public readonly ?array $referenciasComerciales = null,
        public readonly ?bool $inventarioConsignacion = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            usuarioId:              (int) $data['usuario_id'],
            razonSocial:            $data['razon_social'] ?? null,
            nit:                    $data['nit'] ?? null,
            ciudad:                 $data['ciudad'] ?? null,
            direccion:              $data['direccion'] ?? null,
            datosCarga:             $data['datos_carga'] ?? null,
            solicitaCupo:           (bool) ($data['solicita_cupo'] ?? false),
            montoSolicitado:        isset($data['monto_solicitado']) ? (float) $data['monto_solicitado'] : null,
            plazoSolicitadoDias:    isset($data['plazo_solicitado_dias']) ? (int) $data['plazo_solicitado_dias'] : null,
            justificacionCupo:      $data['justificacion_cupo'] ?? null,
            referenciasComerciales: $data['referencias_comerciales'] ?? null,
            inventarioConsignacion: isset($data['inventario_consignacion']) ? (bool) $data['inventario_consignacion'] : null,
        );
    }
}
