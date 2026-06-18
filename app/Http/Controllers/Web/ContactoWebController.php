<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web;

use App\Domain\Clientes\Models\Contacto;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;

class ContactoWebController extends Controller
{
    public function toggle(Contacto $contacto): RedirectResponse
    {
        $contacto->update(['activo' => !$contacto->activo]);

        $msg = $contacto->activo ? 'Contacto reactivado.' : 'Contacto desactivado.';

        return back()->with('success', $msg);
    }
}
