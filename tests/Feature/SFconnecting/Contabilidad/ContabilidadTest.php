<?php

declare(strict_types=1);

namespace Tests\Feature\SFconnecting\Contabilidad;

use App\Domain\Clientes\Models\Cliente;
use App\Domain\Pipeline\Models\PipelineEstado;
use App\Domain\Prospectos\Models\Prospecto;
use App\Domain\SolicitudesCredito\Models\SolicitudCredito;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class ContabilidadTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private User $comercial;
    private User $contabilidadFormacol;
    private User $contabilidadContiflex;
    private PipelineEstado $estado;

    protected function setUp(): void
    {
        parent::setUp();
        app()['cache']->forget('spatie.permission.cache');

        foreach ([
            'prospectos.ver', 'prospectos.crear', 'prospectos.editar', 'prospectos.convertir',
            'clientes.ver', 'clientes.revisar_contabilidad',
            'solicitudes_credito.ver', 'solicitudes_credito.crear',
        ] as $p) {
            Permission::firstOrCreate(['name' => $p, 'guard_name' => 'web']);
        }

        Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web'])->syncPermissions(Permission::all());
        Role::firstOrCreate(['name' => 'comercial', 'guard_name' => 'web'])->syncPermissions([
            'prospectos.ver', 'prospectos.crear', 'prospectos.editar', 'prospectos.convertir', 'clientes.ver',
            'solicitudes_credito.ver', 'solicitudes_credito.crear',
        ]);
        Role::firstOrCreate(['name' => 'contabilidad_formacol', 'guard_name' => 'web'])->syncPermissions([
            'clientes.ver', 'clientes.revisar_contabilidad',
        ]);
        Role::firstOrCreate(['name' => 'contabilidad_contiflex', 'guard_name' => 'web'])->syncPermissions([
            'clientes.ver', 'clientes.revisar_contabilidad',
        ]);

        $this->admin                 = User::factory()->create()->assignRole('admin');
        $this->comercial             = User::factory()->create()->assignRole('comercial');
        $this->contabilidadFormacol  = User::factory()->create()->assignRole('contabilidad_formacol');
        $this->contabilidadContiflex = User::factory()->create()->assignRole('contabilidad_contiflex');

        $this->estado = PipelineEstado::create([
            'nombre' => 'Nuevo Lead', 'slug' => 'nuevo-lead-contab', 'tipo' => 'prospecto',
            'color' => '#6B7280', 'orden' => 1, 'porcentaje_cierre' => 5,
            'es_final' => false, 'es_ganado' => false, 'es_perdido' => false, 'activo' => true,
        ]);
    }

    public function test_convertir_prospecto_guarda_datos_carga_y_queda_pendiente_de_contabilidad(): void
    {
        $prospecto = Prospecto::create([
            'codigo' => 'PROS-00001', 'empresa' => 'Empresa Convertir', 'contacto' => 'Juan',
            'estado_pipeline_id' => $this->estado->id, 'asesor_id' => $this->comercial->id, 'activo' => true,
        ]);

        $datosCarga = [
            ['nombre' => 'Ana Pérez', 'cargo' => 'Gerente'],
            ['nombre' => 'Luis Gómez', 'cargo' => 'Contador'],
        ];

        $response = $this->actingAs($this->comercial, 'sanctum')
            ->postJson("/api/prospectos/{$prospecto->id}/convertir", [
                'razon_social' => 'Empresa Convertir SAS',
                'nit'          => '900123456',
                'datos_carga'  => $datosCarga,
            ]);

        $response->assertOk()->assertJsonPath('success', true);

        $cliente = Cliente::where('razon_social', 'Empresa Convertir SAS')->firstOrFail();

        $this->assertNull($cliente->revisado_contabilidad_en);
        $this->assertNull($cliente->revisado_contabilidad_por);
        $this->assertEquals($datosCarga, $cliente->datos_carga);
        $this->assertTrue($cliente->pendienteContabilidad());
    }

    public function test_convertir_prospecto_con_solicita_cupo_radica_solicitud_de_credito_sin_negocio(): void
    {
        PipelineEstado::create([
            'nombre' => 'Radicada', 'slug' => 'credito-radicada', 'tipo' => 'solicitud_credito',
            'color' => '#6B7280', 'orden' => 1, 'porcentaje_cierre' => 10,
            'es_final' => false, 'es_ganado' => false, 'es_perdido' => false, 'activo' => true,
        ]);

        $prospecto = Prospecto::create([
            'codigo' => 'PROS-00002', 'empresa' => 'Empresa Cupo', 'contacto' => 'Juan',
            'estado_pipeline_id' => $this->estado->id, 'asesor_id' => $this->comercial->id, 'activo' => true,
        ]);

        $response = $this->actingAs($this->comercial, 'sanctum')
            ->postJson("/api/prospectos/{$prospecto->id}/convertir", [
                'razon_social'            => 'Empresa Cupo SAS',
                'nit'                     => '900999888',
                'solicita_cupo'           => true,
                'monto_solicitado'        => 8000000,
                'plazo_solicitado_dias'   => 30,
                'referencias_comerciales' => [
                    ['empresa' => 'Ferretería XYZ', 'telefono' => '3001234567', 'nit' => '900111222'],
                    ['empresa' => 'Distribuidora ABC', 'telefono' => '3009876543', 'nit' => '900333444'],
                ],
                'inventario_consignacion' => true,
            ]);

        $response->assertOk()->assertJsonPath('success', true);

        $cliente = Cliente::where('razon_social', 'Empresa Cupo SAS')->firstOrFail();

        $this->assertDatabaseHas('sf_solicitudes_credito', [
            'cliente_id'               => $cliente->id,
            'negocio_id'               => null,
            'monto_solicitado'         => 8000000,
            'plazo_solicitado_dias'    => 30,
            'inventario_consignacion'  => true,
        ]);

        $solicitud = SolicitudCredito::where('cliente_id', $cliente->id)->firstOrFail();
        $this->assertCount(2, $solicitud->referencias_comerciales);
        $this->assertSame('Ferretería XYZ', $solicitud->referencias_comerciales[0]['empresa']);
    }

    public function test_convertir_prospecto_sin_marcar_solicita_cupo_no_radica_solicitud(): void
    {
        $prospecto = Prospecto::create([
            'codigo' => 'PROS-00004', 'empresa' => 'Empresa Sin Cupo', 'contacto' => 'Juan',
            'estado_pipeline_id' => $this->estado->id, 'asesor_id' => $this->comercial->id, 'activo' => true,
        ]);

        $this->actingAs($this->comercial, 'sanctum')
            ->postJson("/api/prospectos/{$prospecto->id}/convertir", [
                'razon_social' => 'Empresa Sin Cupo SAS',
                'nit'          => '900777666',
            ])->assertOk();

        $cliente = Cliente::where('razon_social', 'Empresa Sin Cupo SAS')->firstOrFail();

        $this->assertDatabaseMissing('sf_solicitudes_credito', ['cliente_id' => $cliente->id]);
    }

    public function test_vista_de_convertir_prospecto_renderiza_los_campos_del_cliente(): void
    {
        $prospecto = Prospecto::create([
            'codigo' => 'PROS-00003', 'empresa' => 'Empresa Vista Convertir', 'contacto' => 'Juan',
            'estado_pipeline_id' => $this->estado->id, 'asesor_id' => $this->comercial->id, 'activo' => true,
        ]);

        $response = $this->actingAs($this->comercial)->get("/prospectos/{$prospecto->id}/convertir");

        $response->assertOk();
        foreach (config('cliente_datos_carga_campos') as $campo) {
            $response->assertSee($campo['label']);
        }

        // El bloque x-data va entre comillas dobles; si "campos" se serializara con
        // @json() en vez de Js::from(), las comillas del JSON cerrarían el atributo
        // antes de tiempo y el resto del script quedaría visible como texto plano.
        $response->assertDontSee('"key":"', false);
    }

    public function test_vista_de_convertir_requiere_un_prospecto_valido_en_la_url(): void
    {
        $response = $this->actingAs($this->comercial)->get('/prospectos/999999/convertir');

        $response->assertNotFound();
    }

    public function test_vista_de_contabilidad_muestra_los_campos_del_cliente_con_su_etiqueta(): void
    {
        $cliente = Cliente::factory()->compania(1)->create([
            'datos_carga' => [
                ['razon_social' => 'Ana Pérez SAS', 'nit' => '900999888', 'email' => 'ana@x.co', 'telefono_corporativo' => '300'],
            ],
        ]);

        $response = $this->actingAs($this->contabilidadFormacol)->get("/contabilidad/{$cliente->id}");

        $response->assertOk();
        $response->assertSee('Razón social');
        $response->assertSee('Ana Pérez SAS');
    }

    public function test_contabilidad_formacol_solo_ve_clientes_de_formacol(): void
    {
        Cliente::factory()->compania(1)->create(['razon_social' => 'Cliente Formacol', 'datos_carga' => [['razon_social' => 'Cliente Formacol']]]);
        Cliente::factory()->compania(2)->create(['razon_social' => 'Cliente Contiflex', 'datos_carga' => [['razon_social' => 'Cliente Contiflex']]]);

        $response = $this->actingAs($this->contabilidadFormacol)->get('/contabilidad');

        $response->assertOk();
        $response->assertSee('Cliente Formacol');
        $response->assertDontSee('Cliente Contiflex');
    }

    public function test_contabilidad_contiflex_solo_ve_clientes_de_contiflex(): void
    {
        Cliente::factory()->compania(1)->create(['razon_social' => 'Cliente Formacol', 'datos_carga' => [['razon_social' => 'Cliente Formacol']]]);
        Cliente::factory()->compania(2)->create(['razon_social' => 'Cliente Contiflex', 'datos_carga' => [['razon_social' => 'Cliente Contiflex']]]);

        $response = $this->actingAs($this->contabilidadContiflex)->get('/contabilidad');

        $response->assertOk();
        $response->assertSee('Cliente Contiflex');
        $response->assertDontSee('Cliente Formacol');
    }

    public function test_clientes_sin_datos_carga_no_aparecen_como_pendientes(): void
    {
        // Simula un cliente que ya existía antes de este módulo (sincronizado del ERP,
        // creado por otra vía, etc.) — nunca pasó por la conversión con archivo plano,
        // así que no debe aparecer en la cola de Contabilidad aunque nunca se haya
        // marcado "revisado_contabilidad_en".
        Cliente::factory()->compania(1)->create(['razon_social' => 'Cliente Viejo Sin Archivo', 'datos_carga' => null]);
        Cliente::factory()->compania(1)->create(['razon_social' => 'Cliente Nuevo Con Archivo', 'datos_carga' => [['razon_social' => 'Cliente Nuevo Con Archivo']]]);

        $response = $this->actingAs($this->contabilidadFormacol)->get('/contabilidad');

        $response->assertOk();
        $response->assertSee('Cliente Nuevo Con Archivo');
        $response->assertDontSee('Cliente Viejo Sin Archivo');
    }

    public function test_buscador_filtra_por_razon_social_o_nit(): void
    {
        Cliente::factory()->compania(1)->create(['razon_social' => 'Alfa SAS', 'nit' => '111111111', 'datos_carga' => [['razon_social' => 'Alfa SAS']]]);
        Cliente::factory()->compania(1)->create(['razon_social' => 'Beta SAS', 'nit' => '222222222', 'datos_carga' => [['razon_social' => 'Beta SAS']]]);

        $response = $this->actingAs($this->contabilidadFormacol)->get('/contabilidad?buscar=Alfa');

        $response->assertOk();
        $response->assertSee('Alfa SAS');
        $response->assertDontSee('Beta SAS');
    }

    public function test_marcar_registrado_actualiza_cliente_y_lo_saca_de_pendientes(): void
    {
        $cliente = Cliente::factory()->compania(1)->create(['razon_social' => 'Cliente A Marcar', 'datos_carga' => [['razon_social' => 'Cliente A Marcar']]]);

        $response = $this->actingAs($this->contabilidadFormacol, 'sanctum')
            ->postJson("/api/clientes/{$cliente->id}/marcar-registrado-contabilidad");

        $response->assertOk()->assertJsonPath('success', true);

        $cliente->refresh();
        $this->assertNotNull($cliente->revisado_contabilidad_en);
        $this->assertEquals($this->contabilidadFormacol->id, $cliente->revisado_contabilidad_por);

        $indexResponse = $this->actingAs($this->contabilidadFormacol)->get('/contabilidad');
        $indexResponse->assertDontSee('Cliente A Marcar');
    }

    public function test_comercial_no_puede_marcar_registrado_en_contabilidad(): void
    {
        $cliente = Cliente::factory()->compania(1)->create();

        $response = $this->actingAs($this->comercial, 'sanctum')
            ->postJson("/api/clientes/{$cliente->id}/marcar-registrado-contabilidad");

        $response->assertForbidden();
    }
}
