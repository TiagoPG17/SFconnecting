<?php

declare(strict_types=1);

namespace Tests\Feature\SFconnecting\Seguimientos;

use App\Domain\Clientes\Models\Cliente;
use App\Domain\Prospectos\Models\Prospecto;
use App\Domain\Seguimientos\Models\Seguimiento;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SeguimientoApiTest extends TestCase
{
    use RefreshDatabase;

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
        $this->postJson('/api/seguimientos', [])->assertStatus(401);
    }

    // â€” CREAR â€”

    public function test_asesor_crea_seguimiento_exitosamente(): void
    {
        $asesor  = $this->asesor();
        $cliente = Cliente::factory()->create(['user_id' => $asesor->id]);

        $this->actingAs($asesor)
            ->postJson('/api/seguimientos', [
                'cliente_id'        => $cliente->id,
                'tipo'              => 'llamada',
                'resultado'         => 'exitoso',
                'descripcion'       => 'Llamada de seguimiento comercial con el cliente.',
                'fecha_seguimiento' => now()->subHour()->toDateTimeString(),
            ])
            ->assertStatus(201)
            ->assertJsonPath('success', true);

        $this->assertDatabaseHas('seguimientos', ['cliente_id' => $cliente->id, 'tipo' => 'llamada']);
    }

    public function test_crear_falla_sin_cliente_id_ni_prospecto_id(): void
    {
        $asesor = $this->asesor();

        $this->actingAs($asesor)
            ->postJson('/api/seguimientos', [
                'tipo'              => 'llamada',
                'resultado'         => 'exitoso',
                'descripcion'       => 'DescripciÃ³n de prueba.',
                'fecha_seguimiento' => now()->toDateTimeString(),
            ])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['cliente_id']);
    }

    public function test_puede_crear_seguimiento_para_prospecto(): void
    {
        $asesor    = $this->asesor();
        $prospecto = Prospecto::factory()->create(['asesor_id' => $asesor->id]);

        $this->actingAs($asesor)
            ->postJson('/api/seguimientos', [
                'prospecto_id'      => $prospecto->id,
                'tipo'              => 'llamada',
                'resultado'         => 'exitoso',
                'descripcion'       => 'Llamada de calificaciÃ³n del prospecto.',
                'fecha_seguimiento' => now()->subHour()->toDateTimeString(),
            ])
            ->assertStatus(201)
            ->assertJsonPath('success', true);

        $this->assertDatabaseHas('seguimientos', [
            'prospecto_id' => $prospecto->id,
            'cliente_id'   => null,
        ]);
    }

    public function test_puede_listar_seguimientos_por_prospecto(): void
    {
        $asesor    = $this->asesor();
        $prospecto = Prospecto::factory()->create(['asesor_id' => $asesor->id]);

        Seguimiento::factory()->count(3)->create(['prospecto_id' => $prospecto->id, 'cliente_id' => null]);

        $response = $this->actingAs($asesor)
            ->getJson("/api/seguimientos?prospecto_id={$prospecto->id}")
            ->assertOk()
            ->assertJsonPath('success', true);

        $data = $response->json('data.data');
        $this->assertCount(3, $data);
    }

    public function test_crear_falla_con_cliente_inexistente(): void
    {
        $asesor = $this->asesor();

        $this->actingAs($asesor)
            ->postJson('/api/seguimientos', [
                'cliente_id'        => 99999,
                'tipo'              => 'llamada',
                'resultado'         => 'exitoso',
                'descripcion'       => 'DescripciÃ³n de prueba con longitud suficiente.',
                'fecha_seguimiento' => now()->toDateTimeString(),
            ])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['cliente_id']);
    }

    public function test_crear_falla_con_tipo_invalido(): void
    {
        $asesor  = $this->asesor();
        $cliente = Cliente::factory()->create();

        $this->actingAs($asesor)
            ->postJson('/api/seguimientos', [
                'cliente_id'        => $cliente->id,
                'tipo'              => 'fax',
                'resultado'         => 'exitoso',
                'descripcion'       => 'DescripciÃ³n de prueba.',
                'fecha_seguimiento' => now()->toDateTimeString(),
            ])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['tipo']);
    }

    public function test_crear_falla_con_proxima_fecha_pasada(): void
    {
        $asesor  = $this->asesor();
        $cliente = Cliente::factory()->create();

        $this->actingAs($asesor)
            ->postJson('/api/seguimientos', [
                'cliente_id'        => $cliente->id,
                'tipo'              => 'llamada',
                'resultado'         => 'exitoso',
                'descripcion'       => 'DescripciÃ³n de prueba con longitud suficiente.',
                'fecha_seguimiento' => now()->subHour()->toDateTimeString(),
                'proxima_fecha'     => now()->subDay()->toDateTimeString(),
            ])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['proxima_fecha']);
    }

    public function test_crear_falla_con_descripcion_muy_corta(): void
    {
        $asesor  = $this->asesor();
        $cliente = Cliente::factory()->create();

        $this->actingAs($asesor)
            ->postJson('/api/seguimientos', [
                'cliente_id'        => $cliente->id,
                'tipo'              => 'llamada',
                'resultado'         => 'exitoso',
                'descripcion'       => 'Corto',
                'fecha_seguimiento' => now()->toDateTimeString(),
            ])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['descripcion']);
    }

    // â€” VER â€”

    public function test_puede_ver_seguimiento(): void
    {
        $asesor      = $this->asesor();
        $seguimiento = Seguimiento::factory()->create();

        $this->actingAs($asesor)
            ->getJson("/api/seguimientos/{$seguimiento->id}")
            ->assertOk()
            ->assertJsonPath('data.id', $seguimiento->id);
    }

    public function test_ver_seguimiento_inexistente_retorna_404(): void
    {
        $asesor = $this->asesor();

        $this->actingAs($asesor)
            ->getJson('/api/seguimientos/99999')
            ->assertStatus(404);
    }

    // â€” ACTUALIZAR â€”

    public function test_asesor_puede_actualizar_su_seguimiento(): void
    {
        $asesor      = $this->asesor();
        $seguimiento = Seguimiento::factory()->create(['user_id' => $asesor->id]);

        $this->actingAs($asesor)
            ->putJson("/api/seguimientos/{$seguimiento->id}", [
                'resultado'   => 'exitoso',
                'descripcion' => 'DescripciÃ³n actualizada con mÃ¡s de diez caracteres.',
            ])
            ->assertOk()
            ->assertJsonPath('success', true);

        $this->assertDatabaseHas('seguimientos', ['id' => $seguimiento->id, 'resultado' => 'exitoso']);
    }

    public function test_asesor_no_puede_actualizar_seguimiento_ajeno(): void
    {
        $asesor      = $this->asesor();
        $seguimiento = Seguimiento::factory()->create();

        $this->actingAs($asesor)
            ->putJson("/api/seguimientos/{$seguimiento->id}", ['resultado' => 'exitoso'])
            ->assertStatus(403);
    }

    public function test_actualizar_falla_con_proxima_fecha_pasada(): void
    {
        $asesor      = $this->asesor();
        $seguimiento = Seguimiento::factory()->create(['user_id' => $asesor->id]);

        $this->actingAs($asesor)
            ->putJson("/api/seguimientos/{$seguimiento->id}", [
                'proxima_fecha' => now()->subDay()->toDateTimeString(),
            ])
            ->assertStatus(422);
    }

    // â€” ELIMINAR â€”

    public function test_puede_eliminar_seguimiento_propio(): void
    {
        $asesor      = $this->asesor();
        $seguimiento = Seguimiento::factory()->create(['user_id' => $asesor->id]);

        $this->actingAs($asesor)
            ->deleteJson("/api/seguimientos/{$seguimiento->id}")
            ->assertOk()
            ->assertJsonPath('success', true);

        $this->assertDatabaseMissing('seguimientos', ['id' => $seguimiento->id]);
    }

    public function test_asesor_no_puede_eliminar_seguimiento_ajeno(): void
    {
        $asesor      = $this->asesor();
        $seguimiento = Seguimiento::factory()->create();

        $this->actingAs($asesor)
            ->deleteJson("/api/seguimientos/{$seguimiento->id}")
            ->assertStatus(403);
    }

    // â€” PRÃ“XIMOS â€”

    public function test_retorna_proximos_seguimientos_del_asesor(): void
    {
        $asesor = $this->asesor();

        Seguimiento::factory()->create(['user_id' => $asesor->id, 'proxima_fecha' => Carbon::now()->addDays(2)]);
        Seguimiento::factory()->create(['user_id' => $asesor->id, 'proxima_fecha' => null]);

        $this->actingAs($asesor)
            ->getJson('/api/seguimientos/proximos')
            ->assertOk()
            ->assertJsonPath('success', true);
    }
}

