<?php

declare(strict_types=1);

namespace Tests\Unit\SFconnecting\Repositories;

use App\Domain\Dashboard\Models\VendedorEquivalencia;
use App\Domain\Dashboard\Repositories\DashboardVendedorRepository;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardVendedorRepositoryTest extends TestCase
{
    use RefreshDatabase;

    private DashboardVendedorRepository $repo;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repo = new DashboardVendedorRepository();
    }

    private function mapeo(User $asesor, string $cod, bool $esReemplazo = false, int $compania = 2): VendedorEquivalencia
    {
        return VendedorEquivalencia::create([
            'asesor_id'          => $asesor->id,
            'compania'           => $compania,
            'cod_vendedor_siesa' => $cod,
            'nombre_vendedor'    => 'Vendedor Test',
            'activo'             => true,
            'es_reemplazo'       => $esReemplazo,
        ]);
    }

    public function test_codigos_vendedor_siesa_incluye_el_codigo_propio(): void
    {
        $asesor = User::factory()->create();
        $this->mapeo($asesor, 'V001');

        $resultado = $this->repo->codigosVendedorSiesa($asesor->id, 2);

        $this->assertSame(['V001'], $resultado);
    }

    public function test_codigos_vendedor_siesa_excluye_codigos_marcados_como_reemplazo(): void
    {
        $asesor = User::factory()->create();
        $this->mapeo($asesor, 'V001');
        $this->mapeo($asesor, 'V002', esReemplazo: true);

        $resultado = $this->repo->codigosVendedorSiesa($asesor->id, 2);

        $this->assertSame(['V001'], $resultado);
    }

    public function test_codigos_vendedor_siesa_retorna_vacio_si_solo_tiene_codigo_de_reemplazo(): void
    {
        $asesor = User::factory()->create();
        $this->mapeo($asesor, 'V002', esReemplazo: true);

        $resultado = $this->repo->codigosVendedorSiesa($asesor->id, 2);

        $this->assertSame([], $resultado);
    }
}
