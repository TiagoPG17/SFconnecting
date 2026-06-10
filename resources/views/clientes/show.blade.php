<x-layouts.app :title="$cliente->razon_social">
    <x-slot name="actions">
        <x-ui.button href="{{ route('clientes.index') }}" variant="ghost" size="sm">
            <x-ui.icon name="arrow-left" class="w-4 h-4"/> Volver
        </x-ui.button>
        @can('update', $cliente)
        <x-ui.button href="{{ route('clientes.edit', $cliente) }}" variant="secondary" size="sm">
            <x-ui.icon name="edit" class="w-4 h-4"/> Editar
        </x-ui.button>
        @endcan
        <x-ui.button variant="primary" size="sm" @click="$dispatch('open-seguimiento')">
            <x-ui.icon name="plus" class="w-4 h-4"/> Seguimiento
        </x-ui.button>
    </x-slot>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        {{-- Columna principal --}}
        <div class="lg:col-span-2 space-y-6">

            {{-- Datos del cliente --}}
            <x-ui.card>
                <div class="p-6">
                    <div class="flex items-start justify-between mb-4">
                        <div>
                            <h2 class="text-xl font-semibold text-slate-900">{{ $cliente->razon_social }}</h2>
                            <p class="text-sm font-mono text-slate-500 mt-1">NIT: {{ $cliente->nit }}</p>
                        </div>
                        @php
                            $colorEstado = match($cliente->estado) {
                                'activo'   => 'green',
                                'inactivo' => 'red',
                                default    => 'yellow',
                            };
                        @endphp
                        <x-ui.badge :color="$colorEstado" class="text-sm px-3 py-1">
                            {{ ucfirst($cliente->estado) }}
                        </x-ui.badge>
                    </div>

                    <dl class="grid grid-cols-2 gap-4 text-sm">
                        @if($cliente->email)
                        <div>
                            <dt class="text-slate-500 font-medium">Email</dt>
                            <dd class="text-slate-900 mt-1">{{ $cliente->email }}</dd>
                        </div>
                        @endif
                        @if($cliente->telefono)
                        <div>
                            <dt class="text-slate-500 font-medium">Teléfono</dt>
                            <dd class="text-slate-900 mt-1">{{ $cliente->telefono }}</dd>
                        </div>
                        @endif
                        @if($cliente->ciudad)
                        <div>
                            <dt class="text-slate-500 font-medium">Ciudad</dt>
                            <dd class="text-slate-900 mt-1">{{ $cliente->ciudad }}</dd>
                        </div>
                        @endif
                        @if($cliente->direccion)
                        <div>
                            <dt class="text-slate-500 font-medium">Dirección</dt>
                            <dd class="text-slate-900 mt-1">{{ $cliente->direccion }}</dd>
                        </div>
                        @endif
                        <div>
                            <dt class="text-slate-500 font-medium">Asesor</dt>
                            <dd class="text-slate-900 mt-1">{{ $cliente->asesor?->name ?? '—' }}</dd>
                        </div>
                        <div>
                            <dt class="text-slate-500 font-medium">Cliente desde</dt>
                            <dd class="text-slate-900 mt-1">{{ $cliente->created_at->format('d/m/Y') }}</dd>
                        </div>
                    </dl>

                    @if($cliente->notas)
                    <div class="mt-4 pt-4 border-t border-slate-100">
                        <p class="text-sm font-medium text-slate-500 mb-1">Notas</p>
                        <p class="text-sm text-slate-700">{{ $cliente->notas }}</p>
                    </div>
                    @endif
                </div>
            </x-ui.card>

            {{-- Datos SIESA (solo lectura) --}}
            @if(!empty($datosErp))
            @php
                $calificacion = $datosErp['CALIFICACION_CLIENTE'] ?? null;
                $calColor = match($calificacion) {
                    'A' => 'green', 'B' => 'yellow', 'C' => 'red', default => 'gray'
                };
                $cupo = (float) ($datosErp['CUPO_CREDITO'] ?? 0);

                $tieneDatosSiesa = $calificacion
                    || !empty($datosErp['TIPO_CLIENTE'])
                    || !empty($datosErp['TIPO_TERCERO'])
                    || !empty($datosErp['DESC_CONDICION_PAGO'])
                    || $cupo > 0
                    || !empty($datosErp['DESC_LISTA_PRECIOS'])
                    || !empty($datosErp['DEPARTAMENTO']);
            @endphp
            @if($tieneDatosSiesa)
            <x-ui.card>
                <div class="p-4 border-b border-slate-100 flex items-center gap-2">
                    <div class="w-2 h-2 rounded-full bg-emerald-500"></div>
                    <h3 class="text-sm font-semibold text-slate-900">Datos SIESA</h3>
                    <span class="text-xs text-slate-400">solo lectura</span>
                </div>
                <div class="p-4">
                    <dl class="grid grid-cols-2 sm:grid-cols-3 gap-3 text-sm">

                        @if($calificacion)
                        <div class="bg-slate-50 rounded-lg p-3">
                            <dt class="text-xs text-slate-500 font-medium mb-1">Calificación</dt>
                            <dd><x-ui.badge :color="$calColor" class="text-sm font-bold">{{ $calificacion }}</x-ui.badge></dd>
                        </div>
                        @endif

                        @if(!empty($datosErp['TIPO_CLIENTE']))
                        <div class="bg-slate-50 rounded-lg p-3">
                            <dt class="text-xs text-slate-500 font-medium mb-1">Tipo de cliente</dt>
                            <dd class="text-sm font-medium text-slate-800">{{ ucwords(strtolower($datosErp['TIPO_CLIENTE'])) }}</dd>
                        </div>
                        @endif

                        @if(!empty($datosErp['TIPO_TERCERO']))
                        <div class="bg-slate-50 rounded-lg p-3">
                            <dt class="text-xs text-slate-500 font-medium mb-1">Tipo de persona</dt>
                            <dd class="text-sm font-medium text-slate-800">{{ $datosErp['TIPO_TERCERO'] }}</dd>
                        </div>
                        @endif

                        @if(!empty($datosErp['DESC_CONDICION_PAGO']))
                        <div class="bg-slate-50 rounded-lg p-3">
                            <dt class="text-xs text-slate-500 font-medium mb-1">Condición de pago</dt>
                            <dd class="text-sm font-medium text-slate-800">{{ $datosErp['DESC_CONDICION_PAGO'] }}</dd>
                        </div>
                        @endif

                        @if($cupo > 0)
                        <div class="bg-slate-50 rounded-lg p-3">
                            <dt class="text-xs text-slate-500 font-medium mb-1">Cupo de crédito</dt>
                            <dd class="text-sm font-bold text-slate-900">${{ number_format($cupo, 0, ',', '.') }}</dd>
                        </div>
                        @endif

                        @if(!empty($datosErp['DESC_LISTA_PRECIOS']))
                        @php
                            $listaPrecios = ucwords(strtolower(
                                preg_replace('/^lista\s+de\s+precios\s*/i', '', trim($datosErp['DESC_LISTA_PRECIOS']))
                            ));
                        @endphp
                        <div class="bg-slate-50 rounded-lg p-3">
                            <dt class="text-xs text-slate-500 font-medium mb-1">Lista de precios</dt>
                            <dd class="text-sm font-medium text-slate-800">{{ $listaPrecios }}</dd>
                        </div>
                        @endif

                        @if(!empty($datosErp['DEPARTAMENTO']))
                        <div class="bg-slate-50 rounded-lg p-3">
                            <dt class="text-xs text-slate-500 font-medium mb-1">Departamento</dt>
                            <dd class="text-sm font-medium text-slate-800">{{ $datosErp['DEPARTAMENTO'] }}</dd>
                        </div>
                        @endif


                    </dl>
                </div>
            </x-ui.card>
            @endif {{-- tieneDatosSiesa --}}
            @endif {{-- datosErp --}}

            {{-- Contactos --}}
            <x-ui.card>
                <div class="p-4 border-b border-slate-100 flex items-center justify-between">
                    <h3 class="text-sm font-semibold text-slate-900">Contactos</h3>
                    <x-ui.button variant="ghost" size="xs" @click="$dispatch('open-contacto')">
                        <x-ui.icon name="plus" class="w-4 h-4"/> Añadir
                    </x-ui.button>
                </div>
                @if($cliente->contactos->isEmpty())
                    <div class="p-6 text-center text-sm text-slate-400">Sin contactos registrados</div>
                @else
                <div class="divide-y divide-slate-100">
                    @foreach($cliente->contactos as $contacto)
                    <div class="flex items-center gap-3 px-4 py-3">
                        <div class="w-8 h-8 rounded-full bg-slate-100 flex items-center justify-center text-xs font-semibold text-slate-600">
                            {{ substr($contacto->nombre, 0, 2) }}
                        </div>
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center gap-2">
                                <p class="text-sm font-medium text-slate-900">{{ $contacto->nombre }}</p>
                                @if($contacto->principal)
                                    <x-ui.badge color="blue" class="text-xs">Principal</x-ui.badge>
                                @endif
                            </div>
                            @if($contacto->cargo)
                                <p class="text-xs text-slate-500">{{ $contacto->cargo }}</p>
                            @endif
                        </div>
                        <div class="text-right">
                            @if($contacto->email)
                                <p class="text-xs text-slate-600">{{ $contacto->email }}</p>
                            @endif
                            @if($contacto->telefono)
                                <p class="text-xs text-slate-400">{{ $contacto->telefono }}</p>
                            @endif
                        </div>
                    </div>
                    @endforeach
                </div>
                @endif
            </x-ui.card>

            {{-- Timeline seguimientos --}}
            <x-ui.card>
                <div class="p-4 border-b border-slate-100 flex items-center justify-between">
                    <h3 class="text-sm font-semibold text-slate-900">Seguimientos</h3>
                    <a href="{{ route('seguimientos.index', ['cliente_id' => $cliente->id]) }}"
                        class="text-xs text-blue-600 hover:underline">Ver todos</a>
                </div>
                @if($seguimientos->isEmpty())
                    <div class="p-6 text-center text-sm text-slate-400">Sin seguimientos registrados</div>
                @else
                <div class="divide-y divide-slate-100">
                    @foreach($seguimientos as $seg)
                    <div class="px-4 py-3">
                        <div class="flex items-start justify-between gap-3">
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center gap-2 mb-1">
                                    <x-ui.badge color="blue" class="text-xs">{{ $seg->tipo }}</x-ui.badge>
                                    <x-ui.badge color="{{ $seg->resultado === 'exitoso' ? 'green' : ($seg->resultado === 'fallido' ? 'red' : 'yellow') }}" class="text-xs">
                                        {{ ucfirst($seg->resultado) }}
                                    </x-ui.badge>
                                </div>
                                <p class="text-sm text-slate-700">{{ $seg->descripcion }}</p>
                                @if($seg->proxima_fecha)
                                    <p class="text-xs text-slate-500 mt-1">
                                        Próximo: {{ $seg->proxima_fecha->format('d/m/Y H:i') }}
                                    </p>
                                @endif
                            </div>
                            <div class="text-right shrink-0">
                                <p class="text-xs text-slate-400">{{ $seg->fecha_seguimiento->format('d/m/Y') }}</p>
                                <p class="text-xs text-slate-500">{{ $seg->asesor?->name }}</p>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
                @endif
            </x-ui.card>

            {{-- Gráfica de actividad / ventas SIESA --}}
            @php
                $usarSiesa     = !empty($actividadSiesa['mensual'] ?? []);
                $chartDatasets = $usarSiesa ? $actividadSiesa : $actividad;
                $chartTipo     = $usarSiesa ? 'ventas' : 'seguimientos';
            @endphp
            <script>window['_chart_{{ $cliente->id }}'] = {!! json_encode($chartDatasets) !!};</script>

            <x-ui.card x-data="actividadChart(window['_chart_{{ $cliente->id }}'], '{{ $chartTipo }}')">
                <div class="p-4">
                    <div class="flex items-center justify-between mb-3">
                        <p class="text-xs font-semibold text-slate-500 uppercase tracking-wide">
                            {{ $usarSiesa ? 'Ventas SIESA' : 'Actividad' }}
                        </p>
                        <div class="flex gap-0.5 bg-slate-100 rounded-lg p-0.5">
                            @foreach(['mensual' => 'Mes', 'trimestral' => 'Trim.', 'anual' => 'Año'] as $key => $lbl)
                            <button @click="periodo = '{{ $key }}'; hovered = null"
                                    :class="periodo === '{{ $key }}' ? 'bg-white shadow-sm text-slate-900' : 'text-slate-500 hover:text-slate-700'"
                                    class="px-2 py-0.5 rounded-md text-xs font-medium transition-all">
                                {{ $lbl }}
                            </button>
                            @endforeach
                        </div>
                    </div>

                    {{-- Tooltip --}}
                    <div class="h-5 mb-1 flex items-center justify-center">
                        <template x-if="hovered !== null">
                            <span class="text-xs font-semibold text-blue-600"
                                  x-text="puntos[hovered]?.label + ' · ' + formatTotal(puntos[hovered]?.total)">
                            </span>
                        </template>
                    </div>

                    {{-- SVG sin x-for + overlay HTML --}}
                    <div class="relative w-full" style="padding-bottom: 18%">
                        <svg viewBox="0 0 800 170" preserveAspectRatio="none"
                             class="absolute inset-0 w-full h-full" style="overflow:visible">
                            <defs>
                                <linearGradient id="act-grad-{{ $cliente->id }}" x1="0" y1="0" x2="0" y2="1">
                                    <stop offset="0%" stop-color="#3b82f6" stop-opacity="0.2"/>
                                    <stop offset="100%" stop-color="#3b82f6" stop-opacity="0"/>
                                </linearGradient>
                            </defs>
                            <line x1="10" y1="158" x2="790" y2="158" stroke="#e2e8f0" stroke-width="1"/>
                            <line x1="10" y1="87"  x2="790" y2="87"  stroke="#e2e8f0" stroke-width="0.75" stroke-dasharray="4 3"/>
                            <line x1="10" y1="16"  x2="790" y2="16"  stroke="#e2e8f0" stroke-width="0.75" stroke-dasharray="4 3"/>
                            <path :d="area" :fill="'url(#act-grad-{{ $cliente->id }})'" :opacity="hayDatos ? 1 : 0"/>
                            <polyline :points="linea" fill="none" stroke="#3b82f6" stroke-width="2"
                                      stroke-linejoin="round" stroke-linecap="round" :opacity="hayDatos ? 1 : 0"/>
                            <path :d="dotsPath" fill="white" stroke="#3b82f6" stroke-width="2" :opacity="hayDatos ? 1 : 0"/>
                            <line :x1="hovered !== null ? puntos[hovered]?.x : -999"
                                  :x2="hovered !== null ? puntos[hovered]?.x : -999"
                                  y1="16" :y2="hovered !== null ? (puntos[hovered]?.y ?? 0) - 7 : 0"
                                  stroke="#3b82f6" stroke-width="1.5" stroke-dasharray="4 3"
                                  :opacity="hovered !== null ? 1 : 0"/>
                            <circle :cx="hovered !== null ? puntos[hovered]?.x : -999"
                                    :cy="hovered !== null ? puntos[hovered]?.y : 0"
                                    r="5" fill="#2563eb" stroke="#1d4ed8" stroke-width="2"
                                    :opacity="hovered !== null ? 1 : 0"/>
                            <text x="400" y="92" text-anchor="middle" fill="#cbd5e1" font-size="14"
                                  :opacity="hayDatos ? 0 : 1">Sin datos en este período</text>
                        </svg>
                        <template x-for="p in puntos">
                            <div :style="`position:absolute; left:${(p.x/800*100).toFixed(2)}%; top:${(p.y/170*100).toFixed(2)}%; transform:translate(-50%,-50%); width:32px; height:32px;`"
                                 @mouseover="hovered = p.idx" @mouseleave="hovered = null">
                            </div>
                        </template>
                    </div>

                    {{-- Labels eje X --}}
                    <div class="relative w-full mt-1" style="height:18px">
                        <template x-for="p in puntos">
                            <span :style="`position:absolute; left:${(p.x/800*100).toFixed(2)}%; transform:translateX(-50%); font-size:10px; white-space:nowrap;`"
                                  :class="hovered === p.idx ? 'text-blue-600 font-semibold' : 'text-slate-400'"
                                  x-text="p.shortLabel">
                            </span>
                        </template>
                    </div>

                    {{-- Total --}}
                    <div class="mt-3 pt-3 border-t border-slate-100 flex items-center justify-between">
                        <span class="text-xs text-slate-400">Total período</span>
                        <span class="text-xs font-bold text-slate-700"
                              x-text="formatTotal(data.reduce((s, d) => s + d.total, 0))"></span>
                    </div>
                </div>
            </x-ui.card>
        </div>

        {{-- Sidebar --}}
        <div class="space-y-4">
            {{-- Contadores en fila --}}
            <div class="grid grid-cols-2 gap-3">
                <x-ui.card class="p-4">
                    <p class="text-xs font-semibold text-slate-500 uppercase tracking-wide mb-2">Contactos</p>
                    <p class="text-2xl font-bold text-slate-900">{{ $cliente->contactos->count() }}</p>
                </x-ui.card>
                <x-ui.card class="p-4">
                    <p class="text-xs font-semibold text-slate-500 uppercase tracking-wide mb-2">Seguimientos</p>
                    <p class="text-2xl font-bold text-slate-900">{{ $seguimientos->count() }}</p>
                </x-ui.card>
            </div>
            @can('update', $cliente)
            <x-ui.card class="p-5">
                <p class="text-xs font-semibold text-slate-500 uppercase tracking-wide mb-3">Cambiar estado</p>
                <div
                    x-data="{ loading: false }"
                    class="flex flex-col gap-2"
                >
                    @foreach(['activo', 'inactivo'] as $nuevoEstado)
                    @if($cliente->estado !== $nuevoEstado)
                    <x-ui.button
                        variant="{{ $nuevoEstado === 'activo' ? 'primary' : 'danger' }}"
                        size="sm"
                        x-bind:disabled="loading"
                        @click="
                            loading = true;
                            $api('PUT', '/api/clientes/{{ $cliente->id }}', { estado: '{{ $nuevoEstado }}' }).then(r => {
                                if (r.success) { $store.toast.success('Estado actualizado'); setTimeout(() => location.reload(), 600); }
                                else { $store.toast.error(r.message); loading = false; }
                            })
                        "
                    >
                        Marcar como {{ ucfirst($nuevoEstado) }}
                    </x-ui.button>
                    @endif
                    @endforeach
                </div>
            </x-ui.card>
            @endcan
        </div>
    </div>


    <script>
        function actividadChart(datasets, tipo = 'seguimientos') {
            const mensualSum = (datasets?.mensual || []).reduce((s, d) => s + d.total, 0);
            return {
                periodo: 'mensual',
                hovered: null,
                datasets,
                tipo,
                formatTotal(v) {
                    if (this.tipo !== 'ventas') {
                        return v + (v === 1 ? ' seg.' : ' segs.');
                    }
                    if (v >= 1_000_000_000) return '$' + (v / 1_000_000_000).toFixed(1) + 'B';
                    if (v >= 1_000_000)     return '$' + (v / 1_000_000).toFixed(1) + 'M';
                    if (v >= 1_000)         return '$' + Math.round(v / 1_000) + 'K';
                    return '$' + v.toFixed(0);
                },
                get data()   { return this.datasets[this.periodo] || []; },
                get max()    { return Math.max(...this.data.map(d => d.total), 1); },
                get hayDatos() { return this.data.some(d => d.total > 0); },
                get puntos() {
                    const n = this.data.length;
                    if (n === 0) return [];
                    const xA = 10, xB = 790, yT = 16, yB = 158;
                    return this.data.map((d, i) => ({
                        idx: i,
                        x: n === 1 ? (xA + xB) / 2 : xA + i * (xB - xA) / (n - 1),
                        y: yB - (d.total / this.max) * (yB - yT),
                        label: d.label,
                        total: d.total,
                        shortLabel: /^\d{4}$/.test(d.label)
                            ? d.label
                            : d.label.startsWith('Q')
                                ? d.label.slice(0, 2) + "'" + d.label.slice(-2)
                                : d.label.split(' ')[0].replace('.', ''),
                    }));
                },
                get linea() {
                    return this.puntos.map(p => `${p.x.toFixed(1)},${p.y.toFixed(1)}`).join(' ');
                },
                get area() {
                    const pts = this.puntos;
                    if (pts.length < 2) return '';
                    const f = pts[0], l = pts[pts.length - 1];
                    return `M${f.x.toFixed(1)},158 ` +
                           pts.map(p => `L${p.x.toFixed(1)},${p.y.toFixed(1)}`).join(' ') +
                           ` L${l.x.toFixed(1)},158 Z`;
                },
                get dotsPath() {
                    const r = 4;
                    return this.puntos
                        .filter(p => p.total > 0)
                        .map(p => `M ${(p.x - r).toFixed(1)} ${p.y.toFixed(1)} a ${r} ${r} 0 1 0 ${r * 2} 0 a ${r} ${r} 0 1 0 ${-r * 2} 0`)
                        .join(' ');
                },
            };
        }
    </script>

    {{-- Modal: nuevo seguimiento --}}
    <div x-data="{ open: false }" @open-seguimiento.window="open = true">
        <x-ui.modal title="Registrar seguimiento">
            <form
                x-data="{ loading: false }"
                @submit.prevent="
                    loading = true;
                    const fd = new FormData($el);
                    const body = {};
                    fd.forEach((v, k) => { if (v !== '') body[k] = v; });
                    body.cliente_id = {{ $cliente->id }};
                    $api('POST', '/api/seguimientos', body)
                        .then(r => {
                            if (r.success) { $store.toast.success(r.message); setTimeout(() => location.reload(), 600); }
                            else { $store.toast.error(r.message ?? 'Error'); loading = false; }
                        }).catch(() => { $store.toast.error('Error de conexión'); loading = false; })
                "
                class="space-y-4"
            >
                <div class="grid grid-cols-2 gap-4">
                    <x-ui.select name="tipo" label="Tipo" required>
                        <option value="">Seleccionar...</option>
                        <option value="llamada">Llamada</option>
                        <option value="reunion">Reunión</option>
                        <option value="email">Email</option>
                        <option value="visita">Visita</option>
                        <option value="otro">Otro</option>
                    </x-ui.select>
                    <x-ui.select name="resultado" label="Resultado" required>
                        <option value="">Seleccionar...</option>
                        <option value="exitoso">Exitoso</option>
                        <option value="pendiente">Pendiente</option>
                        <option value="fallido">Fallido</option>
                    </x-ui.select>
                </div>
                <x-ui.input name="fecha_seguimiento" type="datetime-local" label="Fecha y hora" required
                    :value="now()->format('Y-m-d\TH:i')"/>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1.5">Descripción</label>
                    <textarea name="descripcion" rows="3" required
                        class="w-full px-3 py-2 text-sm rounded-lg border border-slate-300 focus:outline-none focus:ring-2 focus:ring-blue-500 resize-none"
                        placeholder="¿Qué se trató en este seguimiento?"></textarea>
                </div>
                <x-ui.input name="proxima_fecha" type="datetime-local" label="Próxima cita (opcional)"/>
                <div class="flex justify-end gap-3 pt-2">
                    <x-ui.button type="button" variant="ghost" @click="open = false">Cancelar</x-ui.button>
                    <x-ui.button type="submit" variant="primary" x-bind:disabled="loading">
                        <span x-show="!loading">Registrar</span>
                        <span x-show="loading">Guardando...</span>
                    </x-ui.button>
                </div>
            </form>
        </x-ui.modal>
    </div>

    {{-- Modal: nuevo contacto --}}
    <div x-data="{ open: false }" @open-contacto.window="open = true">
        <x-ui.modal title="Añadir contacto">
            <form
                x-data="{ loading: false }"
                @submit.prevent="
                    loading = true;
                    const fd = new FormData($el);
                    const body = {};
                    fd.forEach((v, k) => { if (v !== '') body[k] = v; });
                    $api('POST', '/api/clientes/{{ $cliente->id }}/contactos', body)
                        .then(r => {
                            if (r.success) { $store.toast.success(r.message ?? 'Contacto añadido'); setTimeout(() => location.reload(), 600); }
                            else { $store.toast.error(r.message ?? 'Error'); loading = false; }
                        }).catch(() => { $store.toast.error('Error de conexión'); loading = false; })
                "
                class="space-y-4"
            >
                <div class="grid grid-cols-2 gap-4">
                    <x-ui.input name="nombre" label="Nombre" required placeholder="Nombre completo"/>
                    <x-ui.input name="cargo" label="Cargo" placeholder="Cargo o posición"/>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <x-ui.input name="email" type="email" label="Email"/>
                    <x-ui.input name="telefono" label="Teléfono"/>
                </div>
                <div class="flex items-center gap-2">
                    <input type="checkbox" name="principal" value="1" id="principal_check"
                        class="rounded border-slate-300 text-blue-600 focus:ring-blue-500">
                    <label for="principal_check" class="text-sm text-slate-700">Contacto principal</label>
                </div>
                <div class="flex justify-end gap-3 pt-2">
                    <x-ui.button type="button" variant="ghost" @click="open = false">Cancelar</x-ui.button>
                    <x-ui.button type="submit" variant="primary" x-bind:disabled="loading">
                        <span x-show="!loading">Añadir</span>
                        <span x-show="loading">Guardando...</span>
                    </x-ui.button>
                </div>
            </form>
        </x-ui.modal>
    </div>
</x-layouts.app>
