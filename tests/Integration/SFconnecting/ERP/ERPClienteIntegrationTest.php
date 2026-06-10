<?php

declare(strict_types=1);

namespace Tests\Integration\SFconnecting\ERP;

use App\Domain\Clientes\DTOs\CrearClienteDTO;
use App\Domain\Clientes\Repositories\ClienteRepository;
use App\Domain\Clientes\Services\ClienteService;
use App\Domain\ERP\Contracts\ERPRepositoryInterface;
use App\Domain\ERP\Fakes\FakeERPRepository;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Verifica la integraciÃ³n entre ClienteService y el FakeERPRepository.
 * Nunca usa ContiflexERPRepository real.
 */
class ERPClienteIntegrationTest extends TestCase
{
    use RefreshDatabase;

    private FakeERPRepository $erp;
    private ClienteService $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->erp = new FakeERPRepository();
        $this->app->instance(ERPRepositoryInterface::class, $this->erp);
        $this->service = new ClienteService(new ClienteRepository(), $this->erp);
    }

    public function test_cliente_creado_se_enriquece_con_datos_erp(): void
    {
        $user = User::factory()->create();
        $this->erp->agregarCliente('900100200', [
            'razon_social' => 'Empresa Contiflex SA',
            'ciudad'       => 'Barranquilla',
            'telefono'     => '6051234567',
        ]);

        $dto     = new CrearClienteDTO('Empresa Contiflex SA', '900100200', $user->id);
        $cliente = $this->service->crear($dto);

        $this->assertSame('Barranquilla', $cliente->ciudad);
        $this->assertSame('6051234567', $cliente->telefono);
    }

    public function test_creacion_no_falla_si_erp_no_tiene_el_nit(): void
    {
        $user    = User::factory()->create();
        $dto     = new CrearClienteDTO('Cliente Sin ERP', '111222333', $user->id);
        $cliente = $this->service->crear($dto);

        $this->assertNotNull($cliente->id);
        $this->assertNull($cliente->ciudad);
    }

    public function test_creacion_no_falla_si_erp_esta_caido(): void
    {
        $user = User::factory()->create();
        $this->erp->simularDesconexion();

        $dto     = new CrearClienteDTO('Cliente ERP CaÃ­do', '444555666', $user->id);
        $cliente = $this->service->crear($dto);

        $this->assertNotNull($cliente->id);
    }

    public function test_datos_usuario_prevalecen_sobre_erp(): void
    {
        $user = User::factory()->create();
        $this->erp->agregarCliente('777888999', [
            'ciudad'  => 'Ciudad ERP',
            'telefono' => '0001112222',
        ]);

        $dto     = new CrearClienteDTO(
            'Mi Empresa', '777888999', $user->id,
            ciudad: 'Cartagena',
            telefono: '6051111111',
        );
        $cliente = $this->service->crear($dto);

        $this->assertSame('Cartagena', $cliente->ciudad);
        $this->assertSame('6051111111', $cliente->telefono);
    }

    public function test_erp_endpoint_retorna_datos_del_cliente(): void
    {
        $this->crearRol('comercial');
        $user = User::factory()->create();
        $user->assignRole('comercial');

        $this->erp->agregarCliente('900123456', [
            'razon_social' => 'Empresa Fake SA',
            'ciudad'       => 'BogotÃ¡',
        ]);

        $this->actingAs($user)
            ->getJson('/api/erp/clientes/900123456')
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.nit', '900123456');
    }

    public function test_erp_endpoint_retorna_404_si_nit_no_existe(): void
    {
        $this->crearRol('comercial');
        $user = User::factory()->create();
        $user->assignRole('comercial');

        $this->actingAs($user)
            ->getJson('/api/erp/clientes/000000000')
            ->assertStatus(404);
    }

    public function test_erp_estado_retorna_disponibilidad(): void
    {
        $this->crearRol('comercial');
        $user = User::factory()->create();
        $user->assignRole('comercial');

        $this->actingAs($user)
            ->getJson('/api/erp/estado')
            ->assertOk()
            ->assertJsonPath('data.disponible', true);
    }
}

