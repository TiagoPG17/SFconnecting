<?php

declare(strict_types=1);

namespace Tests\Unit\SFconnecting\Repositories;

use App\Domain\Dashboard\Models\VendedorEquivalencia;
use App\Domain\Dashboard\Repositories\VendedorEquivalenciaRepository;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class VendedorEquivalenciaRepositoryTest extends TestCase
{
    use RefreshDatabase;

    private VendedorEquivalenciaRepository $repo;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repo = new VendedorEquivalenciaRepository();
    }

    private function mapeo(User $asesor, int $compania = 2, bool $activo = true, string $cod = 'V001'): VendedorEquivalencia
    {
        return VendedorEquivalencia::create([
            'asesor_id'           => $asesor->id,
            'compania'            => $compania,
            'cod_vendedor_siesa'  => $cod,
            'rowid_vendedor_siesa' => 1,
            'nombre_vendedor'     => 'Vendedor Test',
            'activo'              => $activo,
        ]);
    }

    // ─── todos ───────────────────────────────────────────────────────────────

    public function test_todos_retorna_solo_registros_de_la_compania_indicada(): void
    {
        $asesor = User::factory()->create();
        $this->mapeo($asesor, 2);
        $this->mapeo($asesor, 3, cod: 'V002');

        $resultado = $this->repo->todos(2);

        $this->assertCount(1, $resultado);
    }

    public function test_todos_ordena_activos_antes_que_inactivos(): void
    {
        $a = User::factory()->create();
        $b = User::factory()->create();
        $this->mapeo($a, 2, activo: false, cod: 'V001');
        $this->mapeo($b, 2, activo: true, cod: 'V002');

        $resultado = $this->repo->todos(2);

        $this->assertTrue((bool) $resultado->first()->activo);
    }

    // ─── crear ───────────────────────────────────────────────────────────────

    public function test_crear_persiste_el_mapeo_en_base_de_datos(): void
    {
        $asesor = User::factory()->create();

        $this->repo->crear([
            'asesor_id'           => $asesor->id,
            'compania'            => 2,
            'cod_vendedor_siesa'  => 'V099',
            'rowid_vendedor_siesa' => 5,
            'nombre_vendedor'     => 'Nuevo Vendedor',
            'activo'              => true,
        ]);

        $this->assertDatabaseHas('sf_vendedor_equivalencia', ['cod_vendedor_siesa' => 'V099']);
    }

    public function test_crear_retorna_instancia_de_vendedor_equivalencia(): void
    {
        $asesor = User::factory()->create();

        $resultado = $this->repo->crear([
            'asesor_id'           => $asesor->id,
            'compania'            => 2,
            'cod_vendedor_siesa'  => 'V088',
            'rowid_vendedor_siesa' => 3,
            'nombre_vendedor'     => 'Otro Vendedor',
            'activo'              => true,
        ]);

        $this->assertInstanceOf(VendedorEquivalencia::class, $resultado);
    }

    // ─── actualizar ──────────────────────────────────────────────────────────

    public function test_actualizar_modifica_el_cod_vendedor(): void
    {
        $asesor = User::factory()->create();
        $mapeo  = $this->mapeo($asesor);

        $actualizado = $this->repo->actualizar($mapeo, ['cod_vendedor_siesa' => 'V999']);

        $this->assertSame('V999', $actualizado->cod_vendedor_siesa);
    }

    public function test_actualizar_persiste_el_cambio_en_base_de_datos(): void
    {
        $asesor = User::factory()->create();
        $mapeo  = $this->mapeo($asesor);

        $this->repo->actualizar($mapeo, ['activo' => false]);

        $this->assertDatabaseHas('sf_vendedor_equivalencia', ['id' => $mapeo->id, 'activo' => false]);
    }

    // ─── eliminar ────────────────────────────────────────────────────────────

    public function test_eliminar_borra_el_registro_de_base_de_datos(): void
    {
        $asesor = User::factory()->create();
        $mapeo  = $this->mapeo($asesor);

        $this->repo->eliminar($mapeo);

        $this->assertDatabaseMissing('sf_vendedor_equivalencia', ['id' => $mapeo->id]);
    }

    // ─── buscarPorId ─────────────────────────────────────────────────────────

    public function test_buscar_por_id_retorna_el_mapeo_correcto(): void
    {
        $asesor = User::factory()->create();
        $mapeo  = $this->mapeo($asesor);

        $encontrado = $this->repo->buscarPorId($mapeo->id);

        $this->assertSame($mapeo->id, $encontrado->id);
    }

    public function test_buscar_por_id_retorna_null_si_no_existe(): void
    {
        $resultado = $this->repo->buscarPorId(99999);

        $this->assertNull($resultado);
    }

    // ─── existe ──────────────────────────────────────────────────────────────

    public function test_existe_retorna_true_cuando_asesor_ya_tiene_mapeo_en_la_compania(): void
    {
        $asesor = User::factory()->create();
        $this->mapeo($asesor, 2);

        $resultado = $this->repo->existe($asesor->id, 2);

        $this->assertTrue($resultado);
    }

    public function test_existe_retorna_false_para_compania_diferente(): void
    {
        $asesor = User::factory()->create();
        $this->mapeo($asesor, 2);

        $resultado = $this->repo->existe($asesor->id, 3);

        $this->assertFalse($resultado);
    }

    public function test_existe_retorna_false_cuando_excepto_id_apunta_al_mismo_registro(): void
    {
        $asesor = User::factory()->create();
        $mapeo  = $this->mapeo($asesor, 2);

        $resultado = $this->repo->existe($asesor->id, 2, exceptoId: $mapeo->id);

        $this->assertFalse($resultado);
    }

    // ─── vendedoresSiesa ─────────────────────────────────────────────────────

    public function test_vendedores_siesa_retorna_array_aunque_erp_no_este_disponible(): void
    {
        $resultado = $this->repo->vendedoresSiesa(2);

        $this->assertIsArray($resultado);
    }
}
