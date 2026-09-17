<?php

declare(strict_types=1);

namespace Tests\Feature\SFconnecting\Cartera;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CarteraNotificacionApiTest extends TestCase
{
    use RefreshDatabase;

    private const TOKEN = 'token-de-prueba-n8n';

    protected function setUp(): void
    {
        parent::setUp();

        config(['services.n8n.token' => self::TOKEN]);

        $this->fakeErp->agregarGestionCartera([
            [
                'Compania'          => 1,
                'NroDocumento'      => 'FV-200',
                'Cliente'           => 'Cliente Dos',
                'Vendedor'          => 'Vendedor Dos',
                'FechaPedido'       => '2026-09-01',
                'FechaCumplimiento' => '2026-09-20',
                'SubtotalPendiente' => 750000,
            ],
        ]);

        $this->fakeErp->notificarCartera([
            ['compania' => 1, 'nro_documento' => 'FV-200', 'fecha_inicio_cobro' => '2026-09-17'],
        ]);
    }

    private function conToken(): static
    {
        return $this->withHeader('Authorization', 'Bearer '.self::TOKEN);
    }

    public function test_sin_token_retorna_401(): void
    {
        $this->getJson('/api/cartera/pendientes')->assertUnauthorized();
    }

    public function test_token_invalido_retorna_401(): void
    {
        $this->withHeader('Authorization', 'Bearer token-incorrecto')
            ->getJson('/api/cartera/pendientes')
            ->assertUnauthorized();
    }

    public function test_token_valido_lista_pendientes(): void
    {
        $this->conToken()
            ->getJson('/api/cartera/pendientes?compania=1')
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.0.NroDocumento', 'FV-200');
    }

    public function test_pendientes_filtra_por_fecha_cumplimiento(): void
    {
        $this->conToken()
            ->getJson('/api/cartera/pendientes?fecha_cumplimiento=2026-09-20')
            ->assertOk()
            ->assertJsonCount(1, 'data');

        $this->conToken()
            ->getJson('/api/cartera/pendientes?fecha_cumplimiento=2026-01-01')
            ->assertOk()
            ->assertJsonCount(0, 'data');
    }

    public function test_notificar_marca_resuelto(): void
    {
        $this->conToken()
            ->patchJson('/api/cartera/1/FV-200/notificar')
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.resuelto', true);

        $this->assertCount(0, $this->fakeErp->notificacionesCarteraPendientes(1));
    }

    public function test_notificar_pedido_ya_resuelto_retorna_resuelto_false(): void
    {
        $this->fakeErp->marcarNotificacionCarteraResuelta(1, 'FV-200');

        $this->conToken()
            ->patchJson('/api/cartera/1/FV-200/notificar')
            ->assertOk()
            ->assertJsonPath('data.resuelto', false);
    }

    public function test_notificar_sin_token_retorna_401(): void
    {
        $this->patchJson('/api/cartera/1/FV-200/notificar')->assertUnauthorized();
    }
}
