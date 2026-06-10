<?php

declare(strict_types=1);

namespace Tests\Unit\SFconnecting\Repositories;

use App\Domain\Dashboard\Models\Presupuesto;
use App\Domain\Dashboard\Repositories\PresupuestoRepository;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PresupuestoRepositoryTest extends TestCase
{
    use RefreshDatabase;

    private PresupuestoRepository $repo;

    protected function setUp(): void
    {
        parent::setUp();
        $this->crearRol('comercial');
        $this->repo = new PresupuestoRepository();
    }

    private function comercial(): User
    {
        $user = User::factory()->create();
        $user->assignRole('comercial');
        return $user;
    }

    private function presupuesto(User $asesor, int $anio = 2026, float $valor = 1_000_000): Presupuesto
    {
        return Presupuesto::create([
            'asesor_id'   => $asesor->id,
            'compania'    => 2,
            'anio'        => $anio,
            'presupuesto' => $valor,
        ]);
    }

    // ─── todos ───────────────────────────────────────────────────────────────

    public function test_todos_retorna_presupuestos_del_anio_indicado(): void
    {
        $asesor = $this->comercial();
        $this->presupuesto($asesor, 2026);
        $this->presupuesto($asesor, 2025);

        $resultado = $this->repo->todos(2026);

        $this->assertCount(1, $resultado);
    }

    public function test_todos_excluye_usuarios_sin_rol_comercial(): void
    {
        $sinRol = User::factory()->create();
        Presupuesto::create(['asesor_id' => $sinRol->id, 'compania' => 2, 'anio' => 2026, 'presupuesto' => 500_000]);

        $resultado = $this->repo->todos(2026);

        $this->assertCount(0, $resultado);
    }

    public function test_todos_ordena_por_presupuesto_descendente(): void
    {
        $a = $this->comercial();
        $b = $this->comercial();
        $this->presupuesto($a, 2026, 500_000);
        $this->presupuesto($b, 2026, 2_000_000);

        $resultado = $this->repo->todos(2026);

        $this->assertSame(2_000_000.0, (float) $resultado->first()->presupuesto);
    }

    // ─── crear ───────────────────────────────────────────────────────────────

    public function test_crear_persiste_el_presupuesto_en_base_de_datos(): void
    {
        $asesor = $this->comercial();

        $this->repo->crear([
            'asesor_id'   => $asesor->id,
            'compania'    => 2,
            'anio'        => 2026,
            'presupuesto' => 800_000,
        ]);

        $this->assertDatabaseHas('sf_presupuesto', ['asesor_id' => $asesor->id, 'anio' => 2026]);
    }

    public function test_crear_retorna_instancia_de_presupuesto(): void
    {
        $asesor = $this->comercial();

        $presupuesto = $this->repo->crear([
            'asesor_id'   => $asesor->id,
            'compania'    => 2,
            'anio'        => 2026,
            'presupuesto' => 600_000,
        ]);

        $this->assertInstanceOf(Presupuesto::class, $presupuesto);
    }

    // ─── actualizar ──────────────────────────────────────────────────────────

    public function test_actualizar_modifica_el_valor_del_presupuesto(): void
    {
        $asesor      = $this->comercial();
        $presupuesto = $this->presupuesto($asesor, 2026, 500_000);

        $actualizado = $this->repo->actualizar($presupuesto, ['presupuesto' => 900_000]);

        $this->assertSame(900_000.0, (float) $actualizado->presupuesto);
    }

    public function test_actualizar_persiste_el_cambio_en_base_de_datos(): void
    {
        $asesor      = $this->comercial();
        $presupuesto = $this->presupuesto($asesor, 2026, 500_000);

        $this->repo->actualizar($presupuesto, ['presupuesto' => 1_200_000]);

        $this->assertDatabaseHas('sf_presupuesto', ['id' => $presupuesto->id, 'presupuesto' => 1_200_000]);
    }

    // ─── eliminar ────────────────────────────────────────────────────────────

    public function test_eliminar_borra_el_registro_de_base_de_datos(): void
    {
        $asesor      = $this->comercial();
        $presupuesto = $this->presupuesto($asesor);

        $this->repo->eliminar($presupuesto);

        $this->assertDatabaseMissing('sf_presupuesto', ['id' => $presupuesto->id]);
    }

    // ─── buscarPorId ─────────────────────────────────────────────────────────

    public function test_buscar_por_id_retorna_el_presupuesto_correcto(): void
    {
        $asesor      = $this->comercial();
        $presupuesto = $this->presupuesto($asesor);

        $encontrado = $this->repo->buscarPorId($presupuesto->id);

        $this->assertSame($presupuesto->id, $encontrado->id);
    }

    public function test_buscar_por_id_retorna_null_si_no_existe(): void
    {
        $resultado = $this->repo->buscarPorId(99999);

        $this->assertNull($resultado);
    }

    // ─── existe ──────────────────────────────────────────────────────────────

    public function test_existe_retorna_true_cuando_hay_duplicado(): void
    {
        $asesor = $this->comercial();
        $this->presupuesto($asesor, 2026);

        $resultado = $this->repo->existe($asesor->id, 2, 2026);

        $this->assertTrue($resultado);
    }

    public function test_existe_retorna_false_para_combinacion_diferente(): void
    {
        $asesor = $this->comercial();
        $this->presupuesto($asesor, 2026);

        $resultado = $this->repo->existe($asesor->id, 2, 2025);

        $this->assertFalse($resultado);
    }

    public function test_existe_retorna_false_cuando_excepto_id_apunta_al_mismo_registro(): void
    {
        $asesor      = $this->comercial();
        $presupuesto = $this->presupuesto($asesor, 2026);

        $resultado = $this->repo->existe($asesor->id, 2, 2026, exceptoId: $presupuesto->id);

        $this->assertFalse($resultado);
    }
}
