<?php

declare(strict_types=1);

namespace Tests\Unit\SFconnecting\Repositories;

use App\Domain\Dashboard\Models\VendedorEquivalencia;
use App\Domain\Dashboard\Repositories\DashboardGerencialRepository;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardGerencialRepositoryTest extends TestCase
{
    use RefreshDatabase;

    private DashboardGerencialRepository $repo;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repo = new DashboardGerencialRepository();
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

    public function test_cods_por_asesor_excluye_codigos_marcados_como_reemplazo(): void
    {
        $titular      = User::factory()->create();
        $reemplazante = User::factory()->create();
        $this->mapeo($titular, 'V001');
        $this->mapeo($reemplazante, 'V001', esReemplazo: true);
        $this->mapeo($reemplazante, 'V009');

        $resultado = $this->repo->codsPorAsesor(2)->pluck('cod_vendedor_siesa', 'asesor_id');

        $this->assertSame('V001', $resultado[$titular->id]);
        $this->assertSame('V009', $resultado[$reemplazante->id]);
    }
}
