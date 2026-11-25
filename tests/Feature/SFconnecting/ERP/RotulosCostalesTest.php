<?php

declare(strict_types=1);

namespace Tests\Feature\SFconnecting\ERP;

use App\Domain\RotulosMP\Contracts\ImpresoraZplInterface;
use App\Domain\RotulosMP\Fakes\FakeImpresoraZpl;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RotulosCostalesTest extends TestCase
{
    use RefreshDatabase;

    private FakeImpresoraZpl $impresora;

    protected function setUp(): void
    {
        parent::setUp();

        $this->impresora = new FakeImpresoraZpl();
        $this->app->instance(ImpresoraZplInterface::class, $this->impresora);

        $this->fakeErp->agregarImpresoraSgp('ZTC-230-ZPL-INV', '172.20.3.248');
        $this->fakeErp->agregarSalidaMP('SLI299428', [
            $this->renglon(833803, 'MP-011008', 'POLIPROPILENO NO.RT100N C/R BRASKEM', '103000.00000'),
            $this->renglon(833804, 'MP-020000', 'PIGMENTO BLANCO', '1500.00000', 'INTERNA'),
        ]);
    }

    private function renglon(int $id, string $ref, string $nombre, string $cantidad, string $capa = 'GENERAL'): array
    {
        return [
            'id_row' => $id, 'pedido' => 'PV37697-139852-0014111-2', 'documento' => 'SLI299428', 'formula' => '27661',
            'cliente' => 'CLICK COSMETICS S.A.S.', 'color' => 'BLANCO', 'material' => 'PP', 'proceso' => 'TRANSFORMACION',
            'capa' => $capa, 'ref' => $ref, 'nombre_material' => $nombre, 'cantidad' => $cantidad, 'unidad' => 'GRS',
            'lote' => 'RSGHPG038E',
        ];
    }

    private function usuario(string $rol = 'produccion'): User
    {
        $this->crearRol($rol);
        $user = User::factory()->create();
        $user->assignRole($rol);

        return $user;
    }

    public function test_consultar_devuelve_renglones_e_impresora_y_acepta_solo_el_numero(): void
    {
        $this->actingAs($this->usuario())
            ->getJson('/consultas-lotes/rotulos/consultar?documento=299428')
            ->assertOk()
            ->assertJsonPath('documento', 'SLI299428')
            ->assertJsonPath('impresora.nombre', 'ZTC-230-ZPL-INV')
            ->assertJsonPath('impresora.ip', '172.20.3.248')
            ->assertJsonCount(2, 'renglones');
    }

    public function test_documento_inexistente_da_mensaje_claro(): void
    {
        $this->actingAs($this->usuario())
            ->getJson('/consultas-lotes/rotulos/consultar?documento=SLI000001')
            ->assertStatus(422)
            ->assertJsonPath('success', false)
            ->assertJsonPath('message', 'No se encontró el documento SLI000001 o no tiene materiales.');
    }

    public function test_previsualizar_no_imprime_y_resume_costales(): void
    {
        $this->actingAs($this->usuario())
            ->postJson('/consultas-lotes/rotulos/previsualizar', [
                'documento' => 'SLI299428',
                'pesos'     => ['833803' => '25000', '833804' => null],
            ])
            ->assertOk()
            ->assertJsonPath('total_rotulos', 5)
            ->assertJsonPath('materiales.0.resumen', '4 de 25000 GRS y 1 de 3000 GRS');

        $this->assertSame([], $this->impresora->envios);
    }

    public function test_imprimir_envia_un_solo_zpl_con_un_rotulo_por_costal_y_registra(): void
    {
        $user = $this->usuario();

        $this->actingAs($user)
            ->postJson('/consultas-lotes/rotulos/imprimir', [
                'documento' => 'SLI299428',
                'pesos'     => ['833804' => '1000', '833803' => '25000'],
            ])
            ->assertOk()
            ->assertJsonPath('total_rotulos', 7);

        $this->assertCount(1, $this->impresora->envios);
        $envio = $this->impresora->envios[0];
        $this->assertSame('172.20.3.248', $envio['ip']);
        $this->assertSame(9100, $envio['puerto']);

        $zpl = $envio['zpl'];
        $this->assertSame(1, substr_count($zpl, '~TA0'));
        $this->assertSame(7, substr_count($zpl, '^PQ1,0,1,Y^XZ'));
        $this->assertSame(4, substr_count($zpl, 'CANTIDAD: 25000 GRS'));
        $this->assertStringContainsString('CANTIDAD: 3000 GRS', $zpl);
        $this->assertStringContainsString('ROT: 5/5', $zpl);
        $this->assertStringContainsString('CANTIDAD: 500 GRS', $zpl);
        $this->assertStringContainsString('ROT: 2/2', $zpl);
        // Orden de SGP: el renglón GENERAL antes que INTERNA, aunque se enviaran al revés.
        $this->assertLessThan(strpos($zpl, 'MP-020000'), strpos($zpl, 'MP-011008'));

        $this->assertDatabaseHas('sf_rotulos_mp_impresiones', [
            'documento' => 'SLI299428', 'renglon_id' => 833803, 'num_rotulos' => 5,
            'impresora' => 'ZTC-230-ZPL-INV', 'user_id' => $user->id,
        ]);
        $this->assertDatabaseHas('sf_rotulos_mp_impresiones', ['renglon_id' => 833804, 'num_rotulos' => 2]);
    }

    public function test_tope_de_etiquetas_bloquea_la_impresion(): void
    {
        $this->actingAs($this->usuario())
            ->postJson('/consultas-lotes/rotulos/imprimir', [
                'documento' => 'SLI299428',
                'pesos'     => ['833803' => '25'],
            ])
            ->assertStatus(422)
            ->assertJsonPath('success', false);

        $this->assertSame([], $this->impresora->envios);
        $this->assertDatabaseCount('sf_rotulos_mp_impresiones', 0);
    }

    public function test_renglon_ajeno_al_documento_se_rechaza(): void
    {
        $this->actingAs($this->usuario())
            ->postJson('/consultas-lotes/rotulos/imprimir', [
                'documento' => 'SLI299428',
                'pesos'     => ['999999' => '25000'],
            ])
            ->assertStatus(422);

        $this->assertSame([], $this->impresora->envios);
    }

    public function test_impresora_caida_no_registra_nada(): void
    {
        $this->impresora->simularFallo();

        $this->actingAs($this->usuario())
            ->postJson('/consultas-lotes/rotulos/imprimir', [
                'documento' => 'SLI299428',
                'pesos'     => ['833803' => '25000'],
            ])
            ->assertStatus(422)
            ->assertJsonPath('success', false);

        $this->assertDatabaseCount('sf_rotulos_mp_impresiones', 0);
    }

    public function test_roles_sin_acceso_no_pueden_imprimir(): void
    {
        $this->actingAs($this->usuario('comercial'))
            ->postJson('/consultas-lotes/rotulos/imprimir', [
                'documento' => 'SLI299428',
                'pesos'     => ['833803' => '25000'],
            ])
            ->assertForbidden();

        $this->assertSame([], $this->impresora->envios);
    }

    public function test_la_vista_muestra_el_boton_de_rotulos(): void
    {
        $this->actingAs($this->usuario())
            ->get('/consultas-lotes')
            ->assertOk()
            ->assertSee('Rótulos Material');
    }
}
