<?php

declare(strict_types=1);

namespace Tests\Feature\SFconnecting\Clientes;

use App\Domain\Clientes\Models\Cliente;
use App\Domain\Dashboard\Models\VendedorEquivalencia;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ClienteFacturasFiltroCompaniaTest extends TestCase
{
    use RefreshDatabase;

    private function factura(int $compania, string $concepto): array
    {
        return [
            'ROWID_FACTURA' => random_int(1, 999999),
            'COMPANIA'      => $compania,
            'CONCEPTO'      => $concepto,
            'TIPO'          => 'FV',
            'FECHA'         => '2026-06-30',
            'COD_VENDEDOR'  => 'V1',
            'NUM_ITEMS'     => 1,
            'VLR_NETO'      => 100000,
        ];
    }

    public function test_comercial_solo_ve_facturas_de_su_propia_compania(): void
    {
        $this->crearRol('comercial');
        $comercial = User::factory()->create();
        $comercial->assignRole('comercial');

        VendedorEquivalencia::create([
            'asesor_id'          => $comercial->id,
            'compania'           => 2,
            'cod_vendedor_siesa' => 'V1',
            'nombre_vendedor'    => 'Vendedor Contiflex',
            'activo'             => true,
        ]);

        $cliente = Cliente::factory()->create(['user_id' => $comercial->id, 'nit' => '900111222']);

        $this->fakeErp->agregarFacturas('900111222', [
            $this->factura(1, '501'), // Formacol - no debe verla
            $this->factura(2, '502'), // Contiflex - sí debe verla
        ]);

        $response = $this->actingAs($comercial)->get(route('clientes.show', $cliente));

        $response->assertOk();
        $facturas = $response->viewData('facturas');

        $this->assertCount(1, $facturas);
        $this->assertSame(2, $facturas[array_key_first($facturas)]['COMPANIA']);
    }

    public function test_comercial_con_mapeo_en_ambas_companias_ve_las_dos(): void
    {
        $this->crearRol('comercial');
        $comercial = User::factory()->create();
        $comercial->assignRole('comercial');

        VendedorEquivalencia::create([
            'asesor_id'          => $comercial->id,
            'compania'           => 1,
            'cod_vendedor_siesa' => 'V1',
            'nombre_vendedor'    => 'Vendedor Formacol',
            'activo'             => true,
        ]);
        VendedorEquivalencia::create([
            'asesor_id'          => $comercial->id,
            'compania'           => 2,
            'cod_vendedor_siesa' => 'V1',
            'nombre_vendedor'    => 'Vendedor Contiflex',
            'activo'             => true,
        ]);

        $cliente = Cliente::factory()->create(['user_id' => $comercial->id, 'nit' => '900333444']);

        $this->fakeErp->agregarFacturas('900333444', [
            $this->factura(1, '501'),
            $this->factura(2, '502'),
        ]);

        $response = $this->actingAs($comercial)->get(route('clientes.show', $cliente));

        $response->assertOk();
        $this->assertCount(2, $response->viewData('facturas'));
    }

    public function test_admin_ve_todas_las_facturas_sin_filtrar(): void
    {
        $this->crearRol('admin');
        $this->crearRol('comercial');
        $admin = User::factory()->create();
        $admin->assignRole('admin');

        $cliente = Cliente::factory()->create(['nit' => '900555666']);

        $this->fakeErp->agregarFacturas('900555666', [
            $this->factura(1, '501'),
            $this->factura(2, '502'),
        ]);

        $response = $this->actingAs($admin)->get(route('clientes.show', $cliente));

        $response->assertOk();
        $this->assertCount(2, $response->viewData('facturas'));
    }
}
