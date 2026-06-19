<?php

declare(strict_types=1);

namespace App\Domain\Negocios\Services;

use App\Domain\Auditoria\Models\ActividadLog;
use App\Domain\Negocios\DTOs\ActualizarNegocioDTO;
use App\Domain\Negocios\DTOs\CrearNegocioDTO;
use App\Domain\Negocios\Exceptions\NegocioException;
use App\Domain\Negocios\Models\AuditoriaPipeline;
use App\Domain\Negocios\Models\Negocio;
use App\Domain\Negocios\Repositories\NegocioRepositoryInterface;
use App\Domain\Pipeline\Models\PipelineEstado;

class NegocioService
{
    public function __construct(
        private readonly NegocioRepositoryInterface $repo,
    ) {}

    public function crear(CrearNegocioDTO $dto): Negocio
    {
        if ($dto->prospectoId === null && $dto->clienteId === null) {
            throw NegocioException::sinVinculo();
        }

        $negocio = $this->repo->crear($dto);

        $this->registrarAuditoria($negocio, 'cambio_estado', null, $negocio->pipelineEstado?->nombre, $dto->toArray());

        ActividadLog::registrar('crear', 'negocios', "Negocio '{$negocio->nombre}' creado (#{$negocio->codigo})", $negocio->asesor_id);

        return $negocio;
    }

    public function actualizar(Negocio $negocio, ActualizarNegocioDTO $dto): Negocio
    {
        $estadoAnteriorId = $negocio->pipeline_estado_id;
        $estadoAnterior   = $negocio->pipelineEstado?->nombre;

        if ($dto->pipelineEstadoId !== null) {
            /** @var PipelineEstado|null $nuevoEstado */
            $nuevoEstado = PipelineEstado::find($dto->pipelineEstadoId);

            if ($nuevoEstado?->es_perdido && $dto->motivoPerdidaId === null) {
                throw NegocioException::perdidoSinMotivo();
            }

            if ($nuevoEstado?->es_ganado) {
                $dto = ActualizarNegocioDTO::fromArray(array_merge(
                    $dto->toArray(),
                    ['fecha_cierre_real' => now()->toDateString()]
                ));
            }
        }

        $actualizado = $this->repo->actualizar($negocio, $dto);

        if ($dto->pipelineEstadoId !== null && $dto->pipelineEstadoId !== $estadoAnteriorId) {
            $evento = match (true) {
                $actualizado->pipelineEstado?->es_ganado  => 'negocio_ganado',
                $actualizado->pipelineEstado?->es_perdido => 'negocio_perdido',
                default                                    => 'cambio_estado',
            };

            $this->registrarAuditoria(
                $actualizado,
                $evento,
                $estadoAnterior,
                $actualizado->pipelineEstado?->nombre,
                $dto->toArray(),
            );
        }

        ActividadLog::registrar('actualizar', 'negocios', "Negocio '{$negocio->nombre}' actualizado (#{$negocio->codigo})", $negocio->asesor_id);

        return $actualizado;
    }

    public function eliminar(Negocio $negocio): void
    {
        ActividadLog::registrar('eliminar', 'negocios', "Negocio '{$negocio->nombre}' eliminado (#{$negocio->codigo})", $negocio->asesor_id);

        $this->repo->eliminar($negocio);
    }

    public function buscarPorId(int $id): ?Negocio
    {
        return $this->repo->buscarPorId($id);
    }

    public function forecast(array $filtros = []): array
    {
        return $this->repo->forecast($filtros);
    }

    private function registrarAuditoria(
        Negocio $negocio,
        string $evento,
        ?string $estadoAnterior,
        ?string $estadoNuevo,
        array $datos = [],
    ): void {
        AuditoriaPipeline::create([
            'auditable_type'  => Negocio::class,
            'auditable_id'    => $negocio->id,
            'evento'          => $evento,
            'estado_anterior' => $estadoAnterior,
            'estado_nuevo'    => $estadoNuevo,
            'datos_nuevos'    => $datos,
            'usuario_id'      => $negocio->asesor_id,
        ]);
    }
}
