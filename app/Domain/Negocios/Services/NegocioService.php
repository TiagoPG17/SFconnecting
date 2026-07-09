<?php

declare(strict_types=1);

namespace App\Domain\Negocios\Services;

use App\Domain\Auditoria\Models\ActividadLog;
use App\Domain\Dashboard\Models\VendedorEquivalencia;
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

        $dto = CrearNegocioDTO::fromArray(array_merge(
            $dto->toArray(),
            ['compania' => $this->resolverCompania($dto)]
        ));

        $negocio = $this->repo->crear($dto);

        $this->registrarAuditoria($negocio, 'cambio_estado', null, $negocio->pipelineEstado?->nombre, $dto->toArray());

        ActividadLog::registrar('crear', 'negocios', "Negocio '{$negocio->nombre_negocio}' creado (#{$negocio->id})", $negocio->asesor_id);

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

        ActividadLog::registrar('actualizar', 'negocios', "Negocio '{$negocio->nombre_negocio}' actualizado (#{$negocio->id})", $negocio->asesor_id);

        return $actualizado;
    }

    public function eliminar(Negocio $negocio): void
    {
        ActividadLog::registrar('eliminar', 'negocios', "Negocio '{$negocio->nombre_negocio}' eliminado (#{$negocio->id})", $negocio->asesor_id);

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

    private function resolverCompania(CrearNegocioDTO $dto): ?int
    {
        $companiasAsesor = VendedorEquivalencia::where('asesor_id', $dto->asesorId)
            ->where('activo', true)
            ->pluck('compania')
            ->unique()
            ->values();

        if ($companiasAsesor->count() === 1) {
            return (int) $companiasAsesor->first();
        }

        if ($companiasAsesor->count() > 1) {
            if ($dto->compania === null) {
                throw NegocioException::companiaRequerida();
            }

            if (! $companiasAsesor->contains($dto->compania)) {
                throw NegocioException::companiaNoPermitida();
            }

            return $dto->compania;
        }

        return null;
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
