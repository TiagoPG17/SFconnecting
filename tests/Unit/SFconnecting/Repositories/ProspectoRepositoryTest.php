<?php

declare(strict_types=1);

namespace Tests\Unit\SFconnecting\Repositories;

use App\Domain\Pipeline\Models\PipelineEstado;
use App\Domain\Prospectos\DTOs\ActualizarProspectoDTO;
use App\Domain\Prospectos\DTOs\CrearProspectoDTO;
use App\Domain\Prospectos\Models\Prospecto;
use App\Domain\Prospectos\Repositories\ProspectoRepository;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProspectoRepositoryTest extends TestCase
{
    use RefreshDatabase;

    private ProspectoRepository $repo;
    private PipelineEstado $estado;

    protected function setUp(): void
    {
        parent::setUp();

        $this->estado = PipelineEstado::create([
            'nombre' => 'Nuevo Lead', 'slug' => 'nuevo-lead-rep', 'tipo' => 'prospecto',
            'color' => '#6B7280', 'orden' => 1, 'porcentaje_cierre' => 5,
            'es_final' => false, 'es_ganado' => false, 'es_perdido' => false, 'activo' => true,
        ]);

        $this->repo = new ProspectoRepository();
    }

    public function test_crea_prospecto_con_codigo(): void
    {
        $user   = User::factory()->create();
        $dto    = $this->dtoBase($user->id);
        $codigo = $this->repo->proximosCodigo();

        $prospecto = $this->repo->crear($dto, $codigo);

        $this->assertSame($codigo, $prospecto->codigo);
        $this->assertDatabaseHas('sf_prospectos', ['codigo' => $codigo]);
    }

    public function test_genera_codigos_unicos_correlativos(): void
    {
        $user    = User::factory()->create();
        $codigo1 = $this->repo->proximosCodigo();
        $this->repo->crear($this->dtoBase($user->id, 'a@test.co'), $codigo1);

        $codigo2 = $this->repo->proximosCodigo();
        $this->repo->crear($this->dtoBase($user->id, 'b@test.co', 'Empresa B'), $codigo2);

        $this->assertNotSame($codigo1, $codigo2);
    }

    public function test_actualiza_prospecto(): void
    {
        $user      = User::factory()->create();
        $prospecto = $this->repo->crear($this->dtoBase($user->id), 'PROS-00001');

        $actualizado = $this->repo->actualizar(
            $prospecto,
            ActualizarProspectoDTO::fromArray(['empresa' => 'Empresa Actualizada'])
        );

        $this->assertSame('Empresa Actualizada', $actualizado->empresa);
    }

    public function test_elimina_con_soft_delete(): void
    {
        $user      = User::factory()->create();
        $prospecto = $this->repo->crear($this->dtoBase($user->id), 'PROS-00001');

        $this->repo->eliminar($prospecto);

        $this->assertSoftDeleted('sf_prospectos', ['id' => $prospecto->id]);
    }

    public function test_busca_por_id_con_relaciones(): void
    {
        $user      = User::factory()->create();
        $prospecto = $this->repo->crear($this->dtoBase($user->id), 'PROS-00001');

        $encontrado = $this->repo->buscarPorId($prospecto->id);

        $this->assertNotNull($encontrado);
        $this->assertTrue($encontrado->relationLoaded('estadoPipeline'));
    }

    public function test_pagina_con_filtro_por_estado(): void
    {
        $user = User::factory()->create();
        $this->repo->crear($this->dtoBase($user->id, 'a@test.co'), 'PROS-00001');
        $this->repo->crear($this->dtoBase($user->id, 'b@test.co', 'Empresa B'), 'PROS-00002');

        $resultado = $this->repo->paginar(['estado_pipeline_id' => $this->estado->id]);

        $this->assertSame(2, $resultado->total());
    }

    public function test_detecta_email_duplicado(): void
    {
        $user = User::factory()->create();
        $this->repo->crear($this->dtoBase($user->id, 'dup@test.co'), 'PROS-00001');

        $this->assertTrue($this->repo->existeEmail('dup@test.co'));
        $this->assertFalse($this->repo->existeEmail('nuevo@test.co'));
    }

    public function test_detecta_email_duplicado_excluyendo_id_propio(): void
    {
        $user      = User::factory()->create();
        $prospecto = $this->repo->crear($this->dtoBase($user->id, 'mismo@test.co'), 'PROS-00001');

        $this->assertFalse($this->repo->existeEmail('mismo@test.co', $prospecto->id));
    }

    private function dtoBase(int $asesorId, ?string $email = null, string $empresa = 'Empresa Test'): CrearProspectoDTO
    {
        return CrearProspectoDTO::fromArray([
            'empresa'           => $empresa,
            'contacto'          => 'Contacto',
            'estado_pipeline_id' => $this->estado->id,
            'asesor_id'         => $asesorId,
            'email'             => $email,
        ]);
    }
}

