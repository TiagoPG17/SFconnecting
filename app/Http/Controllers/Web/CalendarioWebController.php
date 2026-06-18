<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web;

use App\Domain\Seguimientos\Models\Seguimiento;
use App\Http\Controllers\Controller;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CalendarioWebController extends Controller
{
    private const COLORES = [
        'llamada'  => '#0ea5e9',
        'reunion'  => '#7c3aed',
        'email'    => '#475569',
        'visita'   => '#0d9488',
        'whatsapp' => '#16a34a',
        'otro'     => '#94a3b8',
    ];

    public function index(Request $request): View
    {
        $asesores = $request->user()->hasAnyRole(['admin', 'gerente'])
            ? User::role(['comercial', 'admin'])->orderBy('name')->get(['id', 'name'])->toArray()
            : [];

        return view('calendario.index', compact('asesores'));
    }

    public function eventos(Request $request): JsonResponse
    {
        $user      = $request->user();
        $start     = Carbon::parse($request->input('start'));
        $end       = Carbon::parse($request->input('end'));
        $asesorId  = (int) $request->input('asesor', 0);
        $tipos     = array_filter((array) $request->input('tipos', []));
        $resultado = (string) $request->input('resultado', '');

        $query = Seguimiento::with([
                'cliente:id,razon_social',
                'prospecto:id,empresa',
                'asesor:id,name',
            ])
            ->where(function ($q) use ($start, $end) {
                $q->whereBetween('fecha_seguimiento', [$start, $end])
                  ->orWhere(fn ($q2) => $q2
                      ->whereNotNull('proxima_fecha')
                      ->whereBetween('proxima_fecha', [$start, $end])
                  );
            });

        if ($user->hasRole('comercial')) {
            $query->where('user_id', $user->id);
        } elseif ($asesorId > 0) {
            $query->where('user_id', $asesorId);
        }

        if (!empty($tipos)) {
            $query->whereIn('tipo', $tipos);
        }

        if ($resultado !== '') {
            $query->where('resultado', $resultado);
        }

        $eventos = [];

        foreach ($query->get() as $s) {
            $entidad   = $s->cliente?->razon_social ?? $s->prospecto?->empresa ?? 'Sin cliente';
            $color     = self::COLORES[$s->tipo] ?? '#94a3b8';
            $tipoLabel = ucfirst($s->tipo);
            $url       = $s->cliente_id
                ? route('clientes.show', $s->cliente_id)
                : ($s->prospecto_id ? route('prospectos.show', $s->prospecto_id) : null);

            // Actividad realizada (fecha_seguimiento)
            if ($s->fecha_seguimiento?->between($start, $end)) {
                $isPending = $s->resultado === 'pendiente';
                $eventos[] = [
                    'id'              => 'fs-'.$s->id,
                    'title'           => $tipoLabel.' · '.$entidad,
                    'start'           => $s->fecha_seguimiento->toIso8601String(),
                    'backgroundColor' => $isPending ? $color : $color.'28',
                    'borderColor'     => $color,
                    'textColor'       => $isPending ? '#fff' : $color,
                    'extendedProps'   => [
                        'tipo'          => $s->tipo,
                        'resultado'     => $s->resultado,
                        'descripcion'   => $s->descripcion,
                        'entidad'       => $entidad,
                        'asesor'        => $s->asesor?->name ?? '',
                        'esCita'        => false,
                        'url'           => $url,
                        'seguimientoId' => $s->id,
                        'fecha'         => $s->fecha_seguimiento->toIso8601String(),
                    ],
                ];
            }

            // Cita programada (proxima_fecha)
            if ($s->proxima_fecha?->between($start, $end)) {
                $eventos[] = [
                    'id'              => 'pf-'.$s->id,
                    'title'           => $tipoLabel.' · '.$entidad,
                    'start'           => $s->proxima_fecha->toIso8601String(),
                    'backgroundColor' => $color,
                    'borderColor'     => $color,
                    'textColor'       => '#fff',
                    'extendedProps'   => [
                        'tipo'          => $s->tipo,
                        'resultado'     => $s->resultado,
                        'descripcion'   => $s->descripcion,
                        'entidad'       => $entidad,
                        'asesor'        => $s->asesor?->name ?? '',
                        'esCita'        => true,
                        'url'           => $url,
                        'seguimientoId' => $s->id,
                        'fecha'         => $s->proxima_fecha->toIso8601String(),
                    ],
                ];
            }
        }

        return response()->json($eventos);
    }
}
