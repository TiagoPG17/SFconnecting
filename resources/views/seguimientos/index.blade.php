<x-layouts.app title="Seguimientos" :hide-page-title="true">

@php
$tipoIconos = [
    'llamada'  => 'M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z',
    'reunion'  => 'M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z',
    'email'    => 'M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z',
    'visita'   => 'M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z M15 11a3 3 0 11-6 0 3 3 0 016 0z',
    'whatsapp' => 'M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z',
    'otro'     => 'M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z',
];
$resColors = [
    'exitoso'       => ['bg' => '#dcfce7', 'text' => '#15803d'],
    'pendiente'     => ['bg' => '#fef9c3', 'text' => '#854d0e'],
    'no_contactado' => ['bg' => '#f1f5f9', 'text' => '#475569'],
    'cancelado'     => ['bg' => '#fee2e2', 'text' => '#b91c1c'],
];
@endphp

{{-- Encabezado + Filtros unificados --}}
<div class="bg-white border border-slate-200 rounded-2xl shadow-sm px-6 pt-5 pb-4 mb-6"
     x-data="{
         entidad:   '{{ request('entidad') }}',
         tipo:      '{{ request('tipo') }}',
         resultado: '{{ request('resultado') }}',
         desde:     '{{ request('fecha_desde') }}',
         hasta:     '{{ request('fecha_hasta') }}',
         goFilter() {
             let url = new URL(window.location.href);
             this.entidad   ? url.searchParams.set('entidad', this.entidad)     : url.searchParams.delete('entidad');
             this.tipo      ? url.searchParams.set('tipo', this.tipo)           : url.searchParams.delete('tipo');
             this.resultado ? url.searchParams.set('resultado', this.resultado) : url.searchParams.delete('resultado');
             this.desde     ? url.searchParams.set('fecha_desde', this.desde)   : url.searchParams.delete('fecha_desde');
             this.hasta     ? url.searchParams.set('fecha_hasta', this.hasta)   : url.searchParams.delete('fecha_hasta');
             window.location.href = url.toString();
         }
     }">

    <div class="flex items-end justify-between gap-6">

        {{-- Título izquierda --}}
        <div>
            <p class="text-xs font-semibold uppercase tracking-widest text-slate-400 mb-0.5">Actividad comercial</p>
            <h1 class="text-2xl font-bold text-slate-900 leading-tight">Seguimientos</h1>
            <p class="text-sm text-slate-400 mt-0.5">
                {{ $seguimientos->total() }} registro{{ $seguimientos->total() !== 1 ? 's' : '' }}
                @if($clienteSeleccionado)
                    · <span class="text-slate-500">{{ $clienteSeleccionado->razon_social }}</span>
                    <a href="{{ route('seguimientos.index') }}" class="ml-1 text-slate-300 hover:text-red-400 transition-colors text-xs">✕</a>
                @endif
            </p>
        </div>

        {{-- Filtros derecha --}}
        <div class="flex items-end gap-3 shrink-0">
            <div>
                <p class="text-[10px] font-semibold uppercase tracking-widest text-slate-400 mb-1.5">Entidad</p>
                <select x-model="entidad" @change="goFilter()"
                        class="text-sm rounded-lg border border-slate-200 bg-slate-50 text-slate-700 px-3 py-2 focus:outline-none focus:ring-2 focus:ring-slate-300">
                    <option value="">Todos</option>
                    <option value="prospecto">Prospectos</option>
                    <option value="cliente">Clientes</option>
                </select>
            </div>
            <div>
                <p class="text-[10px] font-semibold uppercase tracking-widest text-slate-400 mb-1.5">Tipo</p>
                <select x-model="tipo" @change="goFilter()"
                        class="text-sm rounded-lg border border-slate-200 bg-slate-50 text-slate-700 px-3 py-2 focus:outline-none focus:ring-2 focus:ring-slate-300">
                    <option value="">Todos</option>
                    <option value="llamada">Llamada</option>
                    <option value="reunion">Reunión</option>
                    <option value="email">Email</option>
                    <option value="visita">Visita</option>
                    <option value="whatsapp">WhatsApp</option>
                    <option value="otro">Otro</option>
                </select>
            </div>
            <div>
                <p class="text-[10px] font-semibold uppercase tracking-widest text-slate-400 mb-1.5">Estado</p>
                <select x-model="resultado" @change="goFilter()"
                        class="text-sm rounded-lg border border-slate-200 bg-slate-50 text-slate-700 px-3 py-2 focus:outline-none focus:ring-2 focus:ring-slate-300">
                    <option value="">Todos</option>
                    <option value="pendiente">Pendiente</option>
                    <option value="exitoso">Exitoso</option>
                    <option value="no_contactado">No contactado</option>
                    <option value="cancelado">Cancelado</option>
                </select>
            </div>
            <div>
                <p class="text-[10px] font-semibold uppercase tracking-widest text-slate-400 mb-1.5">Desde</p>
                <input type="date" x-model="desde" @change="goFilter()"
                       class="text-sm px-3 py-2 rounded-lg border border-slate-200 text-slate-700 bg-slate-50 focus:outline-none focus:ring-2 focus:ring-slate-300">
            </div>
            <div>
                <p class="text-[10px] font-semibold uppercase tracking-widest text-slate-400 mb-1.5">Hasta</p>
                <input type="date" x-model="hasta" @change="goFilter()"
                       class="text-sm px-3 py-2 rounded-lg border border-slate-200 text-slate-700 bg-slate-50 focus:outline-none focus:ring-2 focus:ring-slate-300">
            </div>
            @if(request()->hasAny(['entidad', 'tipo', 'resultado', 'fecha_desde', 'fecha_hasta']))
            <a href="{{ route('seguimientos.index', request()->only('cliente_id')) }}"
               class="text-xs text-slate-400 hover:text-red-500 transition-colors flex items-center gap-1 pb-2.5">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                </svg>
                Limpiar
            </a>
            @endif
        </div>

    </div>
