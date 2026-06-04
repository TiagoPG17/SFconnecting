<?php

declare(strict_types=1);

namespace App\Domain\Prospectos\Services;

use App\Domain\Clientes\DTOs\CrearClienteDTO;
use App\Domain\Clientes\Repositories\ClienteRepositoryInterface;
use App\Domain\Negocios\Models\AuditoriaPipeline;
use App\Domain\Seguimientos\Repositories\SeguimientoRepositoryInterface;
use App\Domain\Prospectos\DTOs\ActualizarProspectoDTO;
use App\Domain\Prospectos\DTOs\ConvertirProspectoDTO;
use App\Domain\Prospectos\DTOs\CrearProspectoDTO;
use App\Domain\Prospectos\Exceptions\ConversionProspectoException;
use App\Domain\Prospectos\Exceptions\ProspectoDuplicadoException;
use App\Domain\Prospectos\Models\Prospecto;
use App\Domain\Prospectos\Repositories\ProspectoRepositoryInterface;
use Illuminate\Support\Facades\DB;

class ProspectoService
{
    public function __construct(
        private readonly ProspectoRepositoryInterface $repo,
        private readonly ClienteRepositoryInterface $clienteRepo,
        private readonly SeguimientoRepositoryInterface $seguimientoRepo,
    ) {}

    public function crear(CrearProspectoDTO $dto): Prospecto
    {
        if ($dto->email !== null && $this->repo->existeEmail($dto->email)) {
            throw ProspectoDuplicadoException::porEmail($dto->email);
        }

        $codigo = $this->repo->proximosCodigo();

        return $this->repo->crear($dto, $codigo);
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
                email:       $prospecto->email,
                telefono:    $prospecto->telefono,
                ciudad:      $dto->ciudad,
                direccion:   $dto->direccion,
                estado:      'activo',
            );

            $cliente = $this->clienteRepo->crear($clienteDto);

            $seguimientosMigrados = $this->seguimientoRepo->migrarACliente($prospecto->id, $cliente->id);

            $convertido = $this->repo->marcarConvertido($prospecto, $cliente->id, $dto->usuarioId);

            $this->registrarAuditoria(
                $convertido,
                'conversion_lead',
                $prospecto->estadoPipeline?->nombre,
                'convertido',
                [
                    'cliente_id'            => $cliente->id,
                    'empresa'               => $prospecto->empresa,
                    'seguimientos_migrados' => $seguimientosMigrados,
                ],
            );

            return $convertido;
        });
    }

    public function eliminar(Prospecto $prospecto): void
    {
        $this->repo->eliminar($prospecto);
    }

    public function buscarPorId(int $id): ?Prospecto
    {
        return $this->repo->buscarPorId($id);
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
