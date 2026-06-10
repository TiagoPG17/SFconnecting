<?php

declare(strict_types=1);

namespace Tests\Unit\SFconnecting\Services;

use App\Domain\Clientes\Models\Cliente;
use App\Domain\Clientes\Repositories\ClienteRepository;
use App\Domain\Pipeline\Models\PipelineEstado;
use App\Domain\Seguimientos\Repositories\SeguimientoRepository;
use App\Domain\Prospectos\DTOs\ActualizarProspectoDTO;
use App\Domain\Prospectos\DTOs\ConvertirProspectoDTO;
use App\Domain\Prospectos\DTOs\CrearProspectoDTO;
use App\Domain\Prospectos\Exceptions\ConversionProspectoException;
use App\Domain\Prospectos\Exceptions\ProspectoDuplicadoException;
use App\Domain\Prospectos\Models\Prospecto;
use App\Domain\Prospectos\Repositories\ProspectoRepository;
use App\Domain\Prospectos\Services\ProspectoService;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProspectoServiceTest extends TestCase
{
    use RefreshDatabase;

    private ProspectoService $service;
    private PipelineEstado $estadoInicial;

    protected function setUp(): void
    {
        parent::setUp();

        $this->estadoInicial = PipelineEstado::create([
            'nombre'           => 'Nuevo Lead',
            'slug'             => 'nuevo-lead',
            'tipo'             => 'prospecto',
            'color'            => '#6B7280',
            'orden'            => 1,
            'porcentaje_cierre' => 5,
            'es_final'         => false,
            'es_ganado'        => false,
            'es_perdido'       => false,
            'activo'           => true,
        ]);

        $this->service = new ProspectoService(
            new ProspectoRepository(),
            new ClienteRepository(),
            new SeguimientoRepository(),
        );
    }

    // â€” CREAR â€”

    public function test_crea_prospecto_exitosamente(): void
    {
        $user = User::factory()->create();
        $dto  = $this->dtoBase($user->id);

        $prospecto = $this->service->crear($dto);

        $this->assertInstanceOf(Prospecto::class, $prospecto);
        $this->assertSame('Empresa ABC', $prospecto->empresa);
        $this->assertStringStartsWith('PROS-', $prospecto->codigo);
        $this->assertDatabaseHas('sf_prospectos', ['empresa' => 'Empresa ABC']);
    }

    public function test_genera_codigo_correlativo(): void
    {
        $user = User::factory()->create();

        $p1 = $this->service->crear($this->dtoBase($user->id, email: 'a@test.co'));
        $p2 = $this->service->crear($this->dtoBase($user->id, email: 'b@test.co', empresa: 'Empresa B'));

        $this->assertNotSame($p1->codigo, $p2->codigo);
    }

    public function test_lanza_excepcion_si_email_duplicado(): void
    {
        $user = User::factory()->create();
        $this->service->crear($this->dtoBase($user->id, email: 'dup@empresa.co'));

        $this->expectException(ProspectoDuplicadoException::class);

        $this->service->crear($this->dtoBase($user->id, email: 'dup@empresa.co', empresa: 'Otra'));
    }

    public function test_permite_crear_sin_email(): void
    {
        $user      = User::factory()->create();
        $prospecto = $this->service->crear($this->dtoBase($user->id));

        $this->assertNull($prospecto->email);
    }

    // â€” ACTUALIZAR â€”

    public function test_actualiza_prospecto_correctamente(): void
    {
        $user      = User::factory()->create();
        $prospecto = $this->service->crear($this->dtoBase($user->id));
        $dto       = ActualizarProspectoDTO::fromArray(['empresa' => 'Nueva Empresa', 'valor_estimado' => 50000]);

        $actualizado = $this->service->actualizar($prospecto, $dto);

        $this->assertSame('Nueva Empresa', $actualizado->empresa);
        $this->assertSame('50000.00', $actualizado->valor_estimado);
    }

    public function test_lanza_excepcion_si_nuevo_email_ya_existe(): void
    {
        $user = User::factory()->create();
        $this->service->crear($this->dtoBase($user->id, email: 'ocupado@empresa.co'));
        $prospecto = $this->service->crear($this->dtoBase($user->id, email: 'libre@empresa.co', empresa: 'Otra'));

        $this->expectException(ProspectoDuplicadoException::class);

        $this->service->actualizar($prospecto, ActualizarProspectoDTO::fromArray(['email' => 'ocupado@empresa.co']));
    }

    public function test_cambio_de_estado_registra_auditoria(): void
    {
        $user = User::factory()->create();
        $nuevoEstado = PipelineEstado::create([
            'nombre' => 'Calificado', 'slug' => 'calificado', 'tipo' => 'prospecto',
            'color' => '#8B5CF6', 'orden' => 2, 'porcentaje_cierre' => 30,
            'es_final' => false, 'es_ganado' => false, 'es_perdido' => false, 'activo' => true,
        ]);

        $prospecto = $this->service->crear($this->dtoBase($user->id));
        $this->service->actualizar($prospecto, ActualizarProspectoDTO::fromArray([
            'estado_pipeline_id' => $nuevoEstado->id,
        ]));

        $this->assertDatabaseHas('sf_auditoria_pipeline', [
            'auditable_id'   => $prospecto->id,
            'evento'         => 'cambio_estado',
            'estado_anterior' => 'Nuevo Lead',
            'estado_nuevo'   => 'Calificado',
        ]);
    }

    // â€” CONVERTIR â€”

    public function test_convierte_prospecto_en_cliente(): void
    {
        $user      = User::factory()->create();
        $prospecto = $this->service->crear($this->dtoBase($user->id, email: 'conv@empresa.co'));

        $dto = ConvertirProspectoDTO::fromArray([
            'usuario_id'  => $user->id,
            'razon_social' => 'Empresa ABC SA',
            'nit'         => '900111222',
        ]);

        $convertido = $this->service->convertirEnCliente($prospecto, $dto);

        $this->assertTrue($convertido->estaConvertido());
        $this->assertNotNull($convertido->convertido_cliente_id);
        $this->assertDatabaseHas('clientes', ['nit' => '900111222']);
        $this->assertDatabaseHas('sf_auditoria_pipeline', [
            'auditable_id' => $prospecto->id,
            'evento'       => 'conversion_lead',
        ]);
    }

    public function test_conversion_migra_seguimientos_del_prospecto(): void
    {
        $user      = User::factory()->create();
        $prospecto = $this->service->crear($this->dtoBase($user->id, email: 'migra@empresa.co'));

        // Seguimiento previo ligado al prospecto
        \App\Domain\Seguimientos\Models\Seguimiento::create([
            'prospecto_id'      => $prospecto->id,
            'cliente_id'        => null,
            'user_id'           => $user->id,
            'tipo'              => 'llamada',
            'resultado'         => 'exitoso',
            'descripcion'       => 'Llamada inicial al lead',
            'fecha_seguimiento' => now()->subDay(),
        ]);

        $dto = ConvertirProspectoDTO::fromArray([
            'usuario_id'   => $user->id,
            'razon_social' => 'Empresa Migrada SA',
            'nit'          => '900555666',
        ]);

        $convertido = $this->service->convertirEnCliente($prospecto, $dto);

        $this->assertDatabaseHas('seguimientos', [
            'prospecto_id' => $prospecto->id,
            'cliente_id'   => $convertido->convertido_cliente_id,
        ]);
    }

    public function test_auditoria_conversion_incluye_conteo_seguimientos(): void
    {
        $user      = User::factory()->create();
        $prospecto = $this->service->crear($this->dtoBase($user->id, email: 'audit@empresa.co'));

        \App\Domain\Seguimientos\Models\Seguimiento::create([
            'prospecto_id' => $prospecto->id, 'cliente_id' => null,
            'user_id' => $user->id, 'tipo' => 'email', 'resultado' => 'exitoso',
            'descripcion' => 'Email de seguimiento', 'fecha_seguimiento' => now()->subDay(),
        ]);

        $dto = ConvertirProspectoDTO::fromArray([
            'usuario_id' => $user->id, 'nit' => '900777888',
        ]);

        $convertido = $this->service->convertirEnCliente($prospecto, $dto);

        $this->assertDatabaseHas('sf_auditoria_pipeline', [
            'auditable_id' => $prospecto->id,
            'evento'       => 'conversion_lead',
        ]);
    }

    public function test_lanza_excepcion_si_ya_convertido(): void
    {
        $user      = User::factory()->create();
        $cliente   = Cliente::factory()->create();
        $prospecto = Prospecto::create([
            'codigo'             => 'PROS-00001',
            'empresa'            => 'Ya Convertida',
            'contacto'           => 'Juan',
            'estado_pipeline_id' => $this->estadoInicial->id,
            'asesor_id'          => $user->id,
            'convertido_cliente_id' => $cliente->id,
            'convertido_por'     => $user->id,
            'fecha_conversion'   => now(),
            'activo'             => true,
        ]);

        $this->expectException(ConversionProspectoException::class);

        $this->service->convertirEnCliente($prospecto, ConvertirProspectoDTO::fromArray([
            'usuario_id' => $user->id,
        ]));
    }

    // â€” ELIMINAR â€”

    public function test_elimina_prospecto_con_soft_delete(): void
    {
        $user      = User::factory()->create();
        $prospecto = $this->service->crear($this->dtoBase($user->id));

        $this->service->eliminar($prospecto);

        $this->assertSoftDeleted('sf_prospectos', ['id' => $prospecto->id]);
    }

    // â€” PROBABILIDAD EFECTIVA â€”

    public function test_probabilidad_hereda_del_estado_si_no_hay_override(): void
    {
        $user      = User::factory()->create();
        $prospecto = $this->service->crear($this->dtoBase($user->id));
        $prospecto->load('estadoPipeline');

        $this->assertSame(5, $prospecto->probabilidadEfectiva());
    }

    public function test_probabilidad_manual_tiene_prioridad_sobre_estado(): void
    {
        $user      = User::factory()->create();
        $prospecto = $this->service->crear($this->dtoBase($user->id));

        $actualizado = $this->service->actualizar(
            $prospecto,
            ActualizarProspectoDTO::fromArray(['probabilidad_cierre' => 80])
        );

        $this->assertSame(80, $actualizado->probabilidadEfectiva());
    }

    // â€” HELPER â€”

    private function dtoBase(int $asesorId, ?string $email = null, string $empresa = 'Empresa ABC'): CrearProspectoDTO
    {
        return CrearProspectoDTO::fromArray([
            'empresa'           => $empresa,
            'contacto'          => 'Juan PÃ©rez',
            'estado_pipeline_id' => $this->estadoInicial->id,
            'asesor_id'         => $asesorId,
            'email'             => $email,
        ]);
    }
}

