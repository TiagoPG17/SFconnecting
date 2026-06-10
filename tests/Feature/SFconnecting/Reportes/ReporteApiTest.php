<?php

declare(strict_types=1);

namespace Tests\Feature\SFconnecting\Reportes;

use App\Domain\Clientes\Models\Cliente;
use App\Domain\Seguimientos\Models\Seguimiento;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReporteApiTest extends TestCase
{
    use RefreshDatabase;

    private function gerente(): User
    {
        $this->crearRol('gerente');
        $user = User::factory()->create();
        $user->assignRole('gerente');
        return $user;
    }

    private function asesor(): User
    {
        $this->crearRol('comercial');
        $user = User::factory()->create();
        $user->assignRole('comercial');
        return $user;
    }

    // â€” AUTH â€”

    public function test_no_autenticado_recibe_401(): void
    {
        $this->getJson('/api/reportes/clientes')->assertStatus(401);
        $this->getJson('/api/reportes/seguimientos')->assertStatus(401);
    }

    // â€” ACCESO POR ROL â€”

    public function test_asesor_no_puede_acceder_a_reportes(): void
    {
        $this->actingAs($this->asesor())
            ->getJson('/api/reportes/clientes')
            ->assertStatus(403);
    }

    public function test_gerente_puede_acceder_a_reporte_clientes(): void
    {
        $this->actingAs($this->gerente())
            ->getJson('/api/reportes/clientes')
            ->assertOk()
            ->assertJsonPath('success', true);
    }

    public function test_gerente_puede_acceder_a_reporte_seguimientos(): void
    {
        $this->actingAs($this->gerente())
            ->getJson('/api/reportes/seguimientos')
            ->assertOk()
            ->assertJsonPath('success', true);
    }

    // â€” CONTENIDO REPORTE CLIENTES â€”

    public function test_reporte_clientes_retorna_estructura_correcta(): void
    {
        $gerente = $this->gerente();
        Cliente::factory()->count(3)->create(['estado' => 'activo']);
        Cliente::factory()->count(2)->create(['estado' => 'inactivo']);

        $response = $this->actingAs($gerente)
            ->getJson('/api/reportes/clientes')
            ->assertOk();

        $data = $response->json('data');
        $this->assertArrayHasKey('total', $data);
        $this->assertArrayHasKey('por_estado', $data);
        $this->assertArrayHasKey('por_ciudad', $data);
        $this->assertSame(5, $data['total']);
    }

    // â€” CONTENIDO REPORTE SEGUIMIENTOS â€”

    public function test_reporte_seguimientos_retorna_estructura_correcta(): void
    {
        $gerente = $this->gerente();
        Seguimiento::factory()->count(4)->create(['tipo' => 'llamada']);
        Seguimiento::factory()->count(2)->create(['tipo' => 'reunion']);

        $response = $this->actingAs($gerente)
            ->getJson('/api/reportes/seguimientos')
            ->assertOk();

        $data = $response->json('data');
        $this->assertArrayHasKey('total', $data);
        $this->assertArrayHasKey('por_tipo', $data);
        $this->assertArrayHasKey('por_resultado', $data);
        $this->assertSame(6, $data['total']);
    }

    // â€” FILTRO POR FECHA â€”

    public function test_reporte_clientes_acepta_filtro_de_fecha(): void
    {
        $gerente = $this->gerente();

        $this->actingAs($gerente)
            ->getJson('/api/reportes/clientes?desde=2026-01-01&hasta=2026-12-31')
            ->assertOk();
    }
}

