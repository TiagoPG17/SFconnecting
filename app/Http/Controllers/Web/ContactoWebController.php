<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web;

use App\Domain\Auditoria\Models\ActividadLog;
use App\Domain\Clientes\Models\Contacto;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;

class ContactoWebController extends Controller
{
    public function toggle(Contacto $contacto): RedirectResponse
    {
        $contacto->update(['activo' => !$contacto->activo]);

        $accion = $contacto->activo ? 'reactivar' : 'desactivar';
        $msg    = $contacto->activo ? 'Contacto reactivado.' : 'Contacto desactivado.';

        ActividadLog::registrar($accion, 'contactos', "Contacto '{$contacto->nombre}' {$accion}do (cliente #{$contacto->cliente_id})");

        return back()->with('success', $msg);
    }
}
