<x-layouts.app title="Pipeline" :hide-page-title="true">

<style>
.kanban-board::-webkit-scrollbar        { height: 10px; }
.kanban-board::-webkit-scrollbar-track  { background: #e2e8f0; border-radius: 9999px; }
.kanban-board::-webkit-scrollbar-thumb  { background: #64748b; border-radius: 9999px; }
.kanban-board::-webkit-scrollbar-thumb:hover { background: #334155; }
.kanban-board { scrollbar-width: thin; scrollbar-color: #64748b #e2e8f0; }
#kanban-prev, #kanban-next {
    transition: opacity .15s, box-shadow .15s;
}
/* Evita que el arrastre de tarjetas seleccione texto de la página */
body.sorting, body.sorting * {
    user-select: none !important;
    -webkit-user-select: none !important;
    -moz-user-select: none !important;
}
</style>

    <div x-data="kanban()" x-init="init()">

    {{-- Encabezado --}}
    <div class="flex items-center gap-4 mb-5">
        <div>
            <p class="text-xs font-semibold uppercase tracking-widest text-slate-400 mb-0.5">Comercial</p>
            <h1 class="text-2xl font-bold text-slate-900">Pipeline Kanban</h1>
        </div>
        <div class="flex items-center gap-3 mt-4">
        @unlessrole('gerente')
        <a href="{{ route('negocios.create') }}"
           class="inline-flex items-center gap-1.5 px-4 py-2.5 text-sm font-semibold rounded-xl text-white shadow-sm transition-opacity hover:opacity-90"
           style="background:#0f766e">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
            </svg>
            Nuevo negocio
        </a>
        @endunlessrole
        <a href="{{ route('negocios.index') }}"
           class="inline-flex items-center gap-1.5 px-4 py-2.5 text-sm font-medium rounded-xl border border-slate-200 bg-white text-slate-600 hover:bg-slate-50 transition-colors shadow-sm">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.75">
                <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 10h16M4 14h16M4 18h16"/>
            </svg>
            Ver lista
        </a>
        </div>
    </div>

    <div class="relative">

    {{-- Flecha izquierda --}}
    <button id="kanban-prev"
            style="display:none; position:absolute; left:0; top:120px; z-index:30; width:36px; height:36px; border-radius:50%; background:#fff; box-shadow:0 2px 8px rgba(0,0,0,.15); border:1px solid #e2e8f0; align-items:center; justify-content:center; color:#475569; cursor:pointer;"
            onclick="document.getElementById('kanban-board').scrollBy({left:-320,behavior:'smooth'})">
        <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5">
            <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/>
        </svg>
    </button>

    {{-- Flecha derecha --}}
    <button id="kanban-next"
            style="display:none; position:absolute; right:0; top:120px; z-index:30; width:36px; height:36px; border-radius:50%; background:#fff; box-shadow:0 2px 8px rgba(0,0,0,.15); border:1px solid #e2e8f0; align-items:center; justify-content:center; color:#475569; cursor:pointer;"
            onclick="document.getElementById('kanban-board').scrollBy({left:320,behavior:'smooth'})">
        <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5">
            <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/>
        </svg>
    </button>

    <div
        id="kanban-board"
        class="kanban-board flex gap-4 overflow-x-auto pb-6"
        style="min-height: calc(100vh - 9rem)"
    >
        @foreach($columnas as $columna)
        @php $color = $columna['estado']->color; @endphp
        <div class="flex-none w-72 flex flex-col">

            {{-- Header columna --}}
            <div class="rounded-xl overflow-hidden mb-3 bg-white border border-slate-200 shadow-sm">
                <div class="h-1.5 w-full" style="background-color: {{ $color }}"></div>
                <div class="p-3">
                    <div class="flex items-center justify-between mb-2">
                        <h3 class="text-sm font-bold text-slate-800">{{ $columna['estado']->nombre }}</h3>
                        <div class="flex items-center gap-1.5">
                            <span class="text-xs font-semibold px-2 py-0.5 rounded-full text-white"
                                  style="background-color: {{ $color }}">
                                {{ count($columna['negocios']) }}
                            </span>
                            @unlessrole('gerente')
                            <a href="{{ route('negocios.create') }}?pipeline_estado_id={{ $columna['estado']->id }}"
                               class="w-6 h-6 rounded-full flex items-center justify-center text-slate-400 hover:text-white hover:bg-blue-500 transition-colors"
                               title="Agregar negocio en esta etapa">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/>
                                </svg>
                            </a>
                            @endunlessrole
                        </div>
                    </div>
                    <p class="text-base font-bold text-slate-900">
                        ${{ number_format($columna['valor_total'], 0, ',', '.') }}
                    </p>
                    <p class="text-xs text-slate-400 mt-0.5">
                        Forecast ${{ number_format($columna['valor_forecast'], 0, ',', '.') }}
                    </p>
                </div>
            </div>

            {{-- Cards --}}
            <div
                class="flex-1 space-y-2 rounded-xl bg-slate-100/70 p-2 min-h-24"
                x-sort:group="negocios"
                x-sort="mover($item, $position, '{{ $columna['estado']->id }}')"
                data-estado-id="{{ $columna['estado']->id }}"
            >
                @forelse($columna['negocios'] as $negocio)
                @php
                    $vencido = $negocio->fecha_estimada_cierre && $negocio->fecha_estimada_cierre->isPast();
                    $diasRestantes = $negocio->fecha_estimada_cierre
                        ? (int) now()->diffInDays($negocio->fecha_estimada_cierre, false)
                        : null;
                    $panelData = [
                        'id'              => $negocio->id,
                        'nombre'          => $negocio->nombre_negocio,
                        'descripcion'     => $negocio->descripcion,
                        'valor'           => (float) $negocio->valor_estimado,
                        'forecast'        => $negocio->valorForecast(),
                        'probabilidad'    => $negocio->probabilidadEfectiva(),
                        'fechaCierre'     => optional($negocio->fecha_estimada_cierre)->format('Y-m-d'),
                        'vencido'         => $vencido,
                        'companiaNombre'  => $negocio->companiaNombre(),
                        'companiaSiglas'  => $negocio->companiaSiglas(),
                        'cliente'         => $negocio->cliente?->razon_social,
                        'clienteEmail'    => $negocio->cliente?->email,
                        'clienteTelefono' => $negocio->cliente?->telefono,
                        'prospecto'       => $negocio->prospecto?->empresa,
                        'prospectoContacto' => $negocio->prospecto?->contacto,
                        'prospectoEmail'  => $negocio->prospecto?->email,
                        'prospectoTelefono' => $negocio->prospecto?->telefono,
                        'asesor'          => $negocio->asesor?->name,
                        'estadoNombre'    => $columna['estado']->nombre,
                        'estadoColor'     => $color,
                        'url'             => route('negocios.show', $negocio),
                    ];
                @endphp
                <div
                    x-sort:item="{{ $negocio->id }}"
                    @click="abrirPanel({{ Js::from($panelData) }})"
                    class="relative bg-white rounded-xl p-3 shadow-sm border border-slate-200 cursor-pointer hover:shadow-md transition-all"
                    style="border-left: 3px solid {{ $color }}"
                >
                    {{-- Handle de arrastre --}}
                    <div x-sort:handle
                         @click.stop
                         title="Arrastrar para mover de etapa"
                         class="absolute top-2 right-2 w-6 h-6 rounded-md flex items-center justify-center text-slate-300 hover:text-slate-500 hover:bg-slate-100 cursor-grab active:cursor-grabbing">
                        <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M7 4a1 1 0 110 2 1 1 0 010-2zm6 0a1 1 0 110 2 1 1 0 010-2zM7 9a1 1 0 110 2 1 1 0 010-2zm6 0a1 1 0 110 2 1 1 0 010-2zm-6 5a1 1 0 110 2 1 1 0 010-2zm6 0a1 1 0 110 2 1 1 0 010-2z"/>
                        </svg>
                    </div>

                    {{-- Nombre --}}
                    <div class="mb-1.5 flex items-start justify-between gap-1.5 pr-6">
                        <p class="text-sm font-semibold text-slate-900 leading-tight line-clamp-2">
                            {{ $negocio->nombre_negocio }}
                        </p>
                        @if($negocio->compania)
                            <span class="shrink-0 text-[10px] font-bold px-1.5 py-0.5 rounded {{ $negocio->compania === 1 ? 'bg-red-100 text-red-700' : 'bg-blue-100 text-blue-700' }}"
                                  title="{{ $negocio->companiaNombre() }}">
                                {{ $negocio->companiaSiglas() }}
                            </span>
                        @endif
                    </div>

                    {{-- Cliente --}}
                    <p class="text-xs text-slate-500 mb-2.5 truncate flex items-center gap-1">
                        <svg class="w-3 h-3 shrink-0 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-2 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                        </svg>
                        {{ $negocio->cliente?->razon_social ?? $negocio->prospecto?->empresa ?? '—' }}
                    </p>

                    {{-- Valor + fecha + asesor --}}
                    <div class="flex items-center justify-between">
                        <span class="text-sm font-bold text-slate-900">
                            ${{ number_format($negocio->valor_estimado, 0, ',', '.') }}
                        </span>
                        <div class="flex items-center gap-1.5">
                            @if($negocio->fecha_estimada_cierre)
                                <span class="text-xs px-1.5 py-0.5 rounded font-medium
                                    {{ $vencido
                                        ? 'bg-red-50 text-red-600'
                                        : ($diasRestantes !== null && $diasRestantes <= 7
                                            ? 'bg-amber-50 text-amber-600'
                                            : 'text-slate-400') }}">
                                    @if($vencido) ⚠ @endif{{ $negocio->fecha_estimada_cierre->format('d/m') }}
                                </span>
                            @endif
                            <div class="w-6 h-6 rounded-full flex items-center justify-center text-xs font-bold text-white shrink-0"
                                 style="background-color: {{ $color }}"
                                 title="{{ $negocio->asesor?->name }}">
                                {{ strtoupper(substr($negocio->asesor?->name ?? 'U', 0, 1)) }}
                            </div>
                        </div>
                    </div>

                    {{-- Barra de probabilidad --}}
                    <div class="mt-2.5">
                        <div class="flex items-center justify-between mb-1">
                            <span class="text-xs text-slate-400">Probabilidad</span>
                            <span class="text-xs font-semibold text-slate-600">{{ $negocio->probabilidadEfectiva() }}%</span>
                        </div>
                        <div class="h-1.5 bg-slate-100 rounded-full overflow-hidden">
                            <div class="h-full rounded-full"
                                 style="width: {{ $negocio->probabilidadEfectiva() }}%; background-color: {{ $color }}">
                            </div>
                        </div>
                    </div>
                </div>
                @empty
                @unlessrole('gerente')
                <a href="{{ route('negocios.create') }}?pipeline_estado_id={{ $columna['estado']->id }}"
                   class="flex flex-col items-center justify-center h-20 text-xs text-slate-400 gap-1.5 rounded-lg border-2 border-dashed border-slate-200 hover:border-blue-300 hover:text-blue-400 transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 4v16m8-8H4"/>
                    </svg>
                    Agregar negocio
                </a>
                @endunlessrole
                @endforelse
            </div>
        </div>
        @endforeach
    </div>
    </div>{{-- /kanban-wrapper --}}

    {{-- Panel de detalle (deslizante derecho) --}}
    <div
        x-show="panelAbierto"
        x-transition:enter="transition ease-out duration-220"
        x-transition:enter-start="opacity-0 translate-x-8"
        x-transition:enter-end="opacity-100 translate-x-0"
        x-transition:leave="transition ease-in duration-160"
        x-transition:leave-start="opacity-100 translate-x-0"
        x-transition:leave-end="opacity-0 translate-x-8"
        @keydown.escape.window="cerrarPanel()"
        class="fixed right-0 top-12 bottom-0 z-40 w-96 bg-white border-l border-slate-100 shadow-2xl flex flex-col"
        x-cloak>

        {{-- Barra de color arriba --}}
        <div class="h-1.5 w-full flex-shrink-0"
             :style="'background: linear-gradient(90deg,' + (negocioActivo?.estadoColor ?? '#94a3b8') + ',' + (negocioActivo?.estadoColor ?? '#94a3b8') + '99)'">
        </div>

        {{-- Cabecera --}}
        <div class="px-6 py-5 border-b border-slate-100 flex items-start justify-between gap-4">
            <div class="flex items-center gap-4 min-w-0">
                <div class="w-12 h-12 rounded-2xl flex items-center justify-center flex-shrink-0"
                     :style="'background:' + (negocioActivo?.estadoColor ?? '#94a3b8') + '18;border:2px solid ' + (negocioActivo?.estadoColor ?? '#94a3b8') + '30'">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                         :style="'color:' + (negocioActivo?.estadoColor ?? '#94a3b8')">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m-2 0h12a2 2 0 012 2v10a2 2 0 01-2 2H6a2 2 0 01-2-2V8a2 2 0 012-2z"/>
                    </svg>
                </div>
                <div class="min-w-0">
                    <p class="text-xs font-bold uppercase tracking-widest mb-0.5"
                       :style="'color:' + (negocioActivo?.estadoColor ?? '#94a3b8')"
                       x-text="negocioActivo?.estadoNombre ?? ''"></p>
                    <p class="text-base font-bold text-slate-900 leading-tight line-clamp-2" x-text="negocioActivo?.nombre ?? ''"></p>
                </div>
            </div>
            <button @click="cerrarPanel()"
                    class="text-slate-400 hover:text-slate-700 hover:bg-slate-100 p-2 rounded-xl transition-colors flex-shrink-0 mt-0.5">
                <svg class="w-4.5 h-4.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>

        {{-- Cuerpo --}}
        <div class="flex-1 overflow-y-auto px-6 py-5 space-y-5">

            {{-- Valor + forecast + probabilidad --}}
            <div class="bg-slate-50 border border-slate-100 rounded-2xl px-4 py-4">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs text-slate-400 font-medium">Valor estimado</p>
                        <p class="text-lg font-bold text-slate-900 mt-0.5" x-text="'$' + Number(negocioActivo?.valor ?? 0).toLocaleString('es-CO')"></p>
                    </div>
                    <div class="text-right">
                        <p class="text-xs text-slate-400 font-medium">Forecast</p>
                        <p class="text-sm font-semibold text-slate-600 mt-0.5" x-text="'$' + Number(negocioActivo?.forecast ?? 0).toLocaleString('es-CO')"></p>
                    </div>
                </div>
                <div class="mt-3">
                    <div class="flex items-center justify-between mb-1">
                        <span class="text-xs text-slate-400">Probabilidad</span>
                        <span class="text-xs font-semibold text-slate-600" x-text="(negocioActivo?.probabilidad ?? 0) + '%'"></span>
                    </div>
                    <div class="h-1.5 bg-slate-200 rounded-full overflow-hidden">
                        <div class="h-full rounded-full"
                             :style="'width:' + (negocioActivo?.probabilidad ?? 0) + '%; background-color:' + (negocioActivo?.estadoColor ?? '#94a3b8')">
                        </div>
                    </div>
                </div>
            </div>

            {{-- Fecha estimada de cierre --}}
            <div class="bg-slate-50 border border-slate-100 rounded-2xl px-4 py-4">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-white border border-slate-200 flex items-center justify-center flex-shrink-0 shadow-sm">
                        <svg class="w-5 h-5 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                        </svg>
                    </div>
                    <div>
                        <p class="text-xs text-slate-400 font-medium">Fecha estimada de cierre</p>
                        <p class="text-sm font-bold mt-0.5"
                           :class="negocioActivo?.vencido ? 'text-red-600' : 'text-slate-900'"
                           x-text="fmtFechaCierre(negocioActivo?.fechaCierre) + (negocioActivo?.vencido ? ' · vencido' : '')"></p>
                    </div>
                </div>
            </div>

            {{-- Cliente / Prospecto --}}
            <div class="bg-slate-50 border border-slate-100 rounded-2xl px-4 py-4">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-white border border-slate-200 flex items-center justify-center flex-shrink-0 shadow-sm">
                        <svg class="w-5 h-5 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-2 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                        </svg>
                    </div>
                    <div class="min-w-0">
                        <p class="text-xs text-slate-400 font-medium" x-text="negocioActivo?.cliente ? 'Cliente' : 'Prospecto'"></p>
                        <p class="text-sm font-bold text-slate-900 mt-0.5 truncate" x-text="negocioActivo?.cliente ?? negocioActivo?.prospecto ?? '—'"></p>
                    </div>
                </div>
                <template x-if="negocioActivo?.prospectoContacto">
                    <p class="text-xs text-slate-500 mt-2.5 pl-[52px]" x-text="'Contacto: ' + negocioActivo.prospectoContacto"></p>
                </template>
                <template x-if="(negocioActivo?.clienteTelefono || negocioActivo?.prospectoTelefono)">
                    <p class="text-xs text-slate-500 mt-1.5 flex items-center gap-1.5">
                        <svg class="w-3.5 h-3.5 text-slate-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                        </svg>
                        <span x-text="negocioActivo?.clienteTelefono ?? negocioActivo?.prospectoTelefono"></span>
                    </p>
                </template>
                <template x-if="(negocioActivo?.clienteEmail || negocioActivo?.prospectoEmail)">
                    <p class="text-xs text-slate-500 mt-1.5 flex items-center gap-1.5">
                        <svg class="w-3.5 h-3.5 text-slate-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                        </svg>
                        <span class="truncate" x-text="negocioActivo?.clienteEmail ?? negocioActivo?.prospectoEmail"></span>
                    </p>
                </template>
            </div>

            {{-- Asesor + compañía --}}
            <div class="bg-slate-50 border border-slate-100 rounded-2xl px-4 py-4">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-white border border-slate-200 flex items-center justify-center flex-shrink-0 shadow-sm">
                        <svg class="w-5 h-5 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                        </svg>
                    </div>
                    <div>
                        <p class="text-xs text-slate-400 font-medium">Asesor responsable</p>
                        <p class="text-sm font-bold text-slate-900 mt-0.5" x-text="negocioActivo?.asesor || 'Sin asesor asignado'"></p>
                    </div>
                </div>
                <template x-if="negocioActivo?.companiaNombre">
                    <p class="text-xs text-slate-500 mt-2.5" x-text="'Compañía: ' + negocioActivo.companiaNombre"></p>
                </template>
            </div>

            {{-- Descripción --}}
            <template x-if="negocioActivo?.descripcion">
                <div>
                    <p class="text-xs font-bold text-slate-400 uppercase tracking-widest mb-2.5">Descripción</p>
                    <div class="bg-slate-50 border border-slate-100 rounded-2xl px-4 py-4">
                        <p class="text-sm text-slate-700 leading-relaxed" x-text="negocioActivo.descripcion"></p>
                    </div>
                </div>
            </template>
        </div>

        {{-- Botones de acción --}}
        <div class="px-6 py-5 border-t border-slate-100">
            <a :href="negocioActivo?.url ?? '#'"
               class="flex items-center justify-center gap-2.5 w-full bg-blue-600 hover:bg-blue-700 active:bg-blue-800 text-white text-sm font-bold px-5 py-3 rounded-xl transition-colors shadow-sm shadow-blue-200">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
                          d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
                </svg>
                Ver ficha completa
            </a>
        </div>
    </div>

    {{-- Overlay oscuro detrás del panel en móvil --}}
    <div x-show="panelAbierto"
         @click="cerrarPanel()"
         class="fixed inset-0 z-30 bg-slate-900/20 backdrop-blur-sm lg:hidden"
         x-cloak></div>

    {{-- Modal motivo de pérdida --}}
    <div x-data x-show="$store.kanban.modalPerdido" x-cloak
         class="fixed inset-0 z-50 flex items-center justify-center p-4"
         x-transition:enter="transition ease-out duration-150"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100">
        <div class="absolute inset-0 bg-black/40 backdrop-blur-sm" @click="$store.kanban.cancelar()"></div>
        <div class="relative bg-white rounded-2xl shadow-xl w-full max-w-md p-6"
             x-transition:enter="transition ease-out duration-150"
             x-transition:enter-start="opacity-0 scale-95"
             x-transition:enter-end="opacity-100 scale-100">
            <div class="flex items-center gap-3 mb-4">
                <div class="w-9 h-9 rounded-full bg-red-100 flex items-center justify-center shrink-0">
                    <svg class="w-5 h-5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </div>
                <div>
                    <h3 class="text-base font-bold text-slate-900">Negocio perdido</h3>
                    <p class="text-xs text-slate-500">Indica el motivo para registrar el cierre.</p>
                </div>
            </div>

            <label class="block text-sm font-medium text-slate-700 mb-1.5">Motivo de pérdida <span class="text-red-500">*</span></label>
            <select x-model="$store.kanban.motivo"
                    class="w-full px-3 py-2 text-sm rounded-lg border border-slate-300 focus:outline-none focus:ring-2 focus:ring-red-400 bg-white">
                <option value="">Selecciona un motivo...</option>
                @foreach($motivos as $m)
                    <option value="{{ $m->id }}">{{ $m->nombre }}</option>
                @endforeach
            </select>

            <div class="mt-4">
                <label class="block text-sm font-medium text-slate-700 mb-1.5">Observación <span class="text-xs text-slate-400 font-normal">(opcional)</span></label>
                <textarea x-model="$store.kanban.observacion"
                          rows="3"
                          placeholder="Describe brevemente por qué se perdió este negocio..."
                          class="w-full px-3 py-2 text-sm rounded-lg border border-slate-300 focus:outline-none focus:ring-2 focus:ring-red-400 resize-none"></textarea>
            </div>

            <div class="flex justify-end gap-2 mt-4">
                <button @click="$store.kanban.cancelar()"
                        class="px-4 py-2 text-sm text-slate-600 border border-slate-200 rounded-lg hover:bg-slate-50 transition-colors">
                    Cancelar
                </button>
                <button @click="$store.kanban.confirmar()"
                        :disabled="!$store.kanban.motivo.trim()"
                        class="px-4 py-2 text-sm font-semibold text-white bg-red-600 rounded-lg hover:bg-red-700 disabled:opacity-40 disabled:cursor-not-allowed transition-colors">
                    Confirmar pérdida
                </button>
            </div>
        </div>
    </div>
    </div>{{-- /x-data="kanban()" --}}

    @push('scripts')
    <script>
    window.addEventListener('load', function () {
        const board = document.getElementById('kanban-board');
        const prev  = document.getElementById('kanban-prev');
        const next  = document.getElementById('kanban-next');
        if (!board || !prev || !next) return;

        function updateArrows() {
            const atStart = board.scrollLeft <= 4;
            const atEnd   = board.scrollLeft + board.clientWidth >= board.scrollWidth - 4;
            prev.style.display = atStart ? 'none' : 'flex';
            next.style.display = atEnd   ? 'none' : 'flex';
        }

        board.addEventListener('scroll', updateArrows, { passive: true });
        updateArrows();
    });

    document.addEventListener('alpine:init', () => {
        Alpine.store('kanban', {
            modalPerdido: false,
            motivo: '',
            observacion: '',
            pending: null,
            api: null,
            toast: null,

            abrir(itemId, estadoId, apiFn, toastStore) {
                this.pending     = { itemId, estadoId };
                this.motivo      = '';
                this.observacion = '';
                this.api         = apiFn;
                this.toast       = toastStore;
                this.modalPerdido = true;
            },

            cancelar() {
                this.modalPerdido = false;
                this.pending = null;
                window.location.reload();
            },

            async confirmar() {
                if (!this.motivo.trim() || !this.pending) return;
                const { itemId, estadoId } = this.pending;
                this.modalPerdido = false;
                await kanbanMover(this.api, this.toast, itemId, estadoId, this.motivo.trim(), this.observacion.trim() || null);
                this.pending = null;
            },
        });
    });

    async function kanbanMover(apiFn, toastStore, itemId, estadoId, motivo = null, observacion = null) {
        try {
            const payload = { pipeline_estado_id: parseInt(estadoId) };
            if (motivo) payload.motivo_perdida_id = parseInt(motivo);
            if (observacion) payload.observacion_perdida = observacion;
            const res = await apiFn('PUT', `/api/negocios/${itemId}`, payload);
            if (!res.success) {
                toastStore.error('Error al mover el negocio: ' + res.message);
            } else {
                toastStore.success('Negocio actualizado.');
            }
        } catch {
            toastStore.error('Error de conexión.');
        }
    }

    function kanban() {
        return {
            estadosPerdidos: [{{ collect($columnas)->filter(fn($c) => $c['estado']->es_perdido)->pluck('estado.id')->join(',') }}],
            panelAbierto: false,
            negocioActivo: null,

            init() {},

            async mover(itemId, position, estadoId) {
                if (this.estadosPerdidos.includes(parseInt(estadoId))) {
                    Alpine.store('kanban').abrir(itemId, estadoId, this.$api.bind(this), this.$store.toast);
                    return;
                }
                await kanbanMover(this.$api.bind(this), this.$store.toast, itemId, estadoId);
            },

            abrirPanel(negocio) {
                this.negocioActivo = negocio;
                this.panelAbierto = true;
            },

            cerrarPanel() {
                this.panelAbierto = false;
                this.negocioActivo = null;
            },

            fmtFechaCierre(iso) {
                if (!iso) return 'Sin fecha definida';
                return new Date(iso + 'T12:00:00').toLocaleDateString('es-CO', { day: 'numeric', month: 'long', year: 'numeric' });
            },
        };
    }
    </script>
    @endpush
</x-layouts.app>
