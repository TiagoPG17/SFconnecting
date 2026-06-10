<x-layouts.app title="Dashboard Comercial">
    <x-slot name="actions">
        <span class="inline-flex items-center gap-1.5 text-xs text-slate-400">
            <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 animate-pulse"></span>
            Actualizado hace un momento
        </span>
    </x-slot>

    {{-- ══════════════════════════════════════════════
         KPIs PRINCIPALES
    ══════════════════════════════════════════════ --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-8">

        <div class="relative overflow-hidden rounded-2xl bg-gradient-to-br from-blue-500 to-blue-700 p-5 shadow-lg shadow-blue-200/60">
            <div class="pointer-events-none absolute -right-5 -top-5 h-28 w-28 rounded-full bg-white/10"></div>
            <div class="pointer-events-none absolute -bottom-8 -right-3 h-20 w-20 rounded-full bg-white/5"></div>
            <div class="relative">
                <div class="mb-3 flex h-9 w-9 items-center justify-center rounded-xl bg-white/25 backdrop-blur-sm">
                    <svg class="h-4.5 w-4.5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/>
                    </svg>
                </div>
                <p class="text-2xl font-extrabold text-white tracking-tight">${{ number_format($kpis['forecast']['total_pipeline'], 0, ',', '.') }}</p>
                <p class="mt-1 text-xs font-semibold uppercase tracking-widest text-blue-100">Total Pipeline</p>
            </div>
        </div>

        <div class="relative overflow-hidden rounded-2xl bg-gradient-to-br from-emerald-500 to-emerald-700 p-5 shadow-lg shadow-emerald-200/60">
            <div class="pointer-events-none absolute -right-5 -top-5 h-28 w-28 rounded-full bg-white/10"></div>
            <div class="pointer-events-none absolute -bottom-8 -right-3 h-20 w-20 rounded-full bg-white/5"></div>
            <div class="relative">
                <div class="mb-3 flex h-9 w-9 items-center justify-center rounded-xl bg-white/25">
                    <svg class="h-4.5 w-4.5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                    </svg>
                </div>
                <p class="text-2xl font-extrabold text-white tracking-tight">${{ number_format($kpis['forecast']['total_forecast'], 0, ',', '.') }}</p>
                <p class="mt-1 text-xs font-semibold uppercase tracking-widest text-emerald-100">Forecast Ponderado</p>
            </div>
        </div>

        <div class="relative overflow-hidden rounded-2xl bg-gradient-to-br from-orange-500 to-orange-600 p-5 shadow-lg shadow-orange-200/60">
            <div class="pointer-events-none absolute -right-5 -top-5 h-28 w-28 rounded-full bg-white/10"></div>
            <div class="pointer-events-none absolute -bottom-8 -right-3 h-20 w-20 rounded-full bg-white/5"></div>
            <div class="relative">
                <div class="mb-3 flex h-9 w-9 items-center justify-center rounded-xl bg-white/25">
                    <svg class="h-4.5 w-4.5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                    </svg>
                </div>
                <p class="text-2xl font-extrabold text-white tracking-tight">{{ $kpis['pipeline']['negocios']['abiertos'] }}</p>
                <p class="mt-1 text-xs font-semibold uppercase tracking-widest text-orange-100">Negocios Abiertos</p>
            </div>
        </div>

        <div class="relative overflow-hidden rounded-2xl bg-gradient-to-br from-violet-500 to-violet-700 p-5 shadow-lg shadow-violet-200/60">
            <div class="pointer-events-none absolute -right-5 -top-5 h-28 w-28 rounded-full bg-white/10"></div>
            <div class="pointer-events-none absolute -bottom-8 -right-3 h-20 w-20 rounded-full bg-white/5"></div>
            <div class="relative">
                <div class="mb-3 flex h-9 w-9 items-center justify-center rounded-xl bg-white/25">
                    <svg class="h-4.5 w-4.5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <p class="text-2xl font-extrabold text-white tracking-tight">{{ $kpis['conversion']['tasa'] }}%</p>
                <p class="mt-1 text-xs font-semibold uppercase tracking-widest text-violet-100">Tasa Conversión</p>
            </div>
        </div>

    </div>

    {{-- ══════════════════════════════════════════════
         SECCIÓN: PIPELINE COMERCIAL
    ══════════════════════════════════════════════ --}}
    <div class="flex items-center gap-3 mb-5">
        <div class="flex h-7 w-7 shrink-0 items-center justify-center rounded-lg bg-blue-600 shadow-sm shadow-blue-300">
            <svg class="h-3.5 w-3.5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M3 4h13M3 8h9m-9 4h9m5-4v12m0 0l-4-4m4 4l4-4"/>
            </svg>
        </div>
        <h2 class="text-sm font-bold text-slate-800 uppercase tracking-wider">Pipeline Comercial</h2>
        <div class="flex-1 h-px bg-gradient-to-r from-blue-200 via-blue-100 to-transparent"></div>
    </div>

    {{-- Fila 1: Estado pipeline + Conversión --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-4 mb-4">

        <x-ui.card class="p-5 lg:col-span-2">
            <h3 class="text-sm font-semibold text-slate-700 mb-5">Estado del Pipeline</h3>

            {{-- Funnel steps --}}
            <div class="grid grid-cols-4 gap-2 mb-6">
                @foreach([
                    ['label' => 'Prospectos',  'value' => $kpis['pipeline']['prospectos']['total'],       'from' => 'from-sky-400',    'to' => 'to-sky-600',    'shadow' => 'shadow-sky-200'],
                    ['label' => 'Convertidos', 'value' => $kpis['pipeline']['prospectos']['convertidos'],  'from' => 'from-blue-400',   'to' => 'to-blue-600',   'shadow' => 'shadow-blue-200'],
                    ['label' => 'Ganados',     'value' => $kpis['pipeline']['negocios']['ganados'],        'from' => 'from-emerald-400','to' => 'to-emerald-600','shadow' => 'shadow-emerald-200'],
                    ['label' => 'Perdidos',    'value' => $kpis['pipeline']['negocios']['perdidos'],       'from' => 'from-rose-400',   'to' => 'to-rose-600',   'shadow' => 'shadow-rose-200'],
                ] as $s)
                <div class="flex flex-col items-center">
                    <div class="relative flex items-center justify-center w-full rounded-xl bg-gradient-to-b {{ $s['from'] }} {{ $s['to'] }} shadow-md {{ $s['shadow'] }} py-4">
                        <p class="text-xl font-extrabold text-white">{{ $s['value'] }}</p>
                    </div>
                    <p class="mt-2 text-xs font-medium text-slate-500">{{ $s['label'] }}</p>
                </div>
                @endforeach
            </div>

            {{-- Mini stats --}}
            <div class="grid grid-cols-3 gap-3 pt-4 border-t border-slate-100">
                <div class="rounded-xl bg-slate-50 p-3 text-center">
                    <p class="text-lg font-bold text-slate-800">{{ $kpis['seguimientos']['ultimo_mes'] }}</p>
                    <p class="text-xs text-slate-500 mt-0.5">Seguimientos / mes</p>
                </div>
                <div class="rounded-xl bg-slate-50 p-3 text-center">
                    <p class="text-lg font-bold text-blue-600">{{ $kpis['seguimientos']['proximos_7_dias'] }}</p>
                    <p class="text-xs text-slate-500 mt-0.5">Próximos 7 días</p>
                </div>
                <div class="rounded-xl bg-amber-50 p-3 text-center">
                    <p class="text-lg font-bold text-amber-600">{{ $kpis['alertas']['clientes_sin_seguimiento_reciente'] }}</p>
                    <p class="text-xs text-slate-500 mt-0.5">Sin contacto reciente</p>
                </div>
            </div>
        </x-ui.card>

        {{-- Conversión --}}
        <x-ui.card class="p-5">
            <h3 class="text-sm font-semibold text-slate-700 mb-4">Conversión Lead → Cliente</h3>

            {{-- Donut visual --}}
            <div class="flex flex-col items-center py-2 mb-4">
                <div class="relative w-28 h-28">
                    <svg class="w-28 h-28 -rotate-90" viewBox="0 0 100 100">
                        <circle cx="50" cy="50" r="40" fill="none" stroke="#f1f5f9" stroke-width="12"/>
                        <circle cx="50" cy="50" r="40" fill="none" stroke="#10b981" stroke-width="12"
                            stroke-dasharray="{{ round($kpis['conversion']['tasa'] * 2.513, 1) }} 251.3"
                            stroke-linecap="round"
                            class="transition-all duration-700"/>
                    </svg>
                    <div class="absolute inset-0 flex flex-col items-center justify-center">
                        <span class="text-2xl font-extrabold text-emerald-600">{{ $kpis['conversion']['tasa'] }}%</span>
                    </div>
                </div>
            </div>

            <div class="space-y-2.5 pt-3 border-t border-slate-100">
                <div class="flex justify-between items-center">
                    <span class="text-xs text-slate-500">Total prospectos</span>
                    <span class="text-xs font-bold text-slate-800 bg-slate-100 px-2 py-0.5 rounded-md">{{ $kpis['conversion']['total_prospectos'] }}</span>
                </div>
                <div class="flex justify-between items-center">
                    <span class="text-xs text-slate-500">Convertidos</span>
                    <span class="text-xs font-bold text-emerald-700 bg-emerald-50 px-2 py-0.5 rounded-md">{{ $kpis['conversion']['convertidos'] }}</span>
                </div>
                <div class="flex justify-between items-center">
                    <span class="text-xs text-slate-500">Negocios ganados</span>
                    <span class="text-xs font-bold text-emerald-700 bg-emerald-50 px-2 py-0.5 rounded-md">{{ $kpis['conversion']['negocios_ganados'] }}</span>
                </div>
            </div>
        </x-ui.card>

    </div>

    {{-- Fila 2: Próximos cierres + Top Asesores --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 mb-4">

        <x-ui.card class="p-5">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-sm font-semibold text-slate-700">Próximos cierres <span class="text-slate-400 font-normal text-xs">30 días</span></h3>
                <x-ui.button href="{{ route('negocios.index') }}" variant="ghost" size="xs">Ver todos →</x-ui.button>
            </div>
            @forelse($kpis['proximos_cierres'] as $negocio)
            @php
                $prob = $negocio['probabilidad_efectiva'];
                $probColor = $prob >= 70 ? ['bar'=>'bg-emerald-500','badge'=>'bg-emerald-100 text-emerald-700'] : ($prob >= 40 ? ['bar'=>'bg-amber-400','badge'=>'bg-amber-100 text-amber-700'] : ['bar'=>'bg-rose-400','badge'=>'bg-rose-100 text-rose-700']);
            @endphp
            <div class="py-3 {{ !$loop->last ? 'border-b border-slate-100' : '' }}">
                <div class="flex items-start gap-3">
                    <div class="mt-1 w-1 self-stretch rounded-full {{ $probColor['bar'] }} shrink-0"></div>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-semibold text-slate-900 truncate">{{ $negocio['nombre_negocio'] }}</p>
                        <p class="text-xs text-slate-400 mt-0.5 truncate">{{ $negocio['cliente'] }} · {{ $negocio['asesor'] }}</p>
                    </div>
                    <div class="text-right shrink-0 space-y-1">
                        <p class="text-sm font-bold text-slate-800">${{ number_format($negocio['valor_estimado'], 0, ',', '.') }}</p>
                        <div class="flex items-center justify-end gap-1.5">
                            <span class="text-xs {{ $probColor['badge'] }} font-semibold px-1.5 py-0.5 rounded-md">{{ $prob }}%</span>
                            <span class="text-xs text-slate-400">{{ $negocio['fecha_estimada_cierre'] }}</span>
                        </div>
                    </div>
                </div>
            </div>
            @empty
            <x-ui.empty-state icon="clock" title="Sin cierres próximos" description="No hay negocios con fecha de cierre en los próximos 30 días."/>
            @endforelse
        </x-ui.card>

        @if(!empty($kpis['top_asesores']))
        <x-ui.card class="p-5">
            <h3 class="text-sm font-semibold text-slate-700 mb-4">Top Asesores</h3>
            @php
                $medals = [
                    0 => ['ring'=>'ring-2 ring-yellow-300','bg'=>'bg-gradient-to-b from-yellow-300 to-yellow-500','text'=>'text-white','shadow'=>'shadow-yellow-200'],
                    1 => ['ring'=>'ring-2 ring-slate-300', 'bg'=>'bg-gradient-to-b from-slate-300 to-slate-400', 'text'=>'text-white','shadow'=>'shadow-slate-200'],
                    2 => ['ring'=>'ring-2 ring-orange-300','bg'=>'bg-gradient-to-b from-orange-300 to-orange-500','text'=>'text-white','shadow'=>'shadow-orange-200'],
                ];
            @endphp
            @foreach($kpis['top_asesores'] as $i => $asesor)
            @php $m = $medals[$i] ?? ['ring'=>'','bg'=>'bg-slate-100','text'=>'text-slate-600','shadow'=>'']; @endphp
            <div class="flex items-center gap-3 py-2.5 {{ !$loop->last ? 'border-b border-slate-100' : '' }}">
                <div class="w-8 h-8 rounded-full {{ $m['bg'] }} {{ $m['ring'] }} shadow-md {{ $m['shadow'] }} flex items-center justify-center text-xs font-extrabold {{ $m['text'] }} shrink-0">
                    @if($i === 0)
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 18.75h-9m9 0a3 3 0 013 3h-15a3 3 0 013-3m9 0v-3.375c0-.621-.503-1.125-1.125-1.125h-.871M7.5 18.75v-3.375c0-.621.504-1.125 1.125-1.125h.872m5.007 0H9.497m5.007 0a7.454 7.454 0 01-.982-3.172M9.497 14.25a7.454 7.454 0 00.981-3.172M5.25 4.236c-.982.143-1.954.317-2.916.52A6.003 6.003 0 007.73 9.728M5.25 4.236V4.5c0 2.108.966 3.99 2.48 5.228M5.25 4.236V2.721C7.456 2.41 9.71 2.25 12 2.25c2.291 0 4.545.16 6.75.47v1.516M7.73 9.728a6.726 6.726 0 002.748 1.35m8.272-6.842V4.5c0 2.108-.966 3.99-2.48 5.228m2.48-5.492a46.32 46.32 0 012.916.52 6.003 6.003 0 01-5.395 4.972m0 0a6.726 6.726 0 01-2.749 1.35m0 0a6.772 6.772 0 01-3.044 0"/>
                        </svg>
                    @elseif($i === 1)
                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24">
                            <path fill-rule="evenodd" d="M10.788 3.21c.448-1.077 1.976-1.077 2.424 0l2.082 5.006 5.404.434c1.164.093 1.636 1.545.749 2.305l-4.117 3.527 1.257 5.273c.271 1.136-.964 2.033-1.96 1.425L12 18.354 7.373 21.18c-.996.608-2.231-.29-1.96-1.425l1.257-5.273-4.117-3.527c-.887-.76-.415-2.212.749-2.305l5.404-.434 2.082-5.005Z" clip-rule="evenodd"/>
                        </svg>
                    @elseif($i === 2)
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M11.48 3.499a.562.562 0 011.04 0l2.125 5.111a.563.563 0 00.475.345l5.518.442c.499.04.701.663.321.988l-4.204 3.602a.563.563 0 00-.182.557l1.285 5.385a.562.562 0 01-.84.61l-4.725-2.885a.563.563 0 00-.586 0L6.982 20.54a.562.562 0 01-.84-.61l1.285-5.386a.562.562 0 00-.182-.557l-4.204-3.602a.563.563 0 01.321-.988l5.518-.442a.563.563 0 00.475-.345L11.48 3.5z"/>
                        </svg>
                    @else
                        {{ $i + 1 }}
                    @endif
                </div>
                <div class="flex-1 min-w-0">
                    <p class="text-sm font-semibold text-slate-900 truncate">{{ $asesor['nombre'] }}</p>
                    <p class="text-xs text-slate-400">{{ $asesor['negocios_ganados'] }} negocios ganados</p>
                </div>
                <p class="text-sm font-bold text-emerald-600 shrink-0">
                    ${{ number_format($asesor['valor_ganado'], 0, ',', '.') }}
                </p>
            </div>
            @endforeach
        </x-ui.card>
        @endif
    </div>

    {{-- Aging: colapsado por defecto --}}
    @if(!empty($kpis['aging']))
    <x-ui.card class="p-5 mb-8" x-data="{ open: false }">
        <button @click="open = !open" class="w-full flex items-center justify-between group">
            <div class="flex items-center gap-2.5">
                <svg :class="open ? 'text-blue-600' : 'text-slate-400'" class="w-4 h-4 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <h3 class="text-sm font-semibold text-slate-700 group-hover:text-slate-900 transition-colors">Aging de oportunidades</h3>
                <span class="text-xs text-slate-400">(días promedio por etapa)</span>
            </div>
            <div class="flex items-center gap-3">
                @php $maxDias = collect($kpis['aging'])->max('avg_dias') ?: 1; @endphp
                <div class="flex items-end gap-0.5 h-5">
                    @foreach($kpis['aging'] as $g)
                    @php $h = max(3, round(($g['avg_dias'] / $maxDias) * 20)); @endphp
                    <div class="w-2 rounded-sm opacity-70" style="height:{{ $h }}px; background-color:{{ $g['color'] }}"></div>
                    @endforeach
                </div>
                <svg :class="open ? 'rotate-180' : ''" class="w-4 h-4 text-slate-400 transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                </svg>
            </div>
        </button>

        <div x-show="open"
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0 -translate-y-1"
             x-transition:enter-end="opacity-100 translate-y-0"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="opacity-100 translate-y-0"
             x-transition:leave-end="opacity-0 -translate-y-1"
             class="mt-5 pt-4 border-t border-slate-100 space-y-3">
            <a href="{{ route('negocios.kanban') }}" class="text-xs text-blue-600 hover:underline float-right font-medium">Ver kanban →</a>
            @foreach($kpis['aging'] as $grupo)
            @php
                $pct    = min(100, round(($grupo['avg_dias'] / $maxDias) * 100));
                $alerta = $grupo['avg_dias'] > 30;
            @endphp
            <div>
                <div class="flex items-center justify-between mb-1.5">
                    <div class="flex items-center gap-2">
                        <div class="w-2.5 h-2.5 rounded-full shrink-0" style="background-color:{{ $grupo['color'] }}"></div>
                        <span class="text-xs font-medium text-slate-700">{{ $grupo['estado'] }}</span>
                        <span class="text-xs text-slate-400">({{ $grupo['cantidad'] }})</span>
                    </div>
                    <span class="text-xs font-bold {{ $alerta ? 'text-amber-600' : 'text-slate-500' }}">
                        {{ $grupo['avg_dias'] }} días @if($alerta)⚠@endif
                    </span>
                </div>
                <div class="h-2 bg-slate-100 rounded-full overflow-hidden">
                    <div class="h-full rounded-full transition-all duration-500"
                         style="width:{{ $pct }}%; background: linear-gradient(90deg, {{ $grupo['color'] }}aa, {{ $grupo['color'] }})"></div>
                </div>
            </div>
            @endforeach
        </div>
    </x-ui.card>
    @endif

    {{-- ══════════════════════════════════════════════
         SECCIÓN: INTELIGENCIA COMERCIAL (ERP)
    ══════════════════════════════════════════════ --}}
    @isset($kpis['inteligencia'])
    @php
        $intel        = $kpis['inteligencia'];
        $totalAlertas = count($intel['en_fuga'] ?? []) + count($intel['atencion_inmediata'] ?? []);
        $totalOpor    = count($intel['rescate'] ?? []) + count($intel['expansion'] ?? []);
        $tieneGer     = !empty($intel['integrales']) || !empty($intel['panorama_gerencial']);
        $totalPres    = count($intel['presupuesto_activo'] ?? [])
                      + count($intel['presupuesto_en_riesgo'] ?? [])
                      + count($intel['presupuesto_recuperar'] ?? [])
                      + count($intel['largo_plazo'] ?? []);
    @endphp

    <script>
        window._erpData = {
            enFuga:               {!! json_encode($intel['en_fuga'] ?? []) !!},
            atencionInmediata:    {!! json_encode($intel['atencion_inmediata'] ?? []) !!},
            rescate:              {!! json_encode($intel['rescate'] ?? []) !!},
            expansion:            {!! json_encode($intel['expansion'] ?? []) !!},
            presupuestoActivo:    {!! json_encode($intel['presupuesto_activo'] ?? []) !!},
            presupuestoEnRiesgo:  {!! json_encode($intel['presupuesto_en_riesgo'] ?? []) !!},
            presupuestoRecuperar: {!! json_encode($intel['presupuesto_recuperar'] ?? []) !!},
            largoPlazo:           {!! json_encode($intel['largo_plazo'] ?? []) !!},
        };

        function erpCard(items) {
            return {
                all: items,
                page: 1, perPage: 5, sortBy: null, sortDir: 1,
                get sorted() {
                    if (!this.sortBy) return this.all;
                    return [...this.all].sort((a, b) => {
                        let x = a[this.sortBy] ?? '', y = b[this.sortBy] ?? '';
                        if (typeof x === 'string') { x = x.toLowerCase(); y = y.toLowerCase(); }
                        return (x < y ? -1 : x > y ? 1 : 0) * this.sortDir;
                    });
                },
                get paged()  { const i = (this.page-1)*this.perPage; return this.sorted.slice(i, i+this.perPage); },
                get pages()  { return Math.ceil(this.all.length / this.perPage) || 1; },
                sortOn(f)    { this.sortBy === f ? this.sortDir *= -1 : (this.sortBy = f, this.sortDir = 1); this.page = 1; },
                fmt(n)       { return '$' + (parseFloat(n) || 0).toLocaleString('es-CO', { maximumFractionDigits: 0 }); },
                arrow(f)     { return this.sortBy !== f ? '↕' : this.sortDir === 1 ? '↑' : '↓'; },
            };
        }

        window._tableData = {
            panoramaPresupuestal: {!! json_encode($intel['panorama_presupuestal'] ?? []) !!},
            integrales:           {!! json_encode($intel['integrales'] ?? []) !!},
            panoramaGerencial:    {!! json_encode($intel['panorama_gerencial'] ?? []) !!},
        };

        function sortableTable(items) {
            return {
                all: items,
                sortBy: null, sortDir: 1,
                get sorted() {
                    if (!this.sortBy) return this.all;
                    return [...this.all].sort((a, b) => {
                        let x = a[this.sortBy] ?? '', y = b[this.sortBy] ?? '';
                        const nx = parseFloat(x), ny = parseFloat(y);
                        if (!isNaN(nx) && !isNaN(ny)) { x = nx; y = ny; }
                        else { x = String(x).toLowerCase(); y = String(y).toLowerCase(); }
                        return (x < y ? -1 : x > y ? 1 : 0) * this.sortDir;
                    });
                },
                sortOn(f) { this.sortBy === f ? this.sortDir *= -1 : (this.sortBy = f, this.sortDir = 1); },
                fmt(n)    { return '$' + (parseFloat(n) || 0).toLocaleString('es-CO', { maximumFractionDigits: 0 }); },
                arrow(f)  { return this.sortBy !== f ? '↕' : this.sortDir === 1 ? '↑' : '↓'; },
            };
        }
    </script>

    {{-- Header de sección --}}
    <div class="flex items-center justify-between mb-5">
        <div class="flex items-center gap-3">
            <div class="flex h-7 w-7 shrink-0 items-center justify-center rounded-lg bg-rose-600 shadow-sm shadow-rose-300">
                <svg class="h-3.5 w-3.5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/>
                </svg>
            </div>
            <h2 class="text-sm font-bold text-slate-800 uppercase tracking-wider">Inteligencia Comercial</h2>
            <span class="text-xs text-slate-400 font-medium">ERP Contiflex</span>
            <div class="flex-1 h-px bg-gradient-to-r from-rose-200 via-rose-100 to-transparent" style="min-width:60px"></div>
        </div>
        @if(!$intel['disponible'])
        <span class="inline-flex items-center gap-1.5 text-xs bg-amber-100 text-amber-700 px-3 py-1 rounded-full font-semibold border border-amber-200">
            <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126z"/>
            </svg>
            Sin conexión ERP
        </span>
        @else
        <span class="inline-flex items-center gap-1.5 text-xs bg-emerald-100 text-emerald-700 px-3 py-1 rounded-full font-semibold border border-emerald-200">
            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
            Conectado
        </span>
        @endif
    </div>

    @if($intel['disponible'])
    <div x-data="{ tab: 'alertas' }">

        {{-- Barra de pestañas --}}
        <div class="flex items-center gap-1.5 bg-slate-100 rounded-2xl p-1.5 mb-5">
            <button @click="tab = 'alertas'"
                    :class="tab === 'alertas' ? 'bg-white shadow-sm text-slate-900 shadow-slate-200' : 'text-slate-500 hover:text-slate-700 hover:bg-white/50'"
                    class="flex-1 flex items-center justify-center gap-2 px-3 py-2 rounded-xl text-xs font-semibold transition-all">
                <span class="w-2 h-2 rounded-full bg-rose-500 shrink-0"></span>
                Alertas urgentes
                @if($totalAlertas > 0)
                <span class="text-xs bg-rose-500 text-white px-1.5 py-0.5 rounded-full font-bold leading-none">{{ $totalAlertas }}</span>
                @endif
            </button>

            <button @click="tab = 'oportunidades'"
                    :class="tab === 'oportunidades' ? 'bg-white shadow-sm text-slate-900 shadow-slate-200' : 'text-slate-500 hover:text-slate-700 hover:bg-white/50'"
                    class="flex-1 flex items-center justify-center gap-2 px-3 py-2 rounded-xl text-xs font-semibold transition-all">
                <span class="w-2 h-2 rounded-full bg-blue-500 shrink-0"></span>
                Oportunidades
                @if($totalOpor > 0)
                <span class="text-xs bg-blue-500 text-white px-1.5 py-0.5 rounded-full font-bold leading-none">{{ $totalOpor }}</span>
                @endif
            </button>

            @if($totalPres > 0)
            <button @click="tab = 'presupuesto'"
                    :class="tab === 'presupuesto' ? 'bg-white shadow-sm text-slate-900 shadow-slate-200' : 'text-slate-500 hover:text-slate-700 hover:bg-white/50'"
                    class="flex-1 flex items-center justify-center gap-2 px-3 py-2 rounded-xl text-xs font-semibold transition-all">
                <span class="w-2 h-2 rounded-full bg-teal-500 shrink-0"></span>
                Presupuesto
                <span class="text-xs bg-teal-500 text-white px-1.5 py-0.5 rounded-full font-bold leading-none">{{ $totalPres }}</span>
            </button>
            @endif

            @if($tieneGer)
            <button @click="tab = 'gerencial'"
                    :class="tab === 'gerencial' ? 'bg-white shadow-sm text-slate-900 shadow-slate-200' : 'text-slate-500 hover:text-slate-700 hover:bg-white/50'"
                    class="flex-1 flex items-center justify-center gap-2 px-3 py-2 rounded-xl text-xs font-semibold transition-all">
                <span class="w-2 h-2 rounded-full bg-purple-500 shrink-0"></span>
                Visión gerencial
            </button>
            @endif
        </div>

        {{-- ── TAB: ALERTAS URGENTES ── --}}
        <div x-show="tab === 'alertas'"
             x-transition:enter="transition ease-out duration-150"
             x-transition:enter-start="opacity-0 translate-y-1"
             x-transition:enter-end="opacity-100 translate-y-0">
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">

                {{-- Clientes en Fuga --}}
                <x-ui.card x-data="erpCard(window._erpData.enFuga)">
                    <div class="h-1.5 w-full rounded-t-xl bg-gradient-to-r from-rose-500 to-rose-400"></div>
                    <div class="p-5">
                        <div class="flex items-start justify-between mb-4">
                            <div>
                                <h3 class="text-sm font-bold text-slate-900">Clientes en Fuga</h3>
                                <p class="text-xs text-slate-400 mt-0.5">+$100M histórico · 90–180 días sin comprar</p>
                            </div>
                            <span class="text-xs bg-rose-100 text-rose-700 border border-rose-200 px-2 py-1 rounded-lg font-bold" x-text="all.length + ' clientes'"></span>
                        </div>

                        <div class="flex items-center text-xs font-semibold text-slate-400 pb-2 border-b border-slate-100 select-none gap-2">
                            <button @click="sortOn('RAZON_SOCIAL')" :class="sortBy==='RAZON_SOCIAL'?'text-rose-600':'hover:text-slate-600'" class="flex items-center gap-1 transition-colors">
                                Nombre <span x-text="arrow('RAZON_SOCIAL')" class="text-slate-300"></span>
                            </button>
                            <div class="ml-auto flex items-center gap-3">
                                <button @click="sortOn('DIAS_DESDE_ULTIMA_COMPRA')" :class="sortBy==='DIAS_DESDE_ULTIMA_COMPRA'?'text-rose-600':'hover:text-slate-600'" class="flex items-center gap-1 transition-colors">
                                    Días <span x-text="arrow('DIAS_DESDE_ULTIMA_COMPRA')" class="text-slate-300"></span>
                                </button>
                                <button @click="sortOn('VLR_NETO_FACTURADO')" :class="sortBy==='VLR_NETO_FACTURADO'?'text-rose-600':'hover:text-slate-600'" class="flex items-center gap-1 transition-colors">
                                    Valor <span x-text="arrow('VLR_NETO_FACTURADO')" class="text-slate-300"></span>
                                </button>
                            </div>
                        </div>

                        <div x-show="all.length === 0" class="py-10 text-center text-xs text-slate-400">Sin clientes en fuga de alto valor.</div>

                        <template x-for="(cli, idx) in paged" :key="idx">
                            <div :class="idx < paged.length-1 ? 'border-b border-slate-100' : ''" class="py-3 flex items-center gap-3">
                                <div class="w-1.5 h-8 rounded-full bg-rose-200 shrink-0"></div>
                                <div class="flex-1 min-w-0">
                                    <p class="text-sm font-semibold text-slate-900 truncate" x-text="cli.RAZON_SOCIAL"></p>
                                    <p class="text-xs text-slate-400 mt-0.5" x-text="cli.NOMBRE_VENDEDOR"></p>
                                </div>
                                <div class="text-right shrink-0">
                                    <p class="text-sm font-bold text-slate-800" x-text="fmt(cli.VLR_NETO_FACTURADO)"></p>
                                    <p class="text-xs font-semibold text-rose-500 mt-0.5" x-text="cli.DIAS_DESDE_ULTIMA_COMPRA + ' días'"></p>
                                </div>
                            </div>
                        </template>

                        <div x-show="pages > 1" class="flex items-center justify-between mt-3 pt-3 border-t border-slate-100">
                            <button @click="page > 1 && page--" :disabled="page===1" class="px-2.5 py-1.5 text-xs text-slate-500 hover:text-slate-800 hover:bg-slate-100 rounded-lg disabled:opacity-30 transition-colors">← Ant.</button>
                            <div class="flex gap-1">
                                <template x-for="p in pages" :key="p">
                                    <button @click="page=p" :class="page===p ? 'bg-rose-600 text-white shadow-sm' : 'bg-slate-100 text-slate-500 hover:bg-slate-200'" class="w-7 h-7 rounded-lg text-xs font-semibold transition-colors" x-text="p"></button>
                                </template>
                            </div>
                            <button @click="page < pages && page++" :disabled="page===pages" class="px-2.5 py-1.5 text-xs text-slate-500 hover:text-slate-800 hover:bg-slate-100 rounded-lg disabled:opacity-30 transition-colors">Sig. →</button>
                        </div>
                    </div>
                </x-ui.card>

                {{-- Atención Inmediata --}}
                <x-ui.card x-data="erpCard(window._erpData.atencionInmediata)">
                    <div class="h-1.5 w-full rounded-t-xl bg-gradient-to-r from-amber-500 to-amber-400"></div>
                    <div class="p-5">
                        <div class="flex items-start justify-between mb-4">
                            <div>
                                <h3 class="text-sm font-bold text-slate-900">Atención Inmediata</h3>
                                <p class="text-xs text-slate-400 mt-0.5">Clientes VIP y urgentes · acción requerida</p>
                            </div>
                            <span class="text-xs bg-amber-100 text-amber-700 border border-amber-200 px-2 py-1 rounded-lg font-bold" x-text="all.length + ' clientes'"></span>
                        </div>

                        <div class="flex items-center text-xs font-semibold text-slate-400 pb-2 border-b border-slate-100 select-none gap-2">
                            <button @click="sortOn('RAZON_SOCIAL')" :class="sortBy==='RAZON_SOCIAL'?'text-amber-600':'hover:text-slate-600'" class="flex items-center gap-1 transition-colors">
                                Nombre <span x-text="arrow('RAZON_SOCIAL')" class="text-slate-300"></span>
                            </button>
                            <div class="ml-auto flex items-center gap-3">
                                <button @click="sortOn('DIAS_DESDE_ULTIMA_COMPRA')" :class="sortBy==='DIAS_DESDE_ULTIMA_COMPRA'?'text-amber-600':'hover:text-slate-600'" class="flex items-center gap-1 transition-colors">
                                    Días <span x-text="arrow('DIAS_DESDE_ULTIMA_COMPRA')" class="text-slate-300"></span>
                                </button>
                                <button @click="sortOn('FACTURADO_ANIO_ACTUAL')" :class="sortBy==='FACTURADO_ANIO_ACTUAL'?'text-amber-600':'hover:text-slate-600'" class="flex items-center gap-1 transition-colors">
                                    Facturado <span x-text="arrow('FACTURADO_ANIO_ACTUAL')" class="text-slate-300"></span>
                                </button>
                            </div>
                        </div>

                        <div x-show="all.length === 0" class="py-10 text-center text-xs text-slate-400">Sin alertas de atención inmediata.</div>

                        <template x-for="(cli, idx) in paged" :key="idx">
                            <div :class="idx < paged.length-1 ? 'border-b border-slate-100' : ''" class="py-3">
                                <div class="flex items-start gap-3">
                                    <div class="w-1.5 h-8 rounded-full shrink-0 mt-1 bg-emerald-300"></div>
                                    <div class="flex-1 min-w-0">
                                        <div class="flex items-center gap-2 flex-wrap">
                                            <p class="text-sm font-semibold text-slate-900 truncate" x-text="cli.RAZON_SOCIAL"></p>
                                            <span class="text-xs px-1.5 py-0.5 rounded-md font-bold border bg-emerald-100 text-emerald-700 border-emerald-200 shrink-0">P1 Activo</span>
                                        </div>
                                        <p class="text-xs text-slate-400 mt-0.5" x-text="(cli.CIUDAD||'') + ' · ' + (cli.NOMBRE_VENDEDOR||'')"></p>
                                        <p x-show="cli.ACCION_PRESUPUESTAL" class="text-xs text-slate-500 mt-1 italic" x-text="'→ ' + cli.ACCION_PRESUPUESTAL"></p>
                                    </div>
                                    <div class="text-right shrink-0">
                                        <p class="text-sm font-bold text-slate-800" x-text="fmt(cli.FACTURADO_ANIO_ACTUAL)"></p>
                                        <p class="text-xs text-slate-400 mt-0.5" x-text="cli.DIAS_DESDE_ULTIMA_COMPRA + ' días'"></p>
                                    </div>
                                </div>
                            </div>
                        </template>

                        <div x-show="pages > 1" class="flex items-center justify-between mt-3 pt-3 border-t border-slate-100">
                            <button @click="page > 1 && page--" :disabled="page===1" class="px-2.5 py-1.5 text-xs text-slate-500 hover:text-slate-800 hover:bg-slate-100 rounded-lg disabled:opacity-30 transition-colors">← Ant.</button>
                            <div class="flex gap-1">
                                <template x-for="p in pages" :key="p">
                                    <button @click="page=p" :class="page===p ? 'bg-amber-500 text-white shadow-sm' : 'bg-slate-100 text-slate-500 hover:bg-slate-200'" class="w-7 h-7 rounded-lg text-xs font-semibold transition-colors" x-text="p"></button>
                                </template>
                            </div>
                            <button @click="page < pages && page++" :disabled="page===pages" class="px-2.5 py-1.5 text-xs text-slate-500 hover:text-slate-800 hover:bg-slate-100 rounded-lg disabled:opacity-30 transition-colors">Sig. →</button>
                        </div>
                    </div>
                </x-ui.card>

            </div>
        </div>

        {{-- ── TAB: OPORTUNIDADES ── --}}
        <div x-show="tab === 'oportunidades'"
             x-transition:enter="transition ease-out duration-150"
             x-transition:enter-start="opacity-0 translate-y-1"
             x-transition:enter-end="opacity-100 translate-y-0">
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">

                {{-- Rescate y Reactivación --}}
                <x-ui.card x-data="erpCard(window._erpData.rescate)">
                    <div class="h-1.5 w-full rounded-t-xl bg-gradient-to-r from-indigo-500 to-indigo-400"></div>
                    <div class="p-5">
                        <div class="flex items-start justify-between mb-4">
                            <div>
                                <h3 class="text-sm font-bold text-slate-900">Rescate y Reactivación</h3>
                                <p class="text-xs text-slate-400 mt-0.5">Clientes valiosos enfriándose o dormidos</p>
                            </div>
                            <span class="text-xs bg-indigo-100 text-indigo-700 border border-indigo-200 px-2 py-1 rounded-lg font-bold" x-text="all.length + ' clientes'"></span>
                        </div>

                        <div class="flex items-center text-xs font-semibold text-slate-400 pb-2 border-b border-slate-100 select-none gap-2">
                            <button @click="sortOn('RAZON_SOCIAL')" :class="sortBy==='RAZON_SOCIAL'?'text-indigo-600':'hover:text-slate-600'" class="flex items-center gap-1 transition-colors">
                                Nombre <span x-text="arrow('RAZON_SOCIAL')" class="text-slate-300"></span>
                            </button>
                            <div class="ml-auto flex items-center gap-3">
                                <button @click="sortOn('DIAS_DESDE_ULTIMA_COMPRA')" :class="sortBy==='DIAS_DESDE_ULTIMA_COMPRA'?'text-indigo-600':'hover:text-slate-600'" class="flex items-center gap-1 transition-colors">
                                    Días <span x-text="arrow('DIAS_DESDE_ULTIMA_COMPRA')" class="text-slate-300"></span>
                                </button>
                                <button @click="sortOn('FACTURADO_ANIO_ACTUAL')" :class="sortBy==='FACTURADO_ANIO_ACTUAL'?'text-indigo-600':'hover:text-slate-600'" class="flex items-center gap-1 transition-colors">
                                    Facturado <span x-text="arrow('FACTURADO_ANIO_ACTUAL')" class="text-slate-300"></span>
                                </button>
                            </div>
                        </div>

                        <div x-show="all.length === 0" class="py-10 text-center text-xs text-slate-400">Sin clientes en rescate o reactivación.</div>

                        <template x-for="(cli, idx) in paged" :key="idx">
                            <div :class="idx < paged.length-1 ? 'border-b border-slate-100' : ''" class="py-3">
                                <div class="flex items-start gap-3">
                                    <div class="w-1.5 h-8 rounded-full shrink-0 mt-1"
                                         :class="cli.HORIZONTE_PRESUPUESTO?.startsWith('P2') ? 'bg-orange-300' : 'bg-indigo-300'"></div>
                                    <div class="flex-1 min-w-0">
                                        <div class="flex items-center gap-2 flex-wrap">
                                            <p class="text-sm font-semibold text-slate-900 truncate" x-text="cli.RAZON_SOCIAL"></p>
                                            <span :class="cli.HORIZONTE_PRESUPUESTO?.startsWith('P2') ? 'bg-orange-100 text-orange-700 border-orange-200' : 'bg-indigo-100 text-indigo-700 border-indigo-200'"
                                                  class="text-xs px-1.5 py-0.5 rounded-md font-bold border shrink-0"
                                                  x-text="cli.HORIZONTE_PRESUPUESTO?.startsWith('P2') ? 'En Riesgo' : 'Recuperar'"></span>
                                        </div>
                                        <p class="text-xs text-slate-400 mt-0.5" x-text="(cli.CIUDAD||'') + ' · ' + (cli.NOMBRE_VENDEDOR||'')"></p>
                                        <p x-show="cli.ACCION_PRESUPUESTAL" class="text-xs text-slate-500 mt-1 italic" x-text="'→ ' + cli.ACCION_PRESUPUESTAL"></p>
                                    </div>
                                    <div class="text-right shrink-0">
                                        <p class="text-sm font-bold text-slate-800" x-text="fmt(cli.FACTURADO_ANIO_ACTUAL)"></p>
                                        <p class="text-xs text-slate-400 mt-0.5" x-text="cli.DIAS_DESDE_ULTIMA_COMPRA + ' días'"></p>
                                    </div>
                                </div>
                            </div>
                        </template>

                        <div x-show="pages > 1" class="flex items-center justify-between mt-3 pt-3 border-t border-slate-100">
                            <button @click="page > 1 && page--" :disabled="page===1" class="px-2.5 py-1.5 text-xs text-slate-500 hover:text-slate-800 hover:bg-slate-100 rounded-lg disabled:opacity-30 transition-colors">← Ant.</button>
                            <div class="flex gap-1">
                                <template x-for="p in pages" :key="p">
                                    <button @click="page=p" :class="page===p ? 'bg-indigo-600 text-white shadow-sm' : 'bg-slate-100 text-slate-500 hover:bg-slate-200'" class="w-7 h-7 rounded-lg text-xs font-semibold transition-colors" x-text="p"></button>
                                </template>
                            </div>
                            <button @click="page < pages && page++" :disabled="page===pages" class="px-2.5 py-1.5 text-xs text-slate-500 hover:text-slate-800 hover:bg-slate-100 rounded-lg disabled:opacity-30 transition-colors">Sig. →</button>
                        </div>
                    </div>
                </x-ui.card>

                {{-- Expansión --}}
                <x-ui.card x-data="erpCard(window._erpData.expansion)">
                    <div class="h-1.5 w-full rounded-t-xl bg-gradient-to-r from-emerald-500 to-emerald-400"></div>
                    <div class="p-5">
                        <div class="flex items-start justify-between mb-4">
                            <div>
                                <h3 class="text-sm font-bold text-slate-900">Clientes para Expansión</h3>
                                <p class="text-xs text-slate-400 mt-0.5">Compraron últimos 30 días · mayor facturación</p>
                            </div>
                            <span class="text-xs bg-emerald-100 text-emerald-700 border border-emerald-200 px-2 py-1 rounded-lg font-bold" x-text="all.length + ' clientes'"></span>
                        </div>

                        <div class="flex items-center text-xs font-semibold text-slate-400 pb-2 border-b border-slate-100 select-none gap-2">
                            <button @click="sortOn('RAZON_SOCIAL')" :class="sortBy==='RAZON_SOCIAL'?'text-emerald-600':'hover:text-slate-600'" class="flex items-center gap-1 transition-colors">
                                Nombre <span x-text="arrow('RAZON_SOCIAL')" class="text-slate-300"></span>
                            </button>
                            <div class="ml-auto flex items-center gap-3">
                                <button @click="sortOn('NUM_FACTURAS')" :class="sortBy==='NUM_FACTURAS'?'text-emerald-600':'hover:text-slate-600'" class="flex items-center gap-1 transition-colors">
                                    Facturas <span x-text="arrow('NUM_FACTURAS')" class="text-slate-300"></span>
                                </button>
                                <button @click="sortOn('VLR_NETO_FACTURADO')" :class="sortBy==='VLR_NETO_FACTURADO'?'text-emerald-600':'hover:text-slate-600'" class="flex items-center gap-1 transition-colors">
                                    Valor <span x-text="arrow('VLR_NETO_FACTURADO')" class="text-slate-300"></span>
                                </button>
                            </div>
                        </div>

                        <div x-show="all.length === 0" class="py-10 text-center text-xs text-slate-400">Sin clientes activos con compras recientes.</div>

                        <template x-for="(cli, idx) in paged" :key="idx">
                            <div :class="idx < paged.length-1 ? 'border-b border-slate-100' : ''" class="py-3 flex items-center gap-3">
                                <div class="w-1.5 h-8 rounded-full bg-emerald-200 shrink-0"></div>
                                <div class="flex-1 min-w-0">
                                    <p class="text-sm font-semibold text-slate-900 truncate" x-text="cli.RAZON_SOCIAL"></p>
                                    <p class="text-xs text-slate-400 mt-0.5" x-text="(cli.NOMBRE_VENDEDOR||'') + ' · ' + (cli.NUM_FACTURAS||0) + ' facturas'"></p>
                                </div>
                                <div class="text-right shrink-0">
                                    <p class="text-sm font-bold text-slate-800" x-text="fmt(cli.VLR_NETO_FACTURADO)"></p>
                                    <p class="text-xs font-semibold text-emerald-600 mt-0.5" x-text="'hace ' + cli.DIAS_DESDE_ULTIMA_COMPRA + ' días'"></p>
                                </div>
                            </div>
                        </template>

                        <div x-show="pages > 1" class="flex items-center justify-between mt-3 pt-3 border-t border-slate-100">
                            <button @click="page > 1 && page--" :disabled="page===1" class="px-2.5 py-1.5 text-xs text-slate-500 hover:text-slate-800 hover:bg-slate-100 rounded-lg disabled:opacity-30 transition-colors">← Ant.</button>
                            <div class="flex gap-1">
                                <template x-for="p in pages" :key="p">
                                    <button @click="page=p" :class="page===p ? 'bg-emerald-600 text-white shadow-sm' : 'bg-slate-100 text-slate-500 hover:bg-slate-200'" class="w-7 h-7 rounded-lg text-xs font-semibold transition-colors" x-text="p"></button>
                                </template>
                            </div>
                            <button @click="page < pages && page++" :disabled="page===pages" class="px-2.5 py-1.5 text-xs text-slate-500 hover:text-slate-800 hover:bg-slate-100 rounded-lg disabled:opacity-30 transition-colors">Sig. →</button>
                        </div>
                    </div>
                </x-ui.card>

            </div>
        </div>

        {{-- ── TAB: PRESUPUESTO ── --}}
        @if($totalPres > 0)
        <div x-show="tab === 'presupuesto'"
             x-transition:enter="transition ease-out duration-150"
             x-transition:enter-start="opacity-0 translate-y-1"
             x-transition:enter-end="opacity-100 translate-y-0">

            {{-- Fila 1: P1 Activos + P2 En Riesgo --}}
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 mb-4">

                {{-- P1: Críticos para presupuesto activo --}}
                <x-ui.card x-data="erpCard(window._erpData.presupuestoActivo)">
                    <div class="h-1.5 w-full rounded-t-xl bg-gradient-to-r from-emerald-500 to-teal-400"></div>
                    <div class="p-5">
                        <div class="flex items-start justify-between mb-1">
                            <div>
                                <div class="flex items-center gap-2">
                                    <span class="text-xs font-bold bg-emerald-100 text-emerald-700 border border-emerald-200 px-1.5 py-0.5 rounded-md">P1</span>
                                    <h3 class="text-sm font-bold text-slate-900">Presupuesto Activo</h3>
                                </div>
                                <p class="text-xs text-slate-400 mt-0.5">Aportando ahora — no perder la meta</p>
                            </div>
                            <span class="text-xs bg-emerald-100 text-emerald-700 border border-emerald-200 px-2 py-1 rounded-lg font-bold" x-text="all.length + ' clientes'"></span>
                        </div>

                        <div class="flex items-center text-xs font-semibold text-slate-400 py-2 mt-2 border-b border-slate-100 select-none gap-2">
                            <button @click="sortOn('RAZON_SOCIAL')" :class="sortBy==='RAZON_SOCIAL'?'text-teal-600':'hover:text-slate-600'" class="flex items-center gap-1 transition-colors">
                                Nombre <span x-text="arrow('RAZON_SOCIAL')" class="text-slate-300"></span>
                            </button>
                            <div class="ml-auto flex items-center gap-3">
                                <button @click="sortOn('DIAS_DESDE_ULTIMA_COMPRA')" :class="sortBy==='DIAS_DESDE_ULTIMA_COMPRA'?'text-teal-600':'hover:text-slate-600'" class="flex items-center gap-1 transition-colors">
                                    Días <span x-text="arrow('DIAS_DESDE_ULTIMA_COMPRA')" class="text-slate-300"></span>
                                </button>
                                <button @click="sortOn('FACTURADO_ANIO_ACTUAL')" :class="sortBy==='FACTURADO_ANIO_ACTUAL'?'text-teal-600':'hover:text-slate-600'" class="flex items-center gap-1 transition-colors">
                                    Año actual <span x-text="arrow('FACTURADO_ANIO_ACTUAL')" class="text-slate-300"></span>
                                </button>
                            </div>
                        </div>

                        <div x-show="all.length === 0" class="py-10 text-center text-xs text-slate-400">Sin clientes con presupuesto activo.</div>

                        <template x-for="(cli, idx) in paged" :key="idx">
                            <div :class="idx < paged.length-1 ? 'border-b border-slate-100' : ''" class="py-3 flex items-center gap-3">
                                <div class="w-1.5 h-8 rounded-full bg-emerald-200 shrink-0"></div>
                                <div class="flex-1 min-w-0">
                                    <p class="text-sm font-semibold text-slate-900 truncate" x-text="cli.RAZON_SOCIAL"></p>
                                    <p class="text-xs text-slate-400 mt-0.5" x-text="(cli.NOMBRE_VENDEDOR||'') + ' · ' + (cli.CIUDAD||'')"></p>
                                    <p x-show="cli.ACCION_PRESUPUESTAL" class="text-xs text-slate-500 mt-0.5 italic" x-text="'→ ' + cli.ACCION_PRESUPUESTAL"></p>
                                </div>
                                <div class="text-right shrink-0">
                                    <p class="text-sm font-bold text-slate-800" x-text="fmt(cli.FACTURADO_ANIO_ACTUAL)"></p>
                                    <p class="text-xs text-slate-400 mt-0.5" x-text="cli.DIAS_DESDE_ULTIMA_COMPRA + ' días'"></p>
                                </div>
                            </div>
                        </template>

                        <div x-show="pages > 1" class="flex items-center justify-between mt-3 pt-3 border-t border-slate-100">
                            <button @click="page > 1 && page--" :disabled="page===1" class="px-2.5 py-1.5 text-xs text-slate-500 hover:bg-slate-100 rounded-lg disabled:opacity-30 transition-colors">← Ant.</button>
                            <div class="flex gap-1">
                                <template x-for="p in pages" :key="p">
                                    <button @click="page=p" :class="page===p ? 'bg-teal-600 text-white shadow-sm' : 'bg-slate-100 text-slate-500 hover:bg-slate-200'" class="w-7 h-7 rounded-lg text-xs font-semibold transition-colors" x-text="p"></button>
                                </template>
                            </div>
                            <button @click="page < pages && page++" :disabled="page===pages" class="px-2.5 py-1.5 text-xs text-slate-500 hover:bg-slate-100 rounded-lg disabled:opacity-30 transition-colors">Sig. →</button>
                        </div>
                    </div>
                </x-ui.card>

                {{-- P2: Presupuesto en riesgo --}}
                <x-ui.card x-data="erpCard(window._erpData.presupuestoEnRiesgo)">
                    <div class="h-1.5 w-full rounded-t-xl bg-gradient-to-r from-orange-500 to-amber-400"></div>
                    <div class="p-5">
                        <div class="flex items-start justify-between mb-1">
                            <div>
                                <div class="flex items-center gap-2">
                                    <span class="text-xs font-bold bg-orange-100 text-orange-700 border border-orange-200 px-1.5 py-0.5 rounded-md">P2</span>
                                    <h3 class="text-sm font-bold text-slate-900">Presupuesto en Riesgo</h3>
                                </div>
                                <p class="text-xs text-slate-400 mt-0.5">Compraron este año, +90 días sin comprar — URGENTE</p>
                            </div>
                            <span class="text-xs bg-orange-100 text-orange-700 border border-orange-200 px-2 py-1 rounded-lg font-bold" x-text="all.length + ' clientes'"></span>
                        </div>

                        <div class="flex items-center text-xs font-semibold text-slate-400 py-2 mt-2 border-b border-slate-100 select-none gap-2">
                            <button @click="sortOn('RAZON_SOCIAL')" :class="sortBy==='RAZON_SOCIAL'?'text-orange-600':'hover:text-slate-600'" class="flex items-center gap-1 transition-colors">
                                Nombre <span x-text="arrow('RAZON_SOCIAL')" class="text-slate-300"></span>
                            </button>
                            <div class="ml-auto flex items-center gap-3">
                                <button @click="sortOn('DIAS_DESDE_ULTIMA_COMPRA')" :class="sortBy==='DIAS_DESDE_ULTIMA_COMPRA'?'text-orange-600':'hover:text-slate-600'" class="flex items-center gap-1 transition-colors">
                                    Días <span x-text="arrow('DIAS_DESDE_ULTIMA_COMPRA')" class="text-slate-300"></span>
                                </button>
                                <button @click="sortOn('IMPACTO_PRESUPUESTO_ESTIMADO')" :class="sortBy==='IMPACTO_PRESUPUESTO_ESTIMADO'?'text-orange-600':'hover:text-slate-600'" class="flex items-center gap-1 transition-colors">
                                    Impacto <span x-text="arrow('IMPACTO_PRESUPUESTO_ESTIMADO')" class="text-slate-300"></span>
                                </button>
                            </div>
                        </div>

                        <div x-show="all.length === 0" class="py-10 text-center text-xs text-slate-400">Sin clientes con presupuesto en riesgo.</div>

                        <template x-for="(cli, idx) in paged" :key="idx">
                            <div :class="idx < paged.length-1 ? 'border-b border-slate-100' : ''" class="py-3 flex items-center gap-3">
                                <div class="w-1.5 h-8 rounded-full bg-orange-200 shrink-0"></div>
                                <div class="flex-1 min-w-0">
                                    <p class="text-sm font-semibold text-slate-900 truncate" x-text="cli.RAZON_SOCIAL"></p>
                                    <p class="text-xs text-slate-400 mt-0.5" x-text="(cli.NOMBRE_VENDEDOR||'') + ' · ' + (cli.CIUDAD||'')"></p>
                                    <p x-show="cli.ACCION_PRESUPUESTAL" class="text-xs text-slate-500 mt-0.5 italic" x-text="'→ ' + cli.ACCION_PRESUPUESTAL"></p>
                                </div>
                                <div class="text-right shrink-0">
                                    <p class="text-sm font-bold text-orange-600" x-text="fmt(cli.IMPACTO_PRESUPUESTO_ESTIMADO)"></p>
                                    <p class="text-xs text-slate-400 mt-0.5" x-text="cli.DIAS_DESDE_ULTIMA_COMPRA + ' días'"></p>
                                </div>
                            </div>
                        </template>

                        <div x-show="pages > 1" class="flex items-center justify-between mt-3 pt-3 border-t border-slate-100">
                            <button @click="page > 1 && page--" :disabled="page===1" class="px-2.5 py-1.5 text-xs text-slate-500 hover:bg-slate-100 rounded-lg disabled:opacity-30 transition-colors">← Ant.</button>
                            <div class="flex gap-1">
                                <template x-for="p in pages" :key="p">
                                    <button @click="page=p" :class="page===p ? 'bg-orange-500 text-white shadow-sm' : 'bg-slate-100 text-slate-500 hover:bg-slate-200'" class="w-7 h-7 rounded-lg text-xs font-semibold transition-colors" x-text="p"></button>
                                </template>
                            </div>
                            <button @click="page < pages && page++" :disabled="page===pages" class="px-2.5 py-1.5 text-xs text-slate-500 hover:bg-slate-100 rounded-lg disabled:opacity-30 transition-colors">Sig. →</button>
                        </div>
                    </div>
                </x-ui.card>

            </div>

            {{-- Fila 2: P3 Recuperar + P4 Largo plazo --}}
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 mb-4">

                {{-- P3: Recuperar para el año --}}
                <x-ui.card x-data="erpCard(window._erpData.presupuestoRecuperar)">
                    <div class="h-1.5 w-full rounded-t-xl bg-gradient-to-r from-blue-500 to-sky-400"></div>
                    <div class="p-5">
                        <div class="flex items-start justify-between mb-1">
                            <div>
                                <div class="flex items-center gap-2">
                                    <span class="text-xs font-bold bg-blue-100 text-blue-700 border border-blue-200 px-1.5 py-0.5 rounded-md">P3</span>
                                    <h3 class="text-sm font-bold text-slate-900">Recuperar para el Año</h3>
                                </div>
                                <p class="text-xs text-slate-400 mt-0.5">Compraron en {{ now()->year - 1 }}, no en {{ now()->year }} — reactivar pronto</p>
                            </div>
                            <span class="text-xs bg-blue-100 text-blue-700 border border-blue-200 px-2 py-1 rounded-lg font-bold" x-text="all.length + ' clientes'"></span>
                        </div>

                        <div class="flex items-center text-xs font-semibold text-slate-400 py-2 mt-2 border-b border-slate-100 select-none gap-2">
                            <button @click="sortOn('RAZON_SOCIAL')" :class="sortBy==='RAZON_SOCIAL'?'text-blue-600':'hover:text-slate-600'" class="flex items-center gap-1 transition-colors">
                                Nombre <span x-text="arrow('RAZON_SOCIAL')" class="text-slate-300"></span>
                            </button>
                            <div class="ml-auto flex items-center gap-3">
                                <button @click="sortOn('DIAS_DESDE_ULTIMA_COMPRA')" :class="sortBy==='DIAS_DESDE_ULTIMA_COMPRA'?'text-blue-600':'hover:text-slate-600'" class="flex items-center gap-1 transition-colors">
                                    Días <span x-text="arrow('DIAS_DESDE_ULTIMA_COMPRA')" class="text-slate-300"></span>
                                </button>
                                <button @click="sortOn('FACTURADO_ANIO_ANTERIOR')" :class="sortBy==='FACTURADO_ANIO_ANTERIOR'?'text-blue-600':'hover:text-slate-600'" class="flex items-center gap-1 transition-colors">
                                    Año ant. <span x-text="arrow('FACTURADO_ANIO_ANTERIOR')" class="text-slate-300"></span>
                                </button>
                            </div>
                        </div>

                        <div x-show="all.length === 0" class="py-10 text-center text-xs text-slate-400">Sin clientes para recuperar este año.</div>

                        <template x-for="(cli, idx) in paged" :key="idx">
                            <div :class="idx < paged.length-1 ? 'border-b border-slate-100' : ''" class="py-3 flex items-center gap-3">
                                <div class="w-1.5 h-8 rounded-full bg-blue-200 shrink-0"></div>
                                <div class="flex-1 min-w-0">
                                    <p class="text-sm font-semibold text-slate-900 truncate" x-text="cli.RAZON_SOCIAL"></p>
                                    <p class="text-xs text-slate-400 mt-0.5" x-text="(cli.NOMBRE_VENDEDOR||'') + ' · ' + (cli.CIUDAD||'')"></p>
                                    <p x-show="cli.ACCION_PRESUPUESTAL" class="text-xs text-slate-500 mt-0.5 italic" x-text="'→ ' + cli.ACCION_PRESUPUESTAL"></p>
                                </div>
                                <div class="text-right shrink-0">
                                    <p class="text-sm font-bold text-slate-800" x-text="fmt(cli.FACTURADO_ANIO_ANTERIOR)"></p>
                                    <p class="text-xs text-slate-400 mt-0.5" x-text="cli.DIAS_DESDE_ULTIMA_COMPRA + ' días'"></p>
                                </div>
                            </div>
                        </template>

                        <div x-show="pages > 1" class="flex items-center justify-between mt-3 pt-3 border-t border-slate-100">
                            <button @click="page > 1 && page--" :disabled="page===1" class="px-2.5 py-1.5 text-xs text-slate-500 hover:bg-slate-100 rounded-lg disabled:opacity-30 transition-colors">← Ant.</button>
                            <div class="flex gap-1">
                                <template x-for="p in pages" :key="p">
                                    <button @click="page=p" :class="page===p ? 'bg-blue-600 text-white shadow-sm' : 'bg-slate-100 text-slate-500 hover:bg-slate-200'" class="w-7 h-7 rounded-lg text-xs font-semibold transition-colors" x-text="p"></button>
                                </template>
                            </div>
                            <button @click="page < pages && page++" :disabled="page===pages" class="px-2.5 py-1.5 text-xs text-slate-500 hover:bg-slate-100 rounded-lg disabled:opacity-30 transition-colors">Sig. →</button>
                        </div>
                    </div>
                </x-ui.card>

                {{-- P4: Largo plazo --}}
                <x-ui.card x-data="erpCard(window._erpData.largoPlazo)">
                    <div class="h-1.5 w-full rounded-t-xl bg-gradient-to-r from-slate-400 to-slate-300"></div>
                    <div class="p-5">
                        <div class="flex items-start justify-between mb-1">
                            <div>
                                <div class="flex items-center gap-2">
                                    <span class="text-xs font-bold bg-slate-100 text-slate-600 border border-slate-200 px-1.5 py-0.5 rounded-md">P4</span>
                                    <h3 class="text-sm font-bold text-slate-900">Plan de Largo Plazo</h3>
                                </div>
                                <p class="text-xs text-slate-400 mt-0.5">No compran hace +1 año — no impactan meta actual</p>
                            </div>
                            <span class="text-xs bg-slate-100 text-slate-600 border border-slate-200 px-2 py-1 rounded-lg font-bold" x-text="all.length + ' clientes'"></span>
                        </div>

                        <div class="flex items-center text-xs font-semibold text-slate-400 py-2 mt-2 border-b border-slate-100 select-none gap-2">
                            <button @click="sortOn('RAZON_SOCIAL')" :class="sortBy==='RAZON_SOCIAL'?'text-slate-600':'hover:text-slate-600'" class="flex items-center gap-1 transition-colors">
                                Nombre <span x-text="arrow('RAZON_SOCIAL')" class="text-slate-300"></span>
                            </button>
                            <div class="ml-auto flex items-center gap-3">
                                <button @click="sortOn('DIAS_DESDE_ULTIMA_COMPRA')" :class="sortBy==='DIAS_DESDE_ULTIMA_COMPRA'?'text-slate-600':'hover:text-slate-600'" class="flex items-center gap-1 transition-colors">
                                    Días <span x-text="arrow('DIAS_DESDE_ULTIMA_COMPRA')" class="text-slate-300"></span>
                                </button>
                                <button @click="sortOn('VLR_NETO_FACTURADO')" :class="sortBy==='VLR_NETO_FACTURADO'?'text-slate-600':'hover:text-slate-600'" class="flex items-center gap-1 transition-colors">
                                    Histórico <span x-text="arrow('VLR_NETO_FACTURADO')" class="text-slate-300"></span>
                                </button>
                            </div>
                        </div>

                        <div x-show="all.length === 0" class="py-10 text-center text-xs text-slate-400">Sin clientes fuera de presupuesto.</div>

                        <template x-for="(cli, idx) in paged" :key="idx">
                            <div :class="idx < paged.length-1 ? 'border-b border-slate-100' : ''" class="py-3 flex items-center gap-3">
                                <div class="w-1.5 h-8 rounded-full bg-slate-200 shrink-0"></div>
                                <div class="flex-1 min-w-0">
                                    <p class="text-sm font-semibold text-slate-900 truncate" x-text="cli.RAZON_SOCIAL"></p>
                                    <p class="text-xs text-slate-400 mt-0.5" x-text="(cli.NOMBRE_VENDEDOR||'') + ' · ' + (cli.CIUDAD||'')"></p>
                                    <p x-show="cli.ACCION_PRESUPUESTAL" class="text-xs text-slate-500 mt-0.5 italic" x-text="'→ ' + cli.ACCION_PRESUPUESTAL"></p>
                                </div>
                                <div class="text-right shrink-0">
                                    <p class="text-sm font-bold text-slate-600" x-text="fmt(cli.VLR_NETO_FACTURADO)"></p>
                                    <p class="text-xs text-slate-400 mt-0.5" x-text="cli.DIAS_DESDE_ULTIMA_COMPRA + ' días'"></p>
                                </div>
                            </div>
                        </template>

                        <div x-show="pages > 1" class="flex items-center justify-between mt-3 pt-3 border-t border-slate-100">
                            <button @click="page > 1 && page--" :disabled="page===1" class="px-2.5 py-1.5 text-xs text-slate-500 hover:bg-slate-100 rounded-lg disabled:opacity-30 transition-colors">← Ant.</button>
                            <div class="flex gap-1">
                                <template x-for="p in pages" :key="p">
                                    <button @click="page=p" :class="page===p ? 'bg-slate-600 text-white shadow-sm' : 'bg-slate-100 text-slate-500 hover:bg-slate-200'" class="w-7 h-7 rounded-lg text-xs font-semibold transition-colors" x-text="p"></button>
                                </template>
                            </div>
                            <button @click="page < pages && page++" :disabled="page===pages" class="px-2.5 py-1.5 text-xs text-slate-500 hover:bg-slate-100 rounded-lg disabled:opacity-30 transition-colors">Sig. →</button>
                        </div>
                    </div>
                </x-ui.card>

            </div>

            {{-- Q5: Panorama Presupuestal Gerencial --}}
            @if(!empty($intel['panorama_presupuestal']))
            <x-ui.card x-data="sortableTable(window._tableData.panoramaPresupuestal)">
                <div class="h-1.5 w-full rounded-t-xl bg-gradient-to-r from-teal-600 to-teal-400"></div>
                <div class="p-5">
                    <div class="flex items-center justify-between mb-4">
                        <div>
                            <h3 class="text-sm font-bold text-slate-900">Panorama Presupuestal por Vendedor</h3>
                            <p class="text-xs text-slate-400 mt-0.5">Facturación actual vs. año anterior · distribución de cartera por horizonte</p>
                        </div>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full text-xs">
                            <thead>
                                <tr class="border-b border-slate-100 bg-slate-50/70">
                                    <th @click="sortOn('NOMBRE_VENDEDOR')" :class="sortBy==='NOMBRE_VENDEDOR' ? 'text-teal-700' : 'text-slate-500'" class="text-left py-2.5 pr-3 pl-3 font-semibold cursor-pointer select-none hover:bg-slate-100 transition-colors">
                                        <span class="flex items-center gap-1">Vendedor <span x-text="arrow('NOMBRE_VENDEDOR')" class="opacity-50"></span></span>
                                    </th>
                                    <th @click="sortOn('facturado_anio_actual')" :class="sortBy==='facturado_anio_actual' ? 'text-teal-800' : 'text-teal-600'" class="text-right py-2.5 px-2 font-semibold cursor-pointer select-none hover:bg-slate-100 transition-colors">
                                        <span class="flex items-center justify-end gap-1">Año actual <span x-text="arrow('facturado_anio_actual')" class="opacity-50"></span></span>
                                    </th>
                                    <th @click="sortOn('facturado_anio_anterior')" :class="sortBy==='facturado_anio_anterior' ? 'text-slate-800' : 'text-slate-500'" class="text-right py-2.5 px-2 font-semibold cursor-pointer select-none hover:bg-slate-100 transition-colors">
                                        <span class="flex items-center justify-end gap-1">Año ant. <span x-text="arrow('facturado_anio_anterior')" class="opacity-50"></span></span>
                                    </th>
                                    <th @click="sortOn('variacion_porc')" :class="sortBy==='variacion_porc' ? 'text-slate-800' : 'text-slate-500'" class="text-right py-2.5 px-2 font-semibold cursor-pointer select-none hover:bg-slate-100 transition-colors">
                                        <span class="flex items-center justify-end gap-1">Var.% <span x-text="arrow('variacion_porc')" class="opacity-50"></span></span>
                                    </th>
                                    <th @click="sortOn('p1_activos')" :class="sortBy==='p1_activos' ? 'text-emerald-700' : 'text-emerald-600'" class="text-center py-2.5 px-2 font-semibold cursor-pointer select-none hover:bg-slate-100 transition-colors">
                                        <span class="flex items-center justify-center gap-1">P1 <span x-text="arrow('p1_activos')" class="opacity-50"></span></span>
                                    </th>
                                    <th @click="sortOn('p2_en_riesgo')" :class="sortBy==='p2_en_riesgo' ? 'text-orange-700' : 'text-orange-600'" class="text-center py-2.5 px-2 font-semibold cursor-pointer select-none hover:bg-slate-100 transition-colors">
                                        <span class="flex items-center justify-center gap-1">P2 <span x-text="arrow('p2_en_riesgo')" class="opacity-50"></span></span>
                                    </th>
                                    <th @click="sortOn('p3_recuperar')" :class="sortBy==='p3_recuperar' ? 'text-blue-700' : 'text-blue-600'" class="text-center py-2.5 px-2 font-semibold cursor-pointer select-none hover:bg-slate-100 transition-colors">
                                        <span class="flex items-center justify-center gap-1">P3 <span x-text="arrow('p3_recuperar')" class="opacity-50"></span></span>
                                    </th>
                                    <th @click="sortOn('p4_largo_plazo')" :class="sortBy==='p4_largo_plazo' ? 'text-slate-700' : 'text-slate-500'" class="text-center py-2.5 px-2 font-semibold cursor-pointer select-none hover:bg-slate-100 transition-colors">
                                        <span class="flex items-center justify-center gap-1">P4 <span x-text="arrow('p4_largo_plazo')" class="opacity-50"></span></span>
                                    </th>
                                    <th @click="sortOn('valor_a_rescatar_p2')" :class="sortBy==='valor_a_rescatar_p2' ? 'text-orange-700' : 'text-orange-600'" class="text-right py-2.5 px-2 font-semibold cursor-pointer select-none hover:bg-slate-100 transition-colors">
                                        <span class="flex items-center justify-end gap-1">Rescatar P2 <span x-text="arrow('valor_a_rescatar_p2')" class="opacity-50"></span></span>
                                    </th>
                                    <th @click="sortOn('valor_potencial_p3')" :class="sortBy==='valor_potencial_p3' ? 'text-blue-700' : 'text-blue-600'" class="text-right py-2.5 pl-2 pr-3 font-semibold cursor-pointer select-none hover:bg-slate-100 transition-colors">
                                        <span class="flex items-center justify-end gap-1">Potencial P3 <span x-text="arrow('valor_potencial_p3')" class="opacity-50"></span></span>
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-50">
                                <template x-for="(fila, idx) in sorted" :key="idx">
                                    <tr class="hover:bg-slate-50 transition-colors">
                                        <td class="py-2.5 pr-3 pl-3 font-semibold text-slate-800 whitespace-nowrap">
                                            <span x-text="fila.NOMBRE_VENDEDOR"></span>
                                            <span x-show="fila.COMPANIA" class="text-slate-400 font-normal" x-text="' · Cía ' + (fila.COMPANIA||'')"></span>
                                        </td>
                                        <td class="text-right py-2.5 px-2 font-bold text-teal-700 whitespace-nowrap" x-text="fmt(fila.facturado_anio_actual)"></td>
                                        <td class="text-right py-2.5 px-2 text-slate-500 whitespace-nowrap" x-text="fmt(fila.facturado_anio_anterior)"></td>
                                        <td class="text-right py-2.5 px-2"
                                            :class="(parseFloat(fila.variacion_porc)||0) >= 0 ? 'text-emerald-600 font-bold' : 'text-red-600 font-bold'"
                                            x-text="((parseFloat(fila.variacion_porc)||0) >= 0 ? '+' : '') + (parseFloat(fila.variacion_porc)||0).toFixed(1) + '%'"></td>
                                        <td class="text-center py-2.5 px-2">
                                            <span class="bg-emerald-100 text-emerald-700 font-bold px-1.5 py-0.5 rounded" x-text="fila.p1_activos"></span>
                                        </td>
                                        <td class="text-center py-2.5 px-2">
                                            <span class="bg-orange-100 text-orange-700 font-bold px-1.5 py-0.5 rounded" x-text="fila.p2_en_riesgo"></span>
                                        </td>
                                        <td class="text-center py-2.5 px-2">
                                            <span class="bg-blue-100 text-blue-700 font-bold px-1.5 py-0.5 rounded" x-text="fila.p3_recuperar"></span>
                                        </td>
                                        <td class="text-center py-2.5 px-2">
                                            <span class="bg-slate-100 text-slate-600 font-bold px-1.5 py-0.5 rounded" x-text="fila.p4_largo_plazo"></span>
                                        </td>
                                        <td class="text-right py-2.5 px-2 text-orange-600 font-semibold whitespace-nowrap" x-text="fmt(fila.valor_a_rescatar_p2)"></td>
                                        <td class="text-right py-2.5 pl-2 pr-3 text-blue-600 font-semibold whitespace-nowrap" x-text="fmt(fila.valor_potencial_p3)"></td>
                                    </tr>
                                </template>
                            </tbody>
                        </table>
                    </div>
                </div>
            </x-ui.card>
            @endif

        </div>
        @endif

        {{-- ── TAB: VISIÓN GERENCIAL ── --}}
        @if($tieneGer)
        <div x-show="tab === 'gerencial'"
             x-transition:enter="transition ease-out duration-150"
             x-transition:enter-start="opacity-0 translate-y-1"
             x-transition:enter-end="opacity-100 translate-y-0">

            @if(!empty($intel['integrales']))
            <x-ui.card class="mb-4" x-data="sortableTable(window._tableData.integrales)">
                <div class="h-1.5 w-full rounded-t-xl bg-gradient-to-r from-purple-500 to-violet-500"></div>
                <div class="p-5">
                    <div class="flex items-center justify-between mb-4">
                        <div>
                            <h3 class="text-sm font-bold text-slate-900">Oportunidades de Venta Integral</h3>
                            <p class="text-xs text-slate-400 mt-0.5">Perfil de compra entre compañías · clientes VIP y urgentes</p>
                        </div>
                        <span class="text-xs bg-purple-100 text-purple-700 border border-purple-200 px-2.5 py-1 rounded-lg font-bold">{{ count($intel['integrales']) }} clientes</span>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full text-xs">
                            <thead>
                                <tr class="border-b border-slate-100 bg-slate-50/70">
                                    <th @click="sortOn('RAZON_SOCIAL')" :class="sortBy==='RAZON_SOCIAL' ? 'text-purple-700' : 'text-slate-500'" class="text-left py-2.5 pr-3 pl-3 font-semibold rounded-tl-lg cursor-pointer select-none hover:bg-slate-100 transition-colors">
                                        <span class="flex items-center gap-1">Cliente <span x-text="arrow('RAZON_SOCIAL')" class="opacity-50"></span></span>
                                    </th>
                                    <th @click="sortOn('NOMBRE_VENDEDOR')" :class="sortBy==='NOMBRE_VENDEDOR' ? 'text-purple-700' : 'text-slate-500'" class="text-left py-2.5 px-2 font-semibold cursor-pointer select-none hover:bg-slate-100 transition-colors">
                                        <span class="flex items-center gap-1">Vendedor <span x-text="arrow('NOMBRE_VENDEDOR')" class="opacity-50"></span></span>
                                    </th>
                                    <th @click="sortOn('HORIZONTE_PRESUPUESTO')" :class="sortBy==='HORIZONTE_PRESUPUESTO' ? 'text-purple-700' : 'text-slate-500'" class="text-center py-2.5 px-2 font-semibold cursor-pointer select-none hover:bg-slate-100 transition-colors">
                                        <span class="flex items-center justify-center gap-1">Tipo <span x-text="arrow('HORIZONTE_PRESUPUESTO')" class="opacity-50"></span></span>
                                    </th>
                                    <th @click="sortOn('perfil_cruzado')" :class="sortBy==='perfil_cruzado' ? 'text-purple-700' : 'text-purple-600'" class="text-center py-2.5 px-2 font-semibold cursor-pointer select-none hover:bg-slate-100 transition-colors">
                                        <span class="flex items-center justify-center gap-1">Perfil cruzado <span x-text="arrow('perfil_cruzado')" class="opacity-50"></span></span>
                                    </th>
                                    <th @click="sortOn('VLR_NETO_FACTURADO')" :class="sortBy==='VLR_NETO_FACTURADO' ? 'text-slate-800' : 'text-slate-500'" class="text-right py-2.5 pr-3 pl-3 font-semibold rounded-tr-lg cursor-pointer select-none hover:bg-slate-100 transition-colors">
                                        <span class="flex items-center justify-end gap-1">Facturado <span x-text="arrow('VLR_NETO_FACTURADO')" class="opacity-50"></span></span>
                                    </th>
                                    <th @click="sortOn('DIAS_DESDE_ULTIMA_COMPRA')" :class="sortBy==='DIAS_DESDE_ULTIMA_COMPRA' ? 'text-slate-800' : 'text-slate-500'" class="text-right py-2.5 pl-2 font-semibold cursor-pointer select-none hover:bg-slate-100 transition-colors">
                                        <span class="flex items-center justify-end gap-1">Días <span x-text="arrow('DIAS_DESDE_ULTIMA_COMPRA')" class="opacity-50"></span></span>
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-50">
                                <template x-for="(cli, idx) in sorted" :key="idx">
                                    <tr class="hover:bg-slate-50 transition-colors">
                                        <td class="py-2.5 pr-3 pl-3">
                                            <p class="font-semibold text-slate-900" x-text="cli.RAZON_SOCIAL"></p>
                                            <p class="text-slate-400" x-text="cli.CIUDAD"></p>
                                        </td>
                                        <td class="py-2.5 px-2 text-slate-500 whitespace-nowrap" x-text="cli.NOMBRE_VENDEDOR"></td>
                                        <td class="py-2.5 px-2 text-center">
                                            <span class="border px-1.5 py-0.5 rounded-md font-bold"
                                                  :class="(cli.HORIZONTE_PRESUPUESTO||'').startsWith('P1') ? 'bg-emerald-100 text-emerald-700 border-emerald-200' : 'bg-orange-100 text-orange-700 border-orange-200'"
                                                  x-text="(cli.HORIZONTE_PRESUPUESTO||'').startsWith('P1') ? 'P1 Activo' : 'P2 Riesgo'"></span>
                                        </td>
                                        <td class="py-2.5 px-2 text-center">
                                            <span class="border px-1.5 py-0.5 rounded-md font-semibold"
                                                  :class="cli.perfil_cruzado === 'YA INTEGRAL' ? 'bg-purple-100 text-purple-700 border-purple-200' : ((cli.perfil_cruzado||'').includes('COMPAÑÍA 1') ? 'bg-sky-100 text-sky-700 border-sky-200' : ((cli.perfil_cruzado||'').includes('COMPAÑÍA 2') ? 'bg-teal-100 text-teal-700 border-teal-200' : 'bg-slate-100 text-slate-500 border-slate-200'))"
                                                  x-text="cli.perfil_cruzado"></span>
                                        </td>
                                        <td class="py-2.5 pl-3 pr-3 text-right font-bold text-slate-700 whitespace-nowrap" x-text="fmt(cli.VLR_NETO_FACTURADO)"></td>
                                        <td class="py-2.5 pl-2 text-right font-semibold whitespace-nowrap"
                                            :class="(parseInt(cli.DIAS_DESDE_ULTIMA_COMPRA)||0) > 60 ? 'text-red-600' : 'text-slate-500'"
                                            x-text="cli.DIAS_DESDE_ULTIMA_COMPRA ?? '-'"></td>
                                    </tr>
                                </template>
                            </tbody>
                        </table>
                    </div>
                    @php $yaIntegrales = collect($intel['integrales'])->where('perfil_cruzado', 'YA INTEGRAL')->count(); @endphp
                    @if(count($intel['integrales']) - $yaIntegrales > 0)
                    <div class="flex items-center gap-4 mt-4 pt-3 border-t border-slate-100">
                        <div class="flex items-center gap-1.5">
                            <span class="w-2 h-2 rounded-full bg-purple-400"></span>
                            <span class="text-xs text-slate-500"><span class="font-bold text-purple-700">{{ count($intel['integrales']) - $yaIntegrales }}</span> con potencial cross-selling</span>
                        </div>
                        <div class="flex items-center gap-1.5">
                            <span class="w-2 h-2 rounded-full bg-slate-300"></span>
                            <span class="text-xs text-slate-500"><span class="font-bold text-slate-700">{{ $yaIntegrales }}</span> ya compran en ambas compañías</span>
                        </div>
                    </div>
                    @endif
                </div>
            </x-ui.card>
            @endif

            @if(!empty($intel['panorama_gerencial']))
            <x-ui.card x-data="sortableTable(window._tableData.panoramaGerencial)">
                <div class="h-1.5 w-full rounded-t-xl bg-gradient-to-r from-slate-500 to-slate-400"></div>
                <div class="p-5">
                    <div class="flex items-center justify-between mb-4">
                        <div>
                            <h3 class="text-sm font-bold text-slate-900">Panorama Gerencial</h3>
                            <p class="text-xs text-slate-400 mt-0.5">Clasificación de cartera por vendedor</p>
                        </div>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full text-xs">
                            <thead>
                                <tr class="border-b border-slate-100 bg-slate-50/70">
                                    <th @click="sortOn('NOMBRE_VENDEDOR')" :class="sortBy==='NOMBRE_VENDEDOR' ? 'text-slate-800' : 'text-slate-500'" class="text-left py-2.5 pr-4 pl-3 font-semibold cursor-pointer select-none hover:bg-slate-100 transition-colors">
                                        <span class="flex items-center gap-1">Vendedor <span x-text="arrow('NOMBRE_VENDEDOR')" class="opacity-50"></span></span>
                                    </th>
                                    <th @click="sortOn('total_clientes')" :class="sortBy==='total_clientes' ? 'text-slate-800' : 'text-slate-500'" class="text-center py-2.5 px-2 font-semibold cursor-pointer select-none hover:bg-slate-100 transition-colors">
                                        <span class="flex items-center justify-center gap-1">Total <span x-text="arrow('total_clientes')" class="opacity-50"></span></span>
                                    </th>
                                    <th @click="sortOn('vip_activos')" :class="sortBy==='vip_activos' ? 'text-emerald-700' : 'text-emerald-600'" class="text-center py-2.5 px-2 font-semibold cursor-pointer select-none hover:bg-slate-100 transition-colors">
                                        <span class="flex items-center justify-center gap-1">VIP <span x-text="arrow('vip_activos')" class="opacity-50"></span></span>
                                    </th>
                                    <th @click="sortOn('urgentes')" :class="sortBy==='urgentes' ? 'text-amber-700' : 'text-amber-600'" class="text-center py-2.5 px-2 font-semibold cursor-pointer select-none hover:bg-slate-100 transition-colors">
                                        <span class="flex items-center justify-center gap-1">Urgente <span x-text="arrow('urgentes')" class="opacity-50"></span></span>
                                    </th>
                                    <th @click="sortOn('rescate')" :class="sortBy==='rescate' ? 'text-red-600' : 'text-red-500'" class="text-center py-2.5 px-2 font-semibold cursor-pointer select-none hover:bg-slate-100 transition-colors">
                                        <span class="flex items-center justify-center gap-1">Rescate <span x-text="arrow('rescate')" class="opacity-50"></span></span>
                                    </th>
                                    <th @click="sortOn('reactivacion')" :class="sortBy==='reactivacion' ? 'text-indigo-600' : 'text-indigo-500'" class="text-center py-2.5 px-2 font-semibold cursor-pointer select-none hover:bg-slate-100 transition-colors">
                                        <span class="flex items-center justify-center gap-1">Reactiv. <span x-text="arrow('reactivacion')" class="opacity-50"></span></span>
                                    </th>
                                    <th @click="sortOn('facturacion_total')" :class="sortBy==='facturacion_total' ? 'text-slate-800' : 'text-slate-500'" class="text-right py-2.5 pl-4 font-semibold cursor-pointer select-none hover:bg-slate-100 transition-colors">
                                        <span class="flex items-center justify-end gap-1">Facturación <span x-text="arrow('facturacion_total')" class="opacity-50"></span></span>
                                    </th>
                                    <th @click="sortOn('valor_en_riesgo')" :class="sortBy==='valor_en_riesgo' ? 'text-slate-800' : 'text-slate-500'" class="text-right py-2.5 pl-3 font-semibold cursor-pointer select-none hover:bg-slate-100 transition-colors">
                                        <span class="flex items-center justify-end gap-1">En riesgo <span x-text="arrow('valor_en_riesgo')" class="opacity-50"></span></span>
                                    </th>
                                    <th @click="sortOn('porc_riesgo')" :class="sortBy==='porc_riesgo' ? 'text-slate-800' : 'text-slate-500'" class="text-right py-2.5 pl-2 pr-3 font-semibold cursor-pointer select-none hover:bg-slate-100 transition-colors">
                                        <span class="flex items-center justify-end gap-1">% Riesgo <span x-text="arrow('porc_riesgo')" class="opacity-50"></span></span>
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-50">
                                <template x-for="(fila, idx) in sorted" :key="idx">
                                    <tr class="hover:bg-slate-50 transition-colors">
                                        <td class="py-2.5 pr-4 pl-3 font-semibold text-slate-800 whitespace-nowrap">
                                            <span x-text="fila.NOMBRE_VENDEDOR"></span>
                                            <span x-show="fila.COMPANIA" class="text-slate-400 font-normal" x-text="' · Cía ' + (fila.COMPANIA||'')"></span>
                                        </td>
                                        <td class="text-center py-2.5 px-2 text-slate-600 font-medium" x-text="fila.total_clientes"></td>
                                        <td class="text-center py-2.5 px-2">
                                            <span class="text-emerald-700 font-bold bg-emerald-50 px-1.5 py-0.5 rounded" x-text="fila.vip_activos"></span>
                                        </td>
                                        <td class="text-center py-2.5 px-2">
                                            <span class="text-amber-700 font-bold bg-amber-50 px-1.5 py-0.5 rounded" x-text="fila.urgentes"></span>
                                        </td>
                                        <td class="text-center py-2.5 px-2">
                                            <span class="text-red-600 font-bold bg-red-50 px-1.5 py-0.5 rounded" x-text="fila.rescate"></span>
                                        </td>
                                        <td class="text-center py-2.5 px-2">
                                            <span class="text-indigo-600 font-bold bg-indigo-50 px-1.5 py-0.5 rounded" x-text="fila.reactivacion"></span>
                                        </td>
                                        <td class="text-right py-2.5 pl-4 text-slate-600 whitespace-nowrap font-medium" x-text="fmt(fila.facturacion_total)"></td>
                                        <td class="text-right py-2.5 pl-3 text-amber-600 whitespace-nowrap font-semibold" x-text="fmt(fila.valor_en_riesgo)"></td>
                                        <td class="text-right py-2.5 pl-2 pr-3"
                                            :class="(parseFloat(fila.porc_riesgo)||0) >= 40 ? 'text-red-600 font-bold' : ((parseFloat(fila.porc_riesgo)||0) >= 20 ? 'text-amber-600 font-semibold' : 'text-slate-500')"
                                            x-text="(parseFloat(fila.porc_riesgo)||0).toFixed(1) + '%'"></td>
                                    </tr>
                                </template>
                            </tbody>
                        </table>
                    </div>
                </div>
            </x-ui.card>
            @endif

        </div>
        @endif

    </div>{{-- fin tabs --}}

    @else
    <div class="rounded-2xl bg-slate-50 border border-dashed border-slate-300 p-10 text-center">
        <div class="w-12 h-12 rounded-2xl bg-slate-200 flex items-center justify-center mx-auto mb-3">
            <svg class="w-6 h-6 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 104 0 2 2 0 012-2h1.064M15 20.488V18a2 2 0 012-2h3.064"/>
            </svg>
        </div>
        <p class="text-sm font-semibold text-slate-600">ERP Contiflex no disponible</p>
        <p class="text-xs text-slate-400 mt-1">Los datos de inteligencia comercial no están accesibles en este momento.</p>
    </div>
    @endif

    @endisset
</x-layouts.app>
