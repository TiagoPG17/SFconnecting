<?php

declare(strict_types=1);

namespace App\Domain\SolicitudesCredito\Exceptions;

use RuntimeException;

class SolicitudCreditoException extends RuntimeException
{
    public static function negocioSinCliente(): self
    {
        return new self('El negocio no está vinculado a un cliente; solo se puede radicar una solicitud de crédito para negocios asociados a un cliente con NIT en SIESA.');
    }

    public static function erpNoDisponible(): self
    {
        return new self('No fue posible consultar el ERP para construir el dossier de crédito. Intenta nuevamente en unos minutos.');
    }

    public static function clienteNoEncontradoEnErp(): self
    {
        return new self('El cliente no fue encontrado en el ERP por NIT; verifica el NIT registrado.');
    }

    public static function solicitudActivaExistente(): self
    {
        return new self('Este negocio ya tiene una solicitud de crédito en trámite.');
    }

    public static function yaFinalizada(): self
    {
        return new self('La solicitud ya está en un estado final y no puede modificarse.');
    }

    public static function condicionesRequeridas(): self
    {
        return new self('Debes indicar las condiciones al aprobar la solicitud con condiciones.');
    }

    public static function decisionInvalida(string $decision): self
    {
        return new self("La decisión '{$decision}' no es válida.");
    }
}