</div>

@php
$sortActual = request('sort', 'fecha');
$dirActual  = request('dir', 'desc');
$sortUrl = function(string $col) use ($sortActual, $dirActual): string {
    $newDir = ($sortActual === $col && $dirActual === 'desc') ? 'asc' : 'desc';
    return request()->fullUrlWithQuery(['sort' => $col, 'dir' => $newDir]);
};
$sortIcon = function(string $col) use ($sortActual, $dirActual): string {
    if ($sortActual !== $col) {
        return '<svg class="w-3 h-3 opacity-30" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M7 16V4m0 0L3 8m4-4l4 4M17 8v12m0 0l4-4m-4 4l-4-4"/></svg>';
    }
    return $dirActual === 'asc'
        ? '<svg class="w-3 h-3 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M5 15l7-7 7 7"/></svg>'
        : '<svg class="w-3 h-3 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/></svg>';
};
@endphp

{{-- Lista --}}
@if($seguimientos->isEmpty())
<div class="bg-white border border-slate-200 rounded-2xl p-12 text-center shadow-sm">
    <svg class="w-10 h-10 text-slate-300 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5">
        <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
    </svg>
    <p class="text-slate-500 font-medium">Sin seguimientos</p>
    <p class="text-sm text-slate-400 mt-1">Los seguimientos se registran desde la ficha de cada cliente.</p>
