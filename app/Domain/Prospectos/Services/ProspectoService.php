<?php

declare(strict_types=1);

namespace App\Domain\Prospectos\Services;

use App\Domain\Auditoria\Models\ActividadLog;
use App\Domain\Clientes\DTOs\CrearClienteDTO;
use App\Domain\Clientes\Repositories\ClienteRepositoryInterface;
use App\Domain\Dashboard\Models\VendedorEquivalencia;
use App\Domain\Dashboard\Repositories\DashboardVendedorRepositoryInterface;
use App\Domain\Negocios\Models\AuditoriaPipeline;
use App\Domain\Seguimientos\Repositories\SeguimientoRepositoryInterface;
use App\Domain\Prospectos\DTOs\ActualizarProspectoDTO;
use App\Domain\Prospectos\DTOs\ConvertirProspectoDTO;
use App\Domain\Prospectos\DTOs\CrearProspectoDTO;
use App\Domain\Prospectos\Exceptions\ConversionProspectoException;
use App\Domain\Prospectos\Exceptions\ProspectoDuplicadoException;
use App\Domain\Prospectos\Exceptions\ProspectoException;
use App\Domain\Prospectos\Models\Prospecto;
use App\Domain\Prospectos\Repositories\ProspectoRepositoryInterface;
use App\Domain\SolicitudesCredito\DTOs\CrearSolicitudCreditoDTO;
use App\Domain\SolicitudesCredito\Exceptions\SolicitudCreditoException;
use App\Domain\SolicitudesCredito\Services\SolicitudCreditoService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ProspectoService
{
    public function __construct(
        private readonly ProspectoRepositoryInterface $repo,
        private readonly ClienteRepositoryInterface $clienteRepo,
        private readonly SeguimientoRepositoryInterface $seguimientoRepo,
        private readonly DashboardVendedorRepositoryInterface $vendedorRepo,
        private readonly SolicitudCreditoService $solicitudCreditoService,
    ) {}

    public function crear(CrearProspectoDTO $dto): Prospecto
    {
        if ($dto->email !== null && $this->repo->existeEmail($dto->email)) {
            throw ProspectoDuplicadoException::porEmail($dto->email);
        }

        $dto = CrearProspectoDTO::fromArray(array_merge(
            $dto->toArray(),
            ['compania' => $this->resolverCompania($dto)]
        ));

        $codigo = $this->repo->proximosCodigo();
        $prospecto = $this->repo->crear($dto, $codigo);

        ActividadLog::registrar('crear', 'prospectos', "Prospecto '{$prospecto->empresa}' creado (#{$prospecto->codigo})", $prospecto->asesor_id);

        return $prospecto;
    }

    public function actualizar(Prospecto $prospecto, ActualizarProspectoDTO $dto): Prospecto
    {
        if ($dto->email !== null && $this->repo->existeEmail($dto->email, $prospecto->id)) {
            throw ProspectoDuplicadoException::porEmail($dto->email);
        }

        $estadoAnteriorId = $prospecto->estado_pipeline_id;
        $estadoAnterior   = $prospecto->estadoPipeline?->nombre;

        $actualizado = $this->repo->actualizar($prospecto, $dto);

        if ($dto->estadoPipelineId !== null && $dto->estadoPipelineId !== $estadoAnteriorId) {
            $this->registrarAuditoria(
                $actualizado,
                'cambio_estado',
                $estadoAnterior,
                $actualizado->estadoPipeline?->nombre,
                $dto->toArray(),
            );
        }

        ActividadLog::registrar('actualizar', 'prospectos', "Prospecto '{$prospecto->empresa}' actualizado (#{$prospecto->codigo})", $prospecto->asesor_id);

        return $actualizado;
    }

    public function convertirEnCliente(Prospecto $prospecto, ConvertirProspectoDTO $dto): Prospecto
    {
        if ($prospecto->estaConvertido()) {
            throw ConversionProspectoException::yaConvertido($prospecto->empresa);
        }

        return DB::transaction(function () use ($prospecto, $dto) {
            $clienteDto = new CrearClienteDTO(
                razonSocial: $dto->razonSocial ?? $prospecto->empresa,
                nit:         $dto->nit ?? 'PEND-' . $prospecto->id,
                userId:      $dto->usuarioId,
                compania:    $prospecto->compania ?? $this->vendedorRepo->companiaPrincipal($dto->usuarioId),
                email:       $prospecto->email,
                telefono:    $prospecto->telefono,
                ciudad:      $dto->ciudad,
                direccion:   $dto->direccion,
                estado:      'activo',
                datosCarga:  $dto->datosCarga,
            );

            $cliente = $this->clienteRepo->crear($clienteDto);

            if ($dto->solicitaCupo) {
                $this->radicarSolicitudCupo($cliente->id, $dto);
            }

            $seguimientosMigrados = $this->seguimientoRepo->migrarACliente($prospecto->id, $cliente->id);

            $convertido = $this->repo->marcarConvertido($prospecto, $cliente->id, $dto->usuarioId);

            $this->registrarAuditoria($convertido, 'conversion_lead', $prospecto->estadoPipeline?->nombre, 'convertido', [
                'cliente_id'            => $cliente->id,
                'empresa'               => $prospecto->empresa,
                'seguimientos_migrados' => $seguimientosMigrados,
            ]);

            ActividadLog::registrar('convertir', 'prospectos', "Prospecto '{$prospecto->empresa}' convertido a cliente (#{$prospecto->codigo})", $dto->usuarioId);

            return $convertido;
        });
    }

    /**
     * El cliente recién convertido normalmente no está registrado en SIESA todavía
     * (contabilidad lo hace manualmente después de revisar los datos de carga), así
     * que si la solicitud de cupo falla no debe tumbar la conversión: el comercial ya
     * tiene su cliente y puede volver a radicar el cupo después desde otro lado.
     */
    private function radicarSolicitudCupo(int $clienteId, ConvertirProspectoDTO $dto): void
    {
        try {
            $this->solicitudCreditoService->crear(new CrearSolicitudCreditoDTO(
                negocioId:              null,
                clienteId:              $clienteId,
                asesorId:               $dto->usuarioId,
                montoSolicitado:        $dto->montoSolicitado ?? 0,
                plazoSolicitadoDias:    $dto->plazoSolicitadoDias,
                justificacion:          $dto->justificacionCupo,
                referenciasComerciales: $dto->referenciasComerciales,
                inventarioConsignacion: $dto->inventarioConsignacion,
            ));
        } catch (SolicitudCreditoException $e) {
            Log::warning('[ConvertirProspecto] No se pudo radicar la solicitud de cupo automáticamente.', [
                'cliente_id' => $clienteId,
                'error'      => $e->getMessage(),
            ]);
        }
    }

    public function eliminar(Prospecto $prospecto): void
    {
        ActividadLog::registrar('eliminar', 'prospectos', "Prospecto '{$prospecto->empresa}' eliminado (#{$prospecto->codigo})", $prospecto->asesor_id);

        $this->repo->eliminar($prospecto);
    }

    public function buscarPorId(int $id): ?Prospecto
    {
        return $this->repo->buscarPorId($id);
    }

    private function resolverCompania(CrearProspectoDTO $dto): ?int
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
                throw ProspectoException::companiaRequerida();
            }

            if (! $companiasAsesor->contains($dto->compania)) {
                throw ProspectoException::companiaNoPermitida();
            }

            return $dto->compania;
        }

        return null;
    }

    private function registrarAuditoria(
        Prospecto $prospecto,
        string $evento,
        ?string $estadoAnterior,
        ?string $estadoNuevo,
        array $datos = [],
    ): void {
        AuditoriaPipeline::create([
            'auditable_type'  => Prospecto::class,
            'auditable_id'    => $prospecto->id,
            'evento'          => $evento,
            'estado_anterior' => $estadoAnterior,
            'estado_nuevo'    => $estadoNuevo,
            'datos_nuevos'    => $datos,
            'usuario_id'      => $prospecto->asesor_id,
        ]);
    }
}
