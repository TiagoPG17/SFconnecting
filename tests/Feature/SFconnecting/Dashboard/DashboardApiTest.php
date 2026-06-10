<?php

declare(strict_types=1);

namespace Tests\Feature\SFconnecting\Dashboard;

use App\Domain\Clientes\Models\Cliente;
use App\Domain\Seguimientos\Models\Seguimiento;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardApiTest extends TestCase
{
    use RefreshDatabase;

    private function asesor(): User
    {
        $this->crearRol('comercial');
        $user = User::factory()->create();
        $user->assignRole('comercial');
        return $user;
    }

    private function gerente(): User
    {
        $this->crearRol('gerente');
        $user = User::factory()->create();
        $user->assignRole('gerente');
        return $user;
    }

    // â€” AUTH â€”

    public function test_no_autenticado_recibe_401(): void
    {
        $this->getJson('/api/dashboard/kpis')->assertStatus(401);
    }

    // â€” ESTRUCTURA â€”

    public function test_kpis_retornan_estructura_correcta(): void
    {
        $asesor = $this->asesor();

        $response = $this->actingAs($asesor)
            ->getJson('/api/dashboard/kpis')
            ->assertOk()
            ->assertJsonPath('success', true);

        $data = $response->json('data');

        $this->assertArrayHasKey('clientes', $data);
        $this->assertArrayHasKey('seguimientos', $data);
        $this->assertArrayHasKey('alertas', $data);
        $this->assertArrayHasKey('total', $data['clientes']);
        $this->assertArrayHasKey('activos', $data['clientes']);
        $this->assertArrayHasKey('inactivos', $data['clientes']);
        $this->assertArrayHasKey('prospectos', $data['clientes']);
        $this->assertArrayHasKey('ultimo_mes', $data['seguimientos']);
        $this->assertArrayHasKey('proximos_7_dias', $data['seguimientos']);
        $this->assertArrayHasKey('clientes_sin_seguimiento_reciente', $data['alertas']);
    }

    // â€” FILTRADO POR ROL â€”

    public function test_asesor_ve_solo_sus_clientes(): void
    {
        $asesor = $this->asesor();
        $otro   = User::factory()->create();

        Cliente::factory()->count(3)->create(['user_id' => $asesor->id, 'estado' => 'activo']);
        Cliente::factory()->count(5)->create(['user_id' => $otro->id, 'estado' => 'activo']);

        $response = $this->actingAs($asesor)
            ->getJson('/api/dashboard/kpis')
            ->assertOk();

        $this->assertSame(3, $response->json('data.clientes.total'));
    }

    public function test_gerente_ve_todos_los_clientes(): void
    {
        $gerente = $this->gerente();
        $otro    = User::factory()->create();

        Cliente::factory()->count(3)->create(['user_id' => $gerente->id, 'estado' => 'activo']);
        Cliente::factory()->count(5)->create(['user_id' => $otro->id, 'estado' => 'activo']);

        $response = $this->actingAs($gerente)
            ->getJson('/api/dashboard/kpis')
            ->assertOk();

        $this->assertSame(8, $response->json('data.clientes.total'));
    }

    // â€” CONTEOS CORRECTOS â€”

    public function test_kpis_cuentan_correctamente_por_estado(): void
    {
        $asesor = $this->asesor();

        Cliente::factory()->count(4)->create(['user_id' => $asesor->id, 'estado' => 'activo']);
        Cliente::factory()->count(2)->create(['user_id' => $asesor->id, 'estado' => 'inactivo']);
        Cliente::factory()->create(['user_id' => $asesor->id, 'estado' => 'prospecto']);

        $response = $this->actingAs($asesor)
            ->getJson('/api/dashboard/kpis')
            ->assertOk();

        $this->assertSame(7, $response->json('data.clientes.total'));
        $this->assertSame(4, $response->json('data.clientes.activos'));
        $this->assertSame(2, $response->json('data.clientes.inactivos'));
        $this->assertSame(1, $response->json('data.clientes.prospectos'));
    }

    public function test_kpis_cuentan_seguimientos_del_ultimo_mes(): void
    {
        $asesor  = $this->asesor();
        $cliente = Cliente::factory()->create(['user_id' => $asesor->id]);

        Seguimiento::factory()->count(3)->create([
            'user_id'           => $asesor->id,
            'cliente_id'        => $cliente->id,
            'fecha_seguimiento' => now()->subDays(10),
        ]);
        Seguimiento::factory()->create([
            'user_id'           => $asesor->id,
            'cliente_id'        => $cliente->id,
            'fecha_seguimiento' => now()->subDays(45),
        ]);

        $response = $this->actingAs($asesor)
            ->getJson('/api/dashboard/kpis')
            ->assertOk();

        $this->assertSame(3, $response->json('data.seguimientos.ultimo_mes'));
    }

    public function test_kpis_cuentan_proximos_seguimientos(): void
    {
        $asesor  = $this->asesor();
        $cliente = Cliente::factory()->create(['user_id' => $asesor->id]);

        Seguimiento::factory()->count(2)->create([
            'user_id'    => $asesor->id,
            'cliente_id' => $cliente->id,
            'proxima_fecha' => now()->addDays(3),
        ]);
        Seguimiento::factory()->create([
            'user_id'    => $asesor->id,
            'cliente_id' => $cliente->id,
            'proxima_fecha' => now()->addDays(15),
        ]);

        $response = $this->actingAs($asesor)
            ->getJson('/api/dashboard/kpis')
            ->assertOk();

        $this->assertSame(2, $response->json('data.seguimientos.proximos_7_dias'));
    }
}