</div>
@else
<div class="bg-white border border-slate-200 rounded-2xl shadow-sm overflow-hidden" x-data="{ open: false, detalle: null }">
    <table class="w-full text-sm">
        <thead>
            <tr class="border-b border-slate-100 bg-slate-50">
                <th class="text-left py-3 px-5 text-xs font-semibold text-slate-400 uppercase tracking-wide">
                    Cliente / Prospecto / Negocio
                </th>
                <th class="py-3 px-4">
                    <a href="{{ $sortUrl('tipo') }}" class="inline-flex items-center gap-1 text-xs font-semibold uppercase tracking-wide {{ $sortActual === 'tipo' ? 'text-blue-600' : 'text-slate-400 hover:text-slate-600' }} transition-colors">
                        Tipo {!! $sortIcon('tipo') !!}
                    </a>
                </th>
                <th class="text-left py-3 px-4 text-xs font-semibold text-slate-400 uppercase tracking-wide w-64">Descripción</th>
                <th class="py-3 px-4">
                    <a href="{{ $sortUrl('fecha') }}" class="inline-flex items-center gap-1 text-xs font-semibold uppercase tracking-wide {{ $sortActual === 'fecha' ? 'text-blue-600' : 'text-slate-400 hover:text-slate-600' }} transition-colors">
                        Fecha {!! $sortIcon('fecha') !!}
                    </a>
                </th>
                <th class="py-3 px-4">
                    <a href="{{ $sortUrl('proxima') }}" class="inline-flex items-center gap-1 text-xs font-semibold uppercase tracking-wide {{ $sortActual === 'proxima' ? 'text-blue-600' : 'text-slate-400 hover:text-slate-600' }} transition-colors">
                        Próxima cita {!! $sortIcon('proxima') !!}
                    </a>
                </th>
                <th class="py-3 px-4">
                    <a href="{{ $sortUrl('resultado') }}" class="inline-flex items-center gap-1 text-xs font-semibold uppercase tracking-wide {{ $sortActual === 'resultado' ? 'text-blue-600' : 'text-slate-400 hover:text-slate-600' }} transition-colors">
                        Estado {!! $sortIcon('resultado') !!}
                    </a>
                </th>
            </tr>
        </thead>
        <tbody class="divide-y divide-slate-100">
            @foreach($seguimientos as $seg)
            @php
                $rc = $resColors[$seg->resultado] ?? $resColors['pendiente'];
                $icono = $tipoIconos[$seg->tipo] ?? $tipoIconos['otro'];
            @endphp
            <tr class="hover:bg-slate-50/70 transition-colors group">

                {{-- Cliente --}}
                <td class="py-3.5 px-5">
                    @if($seg->cliente)
                        <a href="{{ route('clientes.show', $seg->cliente) }}"
                           class="font-semibold text-slate-800 hover:text-blue-600 transition-colors">
                            {{ $seg->cliente->razon_social }}
                        </a>
                    @elseif($seg->prospecto)
                        @php
                            $detalleProspecto = [
                                'tipo' => 'prospecto',
                                'prospecto' => [
                                    'empresa'  => $seg->prospecto->empresa,
                                    'codigo'   => $seg->prospecto->codigo,
                                    'contacto' => $seg->prospecto->contacto,
                                    'email'    => $seg->prospecto->email,
                                    'telefono' => $seg->prospecto->telefono,
                                    'estado'   => $seg->prospecto->estadoPipeline?->nombre,
                                    'color'    => $seg->prospecto->estadoPipeline?->color,
                                    'url'      => route('prospectos.show', $seg->prospecto->id),
                                ],
                                'negocios' => $seg->prospecto->negocios->map(fn ($n) => [
                                    'id'     => $n->id,
                                    'nombre' => $n->nombre_negocio,
                                    'estado' => $n->pipelineEstado?->nombre,
                                    'color'  => $n->pipelineEstado?->color,
                                    'valor'  => (float) $n->valor_estimado,
                                    'url'    => route('negocios.show', $n->id),
                                ])->values(),
                            ];
                        @endphp
                        <button type="button" @click="detalle = @js($detalleProspecto); open = true"
                                class="font-semibold text-slate-800 hover:text-blue-600 transition-colors">
                            {{ $seg->prospecto->empresa }}
                        </button>
                        <span class="ml-1.5 text-xs text-slate-400">(prospecto)</span>
                    @elseif($seg->negocio)
                        @php
                            $detalleNegocio = [
                                'tipo' => 'negocio',
                                'negocio' => [
                                    'nombre' => $seg->negocio->nombre_negocio,
                                    'estado' => $seg->negocio->pipelineEstado?->nombre,
                                    'color'  => $seg->negocio->pipelineEstado?->color,
                                    'valor'  => (float) $seg->negocio->valor_estimado,
                                    'url'    => route('negocios.show', $seg->negocio->id),
                                ],
                                'prospecto' => $seg->negocio->prospecto ? [
                                    'empresa' => $seg->negocio->prospecto->empresa,
                                    'estado'  => $seg->negocio->prospecto->estadoPipeline?->nombre,
                                    'color'   => $seg->negocio->prospecto->estadoPipeline?->color,
                                    'url'     => route('prospectos.show', $seg->negocio->prospecto->id),
                                ] : null,
                            ];
                        @endphp
                        <button type="button" @click="detalle = @js($detalleNegocio); open = true"
                                class="font-semibold text-slate-800 hover:text-blue-600 transition-colors">
                            {{ $seg->negocio->nombre_negocio }}
                        </button>
                        <span class="ml-1.5 text-xs text-slate-400">(negocio)</span>
                    @else
                        <span class="text-slate-400">—</span>
                    @endif
                    <p class="text-xs text-slate-400 mt-0.5">{{ $seg->asesor?->name ?? '—' }}</p>
                </td>

                {{-- Tipo --}}
                <td class="py-3.5 px-4">
                    <span class="inline-flex items-center gap-1.5 text-xs font-medium text-slate-600 bg-slate-100 rounded-full px-2.5 py-1">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.75">
                            <path stroke-linecap="round" stroke-linejoin="round" d="{{ $icono }}"/>
                        </svg>
                        {{ ucfirst($seg->tipo) }}
                    </span>
                </td>

                {{-- Descripción --}}
                <td class="py-3.5 px-4 text-slate-600 max-w-xs">
                    <p class="truncate" title="{{ $seg->descripcion }}">{{ $seg->descripcion }}</p>
                </td>

                {{-- Fecha seguimiento --}}
                <td class="py-3.5 px-4 text-slate-500 whitespace-nowrap text-xs">
                    {{ $seg->fecha_seguimiento->format('d/m/Y') }}
                    <span class="block text-slate-400">{{ $seg->fecha_seguimiento->format('H:i') }}</span>
                </td>

                {{-- Próxima fecha --}}
                <td class="py-3.5 px-4 whitespace-nowrap text-xs">
                    @if($seg->proxima_fecha)
                        <span class="{{ $seg->proxima_fecha->isPast() ? 'text-red-600 font-semibold' : 'text-slate-500' }}">
                            {{ $seg->proxima_fecha->format('d/m/Y') }}
                        </span>
                        <span class="block {{ $seg->proxima_fecha->isPast() ? 'text-red-400' : 'text-slate-400' }}">
                            {{ $seg->proxima_fecha->diffForHumans() }}
                        </span>
                    @else
                        <span class="text-slate-300">—</span>
                    @endif
                </td>

                {{-- Estado --}}
                <td class="py-3.5 px-4">
                    @can('update', $seg)
                    <form method="POST" action="{{ route('seguimientos.resultado', $seg) }}">
                        @csrf @method('PATCH')
                        <select name="resultado" onchange="this.form.submit()"
                                class="text-xs rounded-lg px-3 py-1.5 font-semibold border cursor-pointer focus:outline-none focus:ring-2 focus:ring-offset-1 focus:ring-slate-300 transition-colors bg-white"
                                style="color:{{ $rc['text'] }};border-color:{{ $rc['text'] }}55;text-align:center;text-align-last:center">
                            <option value="pendiente"      {{ $seg->resultado === 'pendiente'      ? 'selected' : '' }}>Pendiente</option>
                            <option value="exitoso"        {{ $seg->resultado === 'exitoso'        ? 'selected' : '' }}>Exitoso</option>
                            <option value="no_contactado"  {{ $seg->resultado === 'no_contactado'  ? 'selected' : '' }}>No contactado</option>
                            <option value="cancelado"      {{ $seg->resultado === 'cancelado'      ? 'selected' : '' }}>Cancelado</option>
                        </select>
                    </form>
                    @else
                    <span class="inline-block text-xs rounded-full px-3 py-1.5 font-semibold"
                          style="background:{{ $rc['bg'] }};color:{{ $rc['text'] }}">
                        {{ ucfirst(str_replace('_', ' ', $seg->resultado)) }}
                    </span>
                    @endcan
                </td>

            </tr>
            @endforeach
        </tbody>
    </table>

    @if($seguimientos->hasPages())
    <div class="px-5 py-3 border-t border-slate-100 bg-slate-50">
        {{ $seguimientos->withQueryString()->links() }}
    </div>
    @endif

    {{-- Modal detalle prospecto/negocio --}}
    <x-ui.modal size="md">
        <template x-if="detalle?.tipo === 'prospecto'">
            <div class="-mx-6 -mt-5">
                {{-- Encabezado --}}
                <div class="flex items-start justify-between gap-3 px-6 py-5 bg-gradient-to-br from-blue-50 to-white border-b border-slate-100 rounded-t-2xl">
                    <div class="flex items-start gap-3 min-w-0">
                        <div class="w-11 h-11 rounded-xl bg-blue-600 text-white flex items-center justify-center shrink-0 shadow-sm">
                            <x-ui.icon name="user-plus" class="w-5 h-5"/>
                        </div>
                        <div class="min-w-0">
                            <p class="text-[11px] font-semibold text-blue-600 uppercase tracking-widest">Prospecto</p>
                            <a :href="detalle.prospecto.url" class="block text-lg font-bold text-slate-900 hover:text-blue-600 transition-colors truncate" x-text="detalle.prospecto.empresa"></a>
                            <p class="text-xs text-slate-400 font-mono" x-text="detalle.prospecto.codigo"></p>
                        </div>
                    </div>
                    <button type="button" @click="open = false" aria-label="Cerrar"
                            class="shrink-0 text-slate-400 hover:text-slate-600 hover:bg-slate-100 rounded-lg p-1.5 transition-colors">
                        <x-ui.icon name="x" class="w-5 h-5"/>
                    </button>
                </div>

                <div class="px-6 py-5 space-y-4">
                <div class="grid grid-cols-2 gap-3 text-sm">
                    <div>
                        <p class="text-xs text-slate-500">Contacto</p>
                        <p class="text-slate-800" x-text="detalle.prospecto.contacto || '—'"></p>
                    </div>
                    <div>
                        <p class="text-xs text-slate-500">Estado</p>
                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold"
                              :style="'background:' + (detalle.prospecto.color || '#94a3b8') + '1a; color:' + (detalle.prospecto.color || '#94a3b8')"
                              x-text="detalle.prospecto.estado || '—'"></span>
                    </div>
                    <div>
                        <p class="text-xs text-slate-500">Email</p>
                        <p class="text-slate-800" x-text="detalle.prospecto.email || '—'"></p>
                    </div>
                    <div>
                        <p class="text-xs text-slate-500">Teléfono</p>
                        <p class="text-slate-800" x-text="detalle.prospecto.telefono || '—'"></p>
                    </div>
                </div>

                <div class="pt-3 border-t border-slate-100">
                    <div class="flex items-center justify-between mb-2">
                        <p class="text-xs font-semibold text-slate-500 uppercase tracking-wide">Negocios vinculados</p>
                        <span class="text-xs text-slate-400" x-text="detalle.negocios.length + ' en total'" x-show="detalle.negocios.length > 0"></span>
                    </div>
                    <template x-if="detalle.negocios.length === 0">
                        <p class="text-sm text-slate-400">Este prospecto todavía no tiene negocios creados.</p>
                    </template>
                    <div class="space-y-2 max-h-52 overflow-y-auto pr-1">
                        <template x-for="n in detalle.negocios" :key="n.id">
                            <a :href="n.url" class="block px-3 py-2 rounded-lg border border-slate-200 hover:bg-slate-50 hover:border-slate-300 transition-colors">
                                <div class="flex items-center justify-between gap-2">
                                    <span class="text-sm font-medium text-slate-900 truncate" x-text="n.nombre"></span>
                                    <span class="shrink-0 text-xs font-semibold px-2 py-0.5 rounded-full"
                                          :style="'background:' + (n.color || '#94a3b8') + '1a; color:' + (n.color || '#94a3b8')"
                                          x-text="n.estado || '—'"></span>
                                </div>
                                <p class="text-xs text-slate-400 mt-0.5" x-text="'$' + Number(n.valor).toLocaleString('es-CO')"></p>
                            </a>
                        </template>
                    </div>
                </div>
                </div>
            </div>
        </template>

        <template x-if="detalle?.tipo === 'negocio'">
            <div class="-mx-6 -mt-5">
                {{-- Encabezado --}}
                <div class="flex items-start justify-between gap-3 px-6 py-5 bg-gradient-to-br from-indigo-50 to-white border-b border-slate-100 rounded-t-2xl">
                    <div class="flex items-start gap-3 min-w-0">
                        <div class="w-11 h-11 rounded-xl bg-indigo-600 text-white flex items-center justify-center shrink-0 shadow-sm">
                            <x-ui.icon name="briefcase" class="w-5 h-5"/>
                        </div>
                        <div class="min-w-0">
                            <p class="text-[11px] font-semibold text-indigo-600 uppercase tracking-widest">Negocio</p>
                            <a :href="detalle.negocio.url" class="block text-lg font-bold text-slate-900 hover:text-indigo-600 transition-colors truncate" x-text="detalle.negocio.nombre"></a>
                        </div>
                    </div>
                    <button type="button" @click="open = false" aria-label="Cerrar"
                            class="shrink-0 text-slate-400 hover:text-slate-600 hover:bg-slate-100 rounded-lg p-1.5 transition-colors">
                        <x-ui.icon name="x" class="w-5 h-5"/>
                    </button>
                </div>

                <div class="px-6 py-5 space-y-4">
                <div class="grid grid-cols-2 gap-3 text-sm">
                    <div>
                        <p class="text-xs text-slate-500">Estado</p>
                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold"
                              :style="'background:' + (detalle.negocio.color || '#94a3b8') + '1a; color:' + (detalle.negocio.color || '#94a3b8')"
                              x-text="detalle.negocio.estado || '—'"></span>
                    </div>
                    <div>
                        <p class="text-xs text-slate-500">Valor estimado</p>
                        <p class="text-slate-800 font-semibold" x-text="'$' + Number(detalle.negocio.valor).toLocaleString('es-CO')"></p>
                    </div>
                </div>

                <div class="pt-3 border-t border-slate-100">
                    <p class="text-xs font-semibold text-slate-500 uppercase tracking-wide mb-2">Prospecto de origen</p>
                    <template x-if="!detalle.prospecto">
                        <p class="text-sm text-slate-400">Este negocio no viene de un prospecto (se creó directo con un cliente).</p>
                    </template>
                    <template x-if="detalle.prospecto">
                        <a :href="detalle.prospecto.url" class="block px-3 py-2 rounded-lg border border-slate-200 hover:bg-slate-50 hover:border-slate-300 transition-colors">
                            <div class="flex items-center justify-between gap-2">
                                <span class="text-sm font-medium text-slate-900 truncate" x-text="detalle.prospecto.empresa"></span>
                                <span class="shrink-0 text-xs font-semibold px-2 py-0.5 rounded-full"
                                      :style="'background:' + (detalle.prospecto.color || '#94a3b8') + '1a; color:' + (detalle.prospecto.color || '#94a3b8')"
                                      x-text="detalle.prospecto.estado || '—'"></span>
                            </div>
                        </a>
                    </template>
                </div>
                </div>
            </div>
        </template>
    </x-ui.modal>
</div>
@endif

</x-layouts.app>
