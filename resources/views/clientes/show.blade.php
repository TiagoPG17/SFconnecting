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
        </div>

        {{-- Sidebar --}}
        <div class="space-y-4">
            <x-ui.card class="p-5">
                <p class="text-xs font-semibold text-slate-500 uppercase tracking-wide mb-3">Contactos</p>
                <p class="text-2xl font-bold text-slate-900">{{ $cliente->contactos->count() }}</p>
            </x-ui.card>
            <x-ui.card class="p-5">
                <p class="text-xs font-semibold text-slate-500 uppercase tracking-wide mb-3">Seguimientos</p>
                <p class="text-2xl font-bold text-slate-900">{{ $seguimientos->count() }}</p>
            </x-ui.card>
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

            {{-- Gráfica de actividad --}}
            <x-ui.card x-data="actividadChart({!! json_encode($actividad) !!})">
                <div class="p-4">
                    <div class="flex items-center justify-between mb-3">
                        <p class="text-xs font-semibold text-slate-500 uppercase tracking-wide">Actividad</p>
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
                                  x-text="puntos[hovered]?.label + ' · ' + puntos[hovered]?.total + (puntos[hovered]?.total === 1 ? ' seg.' : ' segs.')">
                            </span>
                        </template>
                    </div>

                    {{-- SVG --}}
                    <svg viewBox="0 0 280 75" class="w-full h-14 overflow-visible">
                        <defs>
                            <linearGradient id="act-grad-{{ $cliente->id }}" x1="0" y1="0" x2="0" y2="1">
                                <stop offset="0%" stop-color="#3b82f6" stop-opacity="0.25"/>
                                <stop offset="100%" stop-color="#3b82f6" stop-opacity="0"/>
                            </linearGradient>
                        </defs>

                        {{-- Grid --}}
                        <line x1="5" y1="60" x2="275" y2="60" stroke="#e2e8f0" stroke-width="0.75"/>
                        <line x1="5" y1="35" x2="275" y2="35" stroke="#e2e8f0" stroke-width="0.5" stroke-dasharray="3 2"/>
                        <line x1="5" y1="10" x2="275" y2="10" stroke="#e2e8f0" stroke-width="0.5" stroke-dasharray="3 2"/>

                        {{-- Area relleno --}}
                        <path :d="area" :fill="'url(#act-grad-{{ $cliente->id }})'"
                              x-show="hayDatos" class="transition-all duration-300"/>

                        {{-- Línea --}}
                        <polyline :points="linea" fill="none" stroke="#3b82f6" stroke-width="1.75"
                                  stroke-linejoin="round" stroke-linecap="round"
                                  x-show="hayDatos" class="transition-all duration-300"/>

                        {{-- Mensaje sin datos --}}
                        <text x="140" y="38" text-anchor="middle" fill="#cbd5e1" font-size="9.5"
                              x-show="!hayDatos">Sin seguimientos en este período</text>

                        {{-- Puntos hover --}}
                        <template x-for="(p, i) in puntos" :key="i">
                            <g>
                                {{-- Área invisible grande para capturar hover --}}
                                <circle :cx="p.x" :cy="p.y" r="9" fill="transparent"
                                        @mouseenter="hovered = i" @mouseleave="hovered = null"
                                        class="cursor-default"/>
                                {{-- Punto visible --}}
                                <circle :cx="p.x" :cy="p.y" r="2.5"
                                        :fill="hovered === i ? '#2563eb' : 'white'"
                                        :stroke="hovered === i ? '#1d4ed8' : '#3b82f6'"
                                        stroke-width="1.5"
                                        x-show="p.total > 0 || hovered === i"/>
                                {{-- Línea vertical en hover --}}
                                <line :x1="p.x" y1="10" :x2="p.x" :y2="p.y - 3"
                                      stroke="#3b82f6" stroke-width="0.75" stroke-dasharray="2 2"
                                      x-show="hovered === i"/>
                            </g>
                        </template>
                    </svg>

                    {{-- Eje X: primera · media · última etiqueta --}}
                    <div class="flex justify-between mt-0.5 px-0.5">
                        <span class="text-xs text-slate-400" x-text="data[0]?.label ?? ''"></span>
                        <span class="text-xs text-slate-400" x-text="data[Math.floor((data.length - 1) / 2)]?.label ?? ''"></span>
                        <span class="text-xs text-slate-400" x-text="data[data.length - 1]?.label ?? ''"></span>
                    </div>

                    {{-- Total --}}
                    <div class="mt-3 pt-3 border-t border-slate-100 flex items-center justify-between">
                        <span class="text-xs text-slate-400">Total período</span>
                        <span class="text-xs font-bold text-slate-700"
                              x-text="data.reduce((s, d) => s + d.total, 0) + ' seguimientos'"></span>
                    </div>
                </div>
            </x-ui.card>
        </div>
    </div>

    <script>
        function actividadChart(datasets) {
            return {
                periodo: 'mensual',
                hovered: null,
                datasets,
                get data()   { return this.datasets[this.periodo] || []; },
                get max()    { return Math.max(...this.data.map(d => d.total), 1); },
                get hayDatos() { return this.data.some(d => d.total > 0); },
                get puntos() {
                    const n = this.data.length;
                    if (n === 0) return [];
                    const xA = 5, xB = 275, yT = 10, yB = 60;
                    return this.data.map((d, i) => ({
                        x: n === 1 ? (xA + xB) / 2 : xA + i * (xB - xA) / (n - 1),
                        y: yB - (d.total / this.max) * (yB - yT),
                        label: d.label,
                        total: d.total,
                    }));
                },
                get linea() {
                    return this.puntos.map(p => `${p.x.toFixed(1)},${p.y.toFixed(1)}`).join(' ');
                },
                get area() {
                    const pts = this.puntos;
                    if (pts.length < 2) return '';
                    const f = pts[0], l = pts[pts.length - 1];
                    return `M${f.x.toFixed(1)},60 ` +
                           pts.map(p => `L${p.x.toFixed(1)},${p.y.toFixed(1)}`).join(' ') +
                           ` L${l.x.toFixed(1)},60 Z`;
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
