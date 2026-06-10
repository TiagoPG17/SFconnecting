<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Domain\Auditoria\Models\ActividadLog;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RegistrarActividad
{
    // Mapeo de segmentos de URL a nombre de módulo
    private const MODULOS = [
        'clientes'         => 'Clientes',
        'contactos'        => 'Contactos',
        'prospectos'       => 'Prospectos',
        'negocios'         => 'Negocios',
        'seguimientos'     => 'Seguimientos',
        'reportes'         => 'Reportes',
        'usuarios'         => 'Usuarios',
        'presupuestos'     => 'Presupuestos',
        'mapeo-vendedores' => 'Mapeo vendedores',
        'maestros'         => 'Maestros CRM',
        'dashboard'        => 'Dashboard',
        'mi-desempeno'     => 'Mi desempeño',
        'gerencial'        => 'Visión gerencial',
    ];

    // Rutas que NO registramos (assets, health, etc.)
    private const IGNORAR = [
        'logout', 'login', 'up',
    ];

    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Solo registrar si hay usuario autenticado y la respuesta es exitosa
        if (! auth()->check() || ! $this->debeRegistrar($request, $response)) {
            return $response;
        }

        try {
            [$modulo, $accion, $descripcion] = $this->extraerContexto($request);
            ActividadLog::registrar($accion, $modulo, $descripcion);
        } catch (\Throwable) {
            // La auditoría nunca debe romper la aplicación
        }

        return $response;
    }

    private function debeRegistrar(Request $request, Response $response): bool
    {
        // Solo respuestas exitosas (no errores 4xx/5xx)
        if ($response->getStatusCode() >= 400) {
            return false;
        }

        $segmento = $request->segment(1) ?? '';

        // Ignorar rutas específicas
        if (in_array($segmento, self::IGNORAR, true)) {
            return false;
        }

        // GET solo en páginas principales (no AJAX, no paginación repetida)
        if ($request->isMethod('GET') && ! $this->esVistaRelevante($request)) {
            return false;
        }

        return isset(self::MODULOS[$segmento]);
    }

    private function esVistaRelevante(Request $request): bool
    {
        $path = $request->path();
        // Registrar solo vistas de índice o detalle, no cada paginación
        return ! str_contains($path, '?page=')
            && ! $request->ajax()
            && ! $request->wantsJson();
    }

    private function extraerContexto(Request $request): array
    {
        $segmento = $request->segment(1) ?? 'general';
        $modulo   = self::MODULOS[$segmento] ?? ucfirst($segmento);
        $metodo   = $request->method();
        $nombre   = null;

        $accion = match (true) {
            $metodo === 'GET'                          => 'ver',
            $metodo === 'POST'                         => 'crear',
            in_array($metodo, ['PUT', 'PATCH'], true)  => 'editar',
            $metodo === 'DELETE'                       => 'eliminar',
            default                                    => 'acceso',
        };

        // Descripción contextual
        $descripcion = match ($accion) {
            'ver'     => "Visitó {$modulo}",
            'crear'   => "Creó registro en {$modulo}",
            'editar'  => "Editó registro en {$modulo}",
            'eliminar'=> "Eliminó registro en {$modulo}",
            default   => "Accedió a {$modulo}",
        };

        return [$modulo, $accion, $descripcion];
    }
}
