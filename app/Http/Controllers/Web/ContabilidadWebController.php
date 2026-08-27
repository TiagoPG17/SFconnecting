<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web;

use App\Domain\Clientes\Models\Cliente;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ContabilidadWebController extends Controller
{
    public function index(Request $request): View
    {
        $compania = $this->companiaDelUsuario($request);

        $query = Cliente::with('asesor')->latest();

        if ($compania !== null) {
            $query->pendientesContabilidad($compania);
        } else {
            $query->whereNull('revisado_contabilidad_en')->whereNotNull('datos_carga');
        }

        if ($buscar = $request->get('buscar')) {
            $query->where(function ($q) use ($buscar) {
                $q->where('razon_social', 'like', "%{$buscar}%")
                    ->orWhere('nit', 'like', "%{$buscar}%");
            });
        }

        $clientes = $query->paginate(15);

        return view('contabilidad.index', compact('clientes', 'compania'));
    }

    public function show(Cliente $cliente): View
    {
        $cliente->load(['asesor', 'contactos', 'revisadoContabilidadPor']);

        return view('contabilidad.show', compact('cliente'));
    }

    private function companiaDelUsuario(Request $request): ?int
    {
        $user = $request->user();

        if ($user->hasRole('contabilidad_formacol')) {
            return 1;
        }

        if ($user->hasRole('contabilidad_contiflex')) {
            return 2;
        }

        return $request->integer('compania') ?: null;
    }
}
