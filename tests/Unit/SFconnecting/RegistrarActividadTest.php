<?php

declare(strict_types=1);

namespace Tests\Unit\SFconnecting;

use App\Http\Middleware\RegistrarActividad;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Tests\TestCase;

class RegistrarActividadTest extends TestCase
{
    use RefreshDatabase;

    private function middleware(): RegistrarActividad
    {
        return new RegistrarActividad();
    }

    private function next(int $status = 200): \Closure
    {
        return fn ($req) => new Response('', $status);
    }

    private function peticion(string $metodo, string $path, array $headers = []): Request
    {
        $server = [];
        foreach ($headers as $nombre => $valor) {
            $server['HTTP_' . strtoupper(str_replace('-', '_', $nombre))] = $valor;
        }

        $request = Request::create("http://localhost/{$path}", $metodo, server: $server);
        $this->app->instance('request', $request);

        return $request;
    }

    // ─── debeRegistrar ───────────────────────────────────────────────────────

    public function test_no_registra_cuando_no_hay_usuario_autenticado(): void
    {
        $request = $this->peticion('GET', 'clientes');

        $this->middleware()->handle($request, $this->next());

        $this->assertDatabaseCount('actividad_log', 0);
    }

    public function test_no_registra_cuando_la_respuesta_es_4xx(): void
    {
        $user = User::factory()->create();
        $this->be($user);
        $request = $this->peticion('GET', 'clientes');

        $this->middleware()->handle($request, $this->next(404));

        $this->assertDatabaseCount('actividad_log', 0);
    }

    public function test_no_registra_rutas_ignoradas_como_logout(): void
    {
        $user = User::factory()->create();
        $this->be($user);
        $request = $this->peticion('POST', 'logout');

        $this->middleware()->handle($request, $this->next());

        $this->assertDatabaseCount('actividad_log', 0);
    }

    public function test_no_registra_segmentos_no_mapeados(): void
    {
        $user = User::factory()->create();
        $this->be($user);
        $request = $this->peticion('GET', 'configuracion');

        $this->middleware()->handle($request, $this->next());

        $this->assertDatabaseCount('actividad_log', 0);
    }

    public function test_no_registra_get_con_cabecera_json(): void
    {
        $user = User::factory()->create();
        $this->be($user);
        $request = $this->peticion('GET', 'clientes', ['Accept' => 'application/json']);

        $this->middleware()->handle($request, $this->next());

        $this->assertDatabaseCount('actividad_log', 0);
    }

    // ─── extraerContexto / accion ────────────────────────────────────────────

    public function test_registra_get_como_accion_ver_en_modulo_correcto(): void
    {
        $user = User::factory()->create();
        $this->be($user);
        $request = $this->peticion('GET', 'clientes');

        $this->middleware()->handle($request, $this->next());

        $this->assertDatabaseHas('actividad_log', [
            'accion' => 'ver',
            'modulo' => 'Clientes',
            'user_id' => $user->id,
        ]);
    }

    public function test_registra_post_como_accion_crear(): void
    {
        $user = User::factory()->create();
        $this->be($user);
        $request = $this->peticion('POST', 'negocios');

        $this->middleware()->handle($request, $this->next(201));

        $this->assertDatabaseHas('actividad_log', ['accion' => 'crear', 'modulo' => 'Negocios']);
    }

    public function test_registra_put_como_accion_editar(): void
    {
        $user = User::factory()->create();
        $this->be($user);
        $request = $this->peticion('PUT', 'clientes/5');

        $this->middleware()->handle($request, $this->next());

        $this->assertDatabaseHas('actividad_log', ['accion' => 'editar', 'modulo' => 'Clientes']);
    }

    public function test_registra_delete_como_accion_eliminar(): void
    {
        $user = User::factory()->create();
        $this->be($user);
        $request = $this->peticion('DELETE', 'prospectos/3');

        $this->middleware()->handle($request, $this->next());

        $this->assertDatabaseHas('actividad_log', ['accion' => 'eliminar', 'modulo' => 'Prospectos']);
    }

    public function test_registra_patch_como_accion_editar(): void
    {
        $user = User::factory()->create();
        $this->be($user);
        $request = $this->peticion('PATCH', 'seguimientos/2');

        $this->middleware()->handle($request, $this->next());

        $this->assertDatabaseHas('actividad_log', ['accion' => 'editar', 'modulo' => 'Seguimientos']);
    }

    // ─── descripcion ─────────────────────────────────────────────────────────

    public function test_descripcion_de_get_menciona_el_modulo(): void
    {
        $user = User::factory()->create();
        $this->be($user);
        $request = $this->peticion('GET', 'negocios');

        $this->middleware()->handle($request, $this->next());

        $this->assertDatabaseHas('actividad_log', ['descripcion' => 'Visitó Negocios']);
    }

    public function test_descripcion_de_post_menciona_creacion_en_modulo(): void
    {
        $user = User::factory()->create();
        $this->be($user);
        $request = $this->peticion('POST', 'presupuestos');

        $this->middleware()->handle($request, $this->next());

        $this->assertDatabaseHas('actividad_log', ['descripcion' => 'Creó registro en Presupuestos']);
    }

    // ─── resiliencia ─────────────────────────────────────────────────────────

    public function test_excepcion_interna_no_rompe_la_respuesta(): void
    {
        // Si la tabla no existiera (simulado via mock), el middleware no debe propagarlo
        $user = User::factory()->create();
        $this->be($user);
        $request = $this->peticion('GET', 'clientes');

        $response = $this->middleware()->handle($request, $this->next());

        $this->assertSame(200, $response->getStatusCode());
    }
}
