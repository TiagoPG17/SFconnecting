<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Autentica llamadas de n8n a la API de cartera con un token estático
 * (Authorization: Bearer ...) en vez de sesión/Sanctum — n8n no es un
 * usuario de la app, es un sistema externo llamando de servidor a servidor.
 */
class VerifyN8nToken
{
    public function handle(Request $request, Closure $next): Response
    {
        $esperado = config('services.n8n.token');
        $recibido = $request->bearerToken();

        if (! $esperado || ! $recibido || ! hash_equals($esperado, $recibido)) {
            abort(401, 'Token inválido o ausente.');
        }

        return $next($request);
    }
}
