<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web;

use App\Domain\Auditoria\Models\ActividadLog;
use App\Domain\Clientes\Models\Contacto;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Throwable;

class ContactoWebController extends Controller
{
    public function toggle(Request $request, Contacto $contacto): RedirectResponse|JsonResponse
    {
        $this->authorize('update', $contacto);

        try {
            $nuevoValor = ! $contacto->activo;
            $contacto->update(['activo' => $nuevoValor]);

            $accion = $nuevoValor ? 'reactivar' : 'desactivar';
            $msg    = $nuevoValor ? 'Contacto reactivado.' : 'Contacto desactivado.';

            Log::info("[Contactos] Toggle exitoso: contacto #{$contacto->id} -> activo={$nuevoValor}");

            try {
                ActividadLog::registrar($accion, 'contactos', "Contacto '{$contacto->nombre}' {$accion}do (cliente #{$contacto->cliente_id})");
            } catch (Throwable $e) {
                Log::error("[Contactos] No se pudo registrar ActividadLog para el toggle del contacto #{$contacto->id}: {$e->getMessage()}", [
                    'exception' => $e,
                ]);
            }

            if ($request->wantsJson()) {
                return response()->json(['success' => true, 'message' => $msg, 'activo' => $nuevoValor]);
            }

            return back()->with('success', $msg);
        } catch (Throwable $e) {
            Log::error("[Contactos] Error al hacer toggle del contacto #{$contacto->id}: {$e->getMessage()}", [
                'exception' => $e,
            ]);

            if ($request->wantsJson()) {
                return response()->json(['success' => false, 'message' => 'No se pudo actualizar el contacto: ' . $e->getMessage()], 500);
            }

            return back()->withErrors(['contacto' => 'No se pudo actualizar el contacto. Revisa storage/logs/laravel.log.']);
        }
    }
}
