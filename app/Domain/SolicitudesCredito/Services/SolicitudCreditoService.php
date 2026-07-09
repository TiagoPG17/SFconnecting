<?php

declare(strict_types=1);

namespace App\Domain\SolicitudesCredito\Services;

use App\Domain\Auditoria\Models\ActividadLog;
use App\Domain\Clientes\Models\Cliente;
use App\Domain\ERP\Contracts\ERPRepositoryInterface;
use App\Domain\ERP\Exceptions\ERPConnectionException;
use App\Domain\Negocios\Models\AuditoriaPipeline;
use App\Domain\Negocios\Repositories\NegocioRepositoryInterface;
use App\Domain\Pipeline\Models\PipelineEstado;
use App\Domain\SolicitudesCredito\DTOs\CrearSolicitudCreditoDTO;
use App\Domain\SolicitudesCredito\DTOs\DecidirSolicitudCreditoDTO;
use App\Domain\SolicitudesCredito\Exceptions\SolicitudCreditoException;
use App\Domain\SolicitudesCredito\Models\SolicitudCredito;
use App\Domain\SolicitudesCredito\Repositories\SolicitudCreditoRepositoryInterface;
use Throwable;

class SolicitudCreditoService
{
    private const DECISIONES = [
        'aprobada'             => 'credito-aprobada',
        'aprobada_condiciones' => 'credito-aprobada-condiciones',
        'rechazada'            => 'credito-rechazada',
    ];

    public function __construct(
        private readonly SolicitudCreditoRepositoryInterface $repo,
        private readonly NegocioRepositoryInterface $negocioRepo,
        private readonly ERPRepositoryInterface $erp,
    ) {}

    public function crear(CrearSolicitudCreditoDTO $dto): SolicitudCredito
    {
        $negocio = $this->negocioRepo->buscarPorId($dto->negocioId);

        if ($negocio === null || $negocio->cliente_id === null) {
            throw SolicitudCreditoException::negocioSinCliente();
        }

        if ($this->repo->tieneSolicitudActiva($negocio->id)) {
            throw SolicitudCreditoException::solicitudActivaExistente();
        }

        $cliente = Cliente::findOrFail($negocio->cliente_id);
        $dossier = $this->construirDossierErp($cliente->nit);

        $estadoRadicada = PipelineEstado::where('tipo', 'solicitud_credito')
            ->where('slug', 'credito-radicada')
            ->firstOrFail();

        $solicitud = $this->repo->crear($dto, $cliente->id, $estadoRadicada->id, $dossier);

        AuditoriaPipeline::create([
            'auditable_type'  => SolicitudCredito::class,
            'auditable_id'    => $solicitud->id,
            'evento'          => 'cambio_estado',
            'estado_anterior' => null,
            'estado_nuevo'    => $estadoRadicada->nombre,
            'datos_nuevos'    => $dto->toArray(),
            'usuario_id'      => $dto->asesorId,
        ]);

        ActividadLog::registrar(
            'crear',
            'solicitudes_credito',
            "Solicitud de crédito #{$solicitud->id} radicada para el negocio '{$negocio->nombre_negocio}'",
            $dto->asesorId,
        );

        return $solicitud;
    }

    public function decidir(SolicitudCredito $solicitud, DecidirSolicitudCreditoDTO $dto): SolicitudCredito
    {
        if ($solicitud->estaFinalizada()) {
            throw SolicitudCreditoException::yaFinalizada();
        }

        if ($dto->decision === 'aprobada_condiciones' && empty($dto->condiciones)) {
            throw SolicitudCreditoException::condicionesRequeridas();
        }

        $slug = self::DECISIONES[$dto->decision] ?? null;

        if ($slug === null) {
            throw SolicitudCreditoException::decisionInvalida($dto->decision);
        }

        $estadoAnterior = $solicitud->pipelineEstado?->nombre;
        $nuevoEstado    = PipelineEstado::where('tipo', 'solicitud_credito')
            ->where('slug', $slug)
            ->firstOrFail();

        $actualizada = $this->repo->decidir($solicitud, $dto, $nuevoEstado->id);

        $evento = match ($dto->decision) {
            'aprobada'             => 'solicitud_credito_aprobada',
            'aprobada_condiciones' => 'solicitud_credito_aprobada_condiciones',
            'rechazada'            => 'solicitud_credito_rechazada',
        };

        AuditoriaPipeline::create([
            'auditable_type'  => SolicitudCredito::class,
            'auditable_id'    => $actualizada->id,
            'evento'          => $evento,
            'estado_anterior' => $estadoAnterior,
            'estado_nuevo'    => $nuevoEstado->nombre,
            'datos_nuevos'    => $dto->toArray(),
            'usuario_id'      => $dto->revisadoPor,
        ]);

        ActividadLog::registrar(
            'actualizar',
            'solicitudes_credito',
            "Solicitud de crédito #{$actualizada->id} marcada como '{$nuevoEstado->nombre}'",
            $dto->revisadoPor,
        );

        return $actualizada;
    }

    public function buscarPorId(int $id): ?SolicitudCredito
    {
        return $this->repo->buscarPorId($id);
    }

    private function construirDossierErp(?string $nit): array
    {
        if (empty($nit)) {
            throw SolicitudCreditoException::clienteNoEncontradoEnErp();
        }

        if (! $this->erp->isAvailable()) {
            throw SolicitudCreditoException::erpNoDisponible();
        }

        try {
            $cliente = $this->erp->clientePorNit($nit);
            $cartera = $this->erp->carteraPorNit($nit);
        } catch (ERPConnectionException|Throwable) {
            throw SolicitudCreditoException::erpNoDisponible();
        }

        if ($cliente === null) {
            throw SolicitudCreditoException::clienteNoEncontradoEnErp();
        }

        $saldoTotal = array_sum(array_column($cartera, 'SALDO'));
        $cupoCredito = (float) ($cliente['CUPO_CREDITO'] ?? 0);

        return [
            'consultado_en'     => now()->toDateTimeString(),
            'nit'               => $nit,
            'cupo_credito'      => $cupoCredito,
            'fecha_cupo'        => $cliente['FECHA_CUPO'] ?? null,
            'calificacion'      => $cliente['CALIFICACION_CLIENTE'] ?? null,
            'condicion_pago'    => $cliente['DESC_CONDICION_PAGO'] ?? null,
            'dias_vcto'         => $cliente['DIAS_VCTO'] ?? null,
            'cuotas'            => $cliente['CUOTAS'] ?? null,
            'tasa_pronto_pago'  => $cliente['TASA_PRONTO_PAGO'] ?? null,
            'tasa_mora'         => $cliente['TASA_MORA'] ?? null,
            'bloqueado'         => (bool) ($cliente['BLOQUEADO'] ?? false),
            'ind_bloqueo_cupo'  => $cliente['IND_BLOQUEO_CUPO'] ?? null,
            'ind_bloqueo_mora'  => $cliente['IND_BLOQUEO_MORA'] ?? null,
            'motivo_bloqueo'    => $cliente['MOTIVO_BLOQUEO'] ?? null,
            'dias_gracia'       => $cliente['DIAS_GRACIA'] ?? null,
            'cartera'           => $cartera,
            'saldo_total'       => $saldoTotal,
            'cupo_disponible'   => $cupoCredito - $saldoTotal,
        ];
    }
}
