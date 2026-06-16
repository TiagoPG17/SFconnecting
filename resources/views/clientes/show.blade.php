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

            {{-- KPIs de ventas ERP --}}
            @if(!empty($actividadSiesa['anual'] ?? []))
            @php
                $kpiAnio      = (int) date('Y');
                $kpiAnterior  = $kpiAnio - 1;
                $kpiActual    = (float) (collect($actividadSiesa['anual'])->firstWhere('label', (string) $kpiAnio)['total'] ?? 0);
                $kpiPasado    = (float) (collect($actividadSiesa['anual'])->firstWhere('label', (string) $kpiAnterior)['total'] ?? 0);
                $kpiVariacion = $kpiPasado > 0 ? (($kpiActual - $kpiPasado) / $kpiPasado) * 100 : null;
                $kpiNFacturas = count($facturas);
                $kpiUltima    = !empty($facturas) ? collect($facturas)->max('FECHA') : null;
                $fmtK = fn(float $v): string => $v >= 1_000_000_000 ? '$'.number_format($v/1_000_000_000,1).'B'
                    : ($v >= 1_000_000 ? '$'.number_format($v/1_000_000,1).'M'
                    : ($v >= 1_000 ? '$'.number_format($v/1_000,0).'K'
                    : '$'.number_format($v,0,'.',',')));
            @endphp
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                <x-ui.card class="p-4">
                    <p class="text-xs font-semibold text-slate-400 uppercase tracking-wide mb-1">Facturado {{ $kpiAnio }}</p>
                    <p class="text-xl font-bold text-slate-900">{{ $fmtK($kpiActual) }}</p>
                    <p class="text-xs text-slate-400 mt-0.5">Año en curso</p>
                </x-ui.card>

                <x-ui.card class="p-4">
                    <p class="text-xs font-semibold text-slate-400 uppercase tracking-wide mb-1">Vs {{ $kpiAnterior }}</p>
                    @if($kpiVariacion !== null)
                        @php $sube = $kpiVariacion >= 0; @endphp
                        <p class="text-xl font-bold {{ $sube ? 'text-green-600' : 'text-red-600' }}">
                            {{ $sube ? '+' : '' }}{{ number_format($kpiVariacion, 1) }}%
                        </p>
                        <p class="text-xs text-slate-400 mt-0.5">{{ $fmtK($kpiPasado) }} el año pasado</p>
                    @else
                        <p class="text-xl font-bold text-slate-400">—</p>
                        <p class="text-xs text-slate-400 mt-0.5">Sin datos previos</p>
                    @endif
                </x-ui.card>

                <x-ui.card class="p-4">
                    <p class="text-xs font-semibold text-slate-400 uppercase tracking-wide mb-1">Nº facturas</p>
                    <p class="text-xl font-bold text-slate-900">{{ $kpiNFacturas }}</p>
                    <p class="text-xs text-slate-400 mt-0.5">Historial cargado</p>
                </x-ui.card>

                <x-ui.card class="p-4">
                    <p class="text-xs font-semibold text-slate-400 uppercase tracking-wide mb-1">Última compra</p>
                    <p class="text-xl font-bold text-slate-900">
                        {{ $kpiUltima ? \Carbon\Carbon::parse($kpiUltima)->format('d/m/Y') : '—' }}
                    </p>
                    @if($kpiUltima)
                        <p class="text-xs text-slate-400 mt-0.5">
                            Hace {{ \Carbon\Carbon::parse($kpiUltima)->diffForHumans(null, true) }}
                        </p>
                    @endif
                </x-ui.card>
            </div>
            @endif

            {{-- Gráfica de actividad / ventas SIESA --}}
            @php
                $usarSiesa     = !empty($actividadSiesa['mensual'] ?? []);
                $chartDatasets = $usarSiesa ? $actividadSiesa : $actividad;
                $chartTipo     = $usarSiesa ? 'ventas' : 'seguimientos';
            @endphp
            <script>window['_chart_{{ $cliente->id }}'] = {!! json_encode($chartDatasets) !!};</script>

            <x-ui.card x-data="actividadChart(window['_chart_{{ $cliente->id }}'], '{{ $chartTipo }}')">
                <div class="p-4">

                    {{-- Header --}}
                    <div class="flex items-center justify-between mb-4">
                        <div>
                            <p class="text-xs font-semibold text-slate-500 uppercase tracking-wide">
                                {{ $usarSiesa ? 'Ventas ERP' : 'Actividad de seguimientos' }}
                            </p>
                            <p class="text-xs text-slate-400 mt-0.5" x-text="hovered !== null
                                ? (puntos[hovered]?.label + ' · ' + formatTotal(puntos[hovered]?.total))
                                : 'Pasa el cursor sobre la gráfica'">
                            </p>
                        </div>
                        <div class="flex gap-0.5 bg-slate-100 rounded-lg p-0.5">
                            @foreach(['mensual' => 'Mes', 'trimestral' => 'Trim.', 'anual' => 'Año'] as $key => $lbl)
                            <button @click="periodo = '{{ $key }}'; hovered = null"
                                    :class="periodo === '{{ $key }}' ? 'bg-white shadow-sm text-slate-900' : 'text-slate-500 hover:text-slate-700'"
                                    class="px-3 py-1 rounded-md text-xs font-medium transition-all">
                                {{ $lbl }}
                            </button>
                            @endforeach
                        </div>
                    </div>

                    {{-- Área gráfica: eje Y + SVG --}}
                    <div class="flex gap-2">

                        {{-- Eje Y --}}
                        <div class="flex flex-col justify-between shrink-0 text-right pb-1" style="width:46px; height:160px">
                            <span class="text-xs text-slate-400 leading-none" x-text="fmtY(max)"></span>
                            <span class="text-xs text-slate-400 leading-none" x-text="fmtY(max * 0.5)"></span>
                            <span class="text-xs text-slate-400 leading-none">$0</span>
                        </div>

                        {{-- SVG + labels X --}}
                        <div class="flex-1 min-w-0">
                            <div class="relative w-full" style="height:160px">
                                <svg viewBox="0 0 800 160" preserveAspectRatio="none"
                                     class="absolute inset-0 w-full h-full" style="overflow:visible">
                                    <defs>
                                        <linearGradient id="act-grad-{{ $cliente->id }}" x1="0" y1="0" x2="0" y2="1">
                                            <stop offset="0%" stop-color="#3b82f6" stop-opacity="0.18"/>
                                            <stop offset="100%" stop-color="#3b82f6" stop-opacity="0"/>
                                        </linearGradient>
                                    </defs>
                                    {{-- Líneas guía --}}
                                    <line x1="0" y1="150" x2="800" y2="150" stroke="#e2e8f0" stroke-width="1"/>
                                    <line x1="0" y1="80"  x2="800" y2="80"  stroke="#e2e8f0" stroke-width="0.75" stroke-dasharray="4 3"/>
                                    <line x1="0" y1="10"  x2="800" y2="10"  stroke="#e2e8f0" stroke-width="0.75" stroke-dasharray="4 3"/>
                                    {{-- Área y línea --}}
                                    <path :d="area" :fill="'url(#act-grad-{{ $cliente->id }})'" :opacity="hayDatos ? 1 : 0"/>
                                    <polyline :points="linea" fill="none" stroke="#3b82f6" stroke-width="2.5"
                                              stroke-linejoin="round" stroke-linecap="round" :opacity="hayDatos ? 1 : 0"/>
                                    <path :d="dotsPath" fill="white" stroke="#3b82f6" stroke-width="2" :opacity="hayDatos ? 1 : 0"/>
                                    {{-- Crosshair hover --}}
                                    <line :x1="hovered !== null ? puntos[hovered]?.x : -999"
                                          :x2="hovered !== null ? puntos[hovered]?.x : -999"
                                          y1="10" :y2="hovered !== null ? (puntos[hovered]?.y ?? 0) - 7 : 0"
                                          stroke="#3b82f6" stroke-width="1.5" stroke-dasharray="4 3"
                                          :opacity="hovered !== null ? 0.6 : 0"/>
                                    <circle :cx="hovered !== null ? puntos[hovered]?.x : -999"
                                            :cy="hovered !== null ? puntos[hovered]?.y : 0"
                                            r="5" fill="#2563eb" stroke="white" stroke-width="2"
                                            :opacity="hovered !== null ? 1 : 0"/>
                                    {{-- Sin datos --}}
                                    <text x="400" y="85" text-anchor="middle" fill="#cbd5e1" font-size="13"
                                          :opacity="hayDatos ? 0 : 1">Sin datos en este período</text>
                                </svg>
                                {{-- Zonas hover invisibles --}}
                                <template x-for="p in puntos">
                                    <div :style="`position:absolute;left:${(p.x/800*100).toFixed(2)}%;top:${(p.y/160*100).toFixed(2)}%;transform:translate(-50%,-50%);width:32px;height:32px;`"
                                         @mouseover="hovered = p.idx" @mouseleave="hovered = null">
                                    </div>
                                </template>
                            </div>

                            {{-- Labels eje X --}}
                            <div class="relative w-full mt-1.5" style="height:16px">
                                <template x-for="p in puntos">
                                    <span :style="`position:absolute;left:${(p.x/800*100).toFixed(2)}%;transform:translateX(-50%);font-size:10px;white-space:nowrap;`"
                                          :class="hovered === p.idx ? 'text-blue-600 font-semibold' : 'text-slate-400'"
                                          x-text="p.shortLabel">
                                    </span>
                                </template>
                            </div>
                        </div>
                    </div>

                    {{-- Footer: total + pico --}}
                    <div class="mt-4 pt-3 border-t border-slate-100 flex items-center justify-between">
                        <div>
                            <span class="text-xs text-slate-400">Total período</span>
                            <span class="ml-2 text-xs font-bold text-slate-700"
                                  x-text="formatTotal(data.reduce((s, d) => s + d.total, 0))"></span>
                        </div>
                        <div x-show="hayDatos">
                            <span class="text-xs text-slate-400">Pico</span>
                            <span class="ml-2 text-xs font-bold text-slate-700"
                                  x-text="formatTotal(max)"></span>
                        </div>
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

            {{-- Historial de compras --}}
            @if(!empty($facturas))
            <x-ui.card class="p-5"
                x-data="historialCompras({{ json_encode(array_values($facturas)) }})">

                <div class="flex items-center justify-between mb-3">
                    <p class="text-xs font-semibold text-slate-500 uppercase tracking-wide">Historial de compras</p>
                    <span class="text-xs text-slate-400" x-text="`${total} facturas`"></span>
                </div>

                {{-- Lista de facturas --}}
                <div class="divide-y divide-slate-100">
                    <template x-for="fac in paginadas" :key="fac.ROWID_FACTURA">
                        <button
                            @click="abrirModal(fac)"
                            class="w-full flex items-center justify-between py-2.5 px-1 hover:bg-slate-50 rounded-lg transition-colors text-left gap-3 group"
                        >
                            <div class="flex flex-col min-w-0">
                                <span class="text-xs font-medium text-slate-800 truncate"
                                      x-text="(fac.CONCEPTO || '') + ' ' + (fac.TIPO || '')"></span>
                                <span class="text-xs text-slate-400 mt-0.5"
                                      x-text="fac.FECHA + ' · ' + fac.NUM_ITEMS + ' ítems'"></span>
                            </div>
                            <div class="flex items-center gap-2 shrink-0">
                                <span class="text-sm font-semibold text-slate-900"
                                      x-text="fmtCOP(fac.VLR_NETO)"></span>
                                <svg class="w-3.5 h-3.5 text-slate-300 group-hover:text-slate-500 transition-colors"
                                     fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                </svg>
                            </div>
                        </button>
                    </template>
                </div>

                {{-- Paginación --}}
                <template x-if="totalPaginas > 1">
                    <div class="flex items-center justify-between mt-3 pt-3 border-t border-slate-100">
                        <button @click="irA(pagina - 1)" :disabled="pagina === 1"
                                class="px-3 py-1.5 text-xs rounded-lg border border-slate-200 text-slate-500 hover:bg-slate-50 disabled:opacity-40 disabled:cursor-not-allowed transition-colors">
                            ← Anterior
                        </button>
                        <span class="text-xs text-slate-400" x-text="`${pagina} / ${totalPaginas}`"></span>
                        <button @click="irA(pagina + 1)" :disabled="pagina === totalPaginas"
                                class="px-3 py-1.5 text-xs rounded-lg border border-slate-200 text-slate-500 hover:bg-slate-50 disabled:opacity-40 disabled:cursor-not-allowed transition-colors">
                            Siguiente →
                        </button>
                    </div>
                </template>

                {{-- Modal detalle --}}
                <div x-show="modal" x-cloak
                     class="fixed inset-0 z-50 flex items-center justify-center p-4"
                     x-transition:enter="transition ease-out duration-150"
                     x-transition:enter-start="opacity-0"
                     x-transition:enter-end="opacity-100"
                     x-transition:leave="transition ease-in duration-100"
                     x-transition:leave-start="opacity-100"
                     x-transition:leave-end="opacity-0">
                    <div class="absolute inset-0 bg-black/40 backdrop-blur-sm" @click="modal = false"></div>
                    <div class="relative bg-white rounded-2xl shadow-xl w-full max-w-2xl max-h-[88vh] flex flex-col"
                         x-transition:enter="transition ease-out duration-150"
                         x-transition:enter-start="opacity-0 scale-95"
                         x-transition:enter-end="opacity-100 scale-100">

                        {{-- Header modal --}}
                        <div class="px-6 pt-5 pb-4 border-b border-slate-100 shrink-0">
                            <div class="flex items-start justify-between gap-4">
                                <div class="flex items-center gap-2 flex-wrap">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-md bg-blue-50 text-blue-700 text-xs font-semibold"
                                          x-text="facActiva?.TIPO || 'DOC'"></span>
                                    <h3 class="text-base font-bold text-slate-900"
                                        x-text="'Concepto ' + (facActiva?.CONCEPTO || '')"></h3>
                                </div>
                                <button @click="modal = false"
                                        class="p-1.5 rounded-lg hover:bg-slate-100 text-slate-400 transition-colors shrink-0">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                    </svg>
                                </button>
                            </div>

                            {{-- Info rápida --}}
                            <div class="mt-3 flex flex-wrap gap-x-5 gap-y-1.5">
                                <div class="flex items-center gap-1.5 text-xs text-slate-500">
                                    <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                    </svg>
                                    <span x-text="facActiva?.FECHA"></span>
                                </div>
                                <div class="flex items-center gap-1.5 text-xs text-slate-500">
                                    <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10"/>
                                    </svg>
                                    <span x-text="(facActiva?.NUM_ITEMS || 0) + ' ítems'"></span>
                                </div>
                                <div class="flex items-center gap-1.5 text-xs text-slate-500">
                                    <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                                    </svg>
                                    <span x-text="'Compañía ' + (facActiva?.COMPANIA || '')"></span>
                                </div>
                                <div class="flex items-center gap-1.5 text-xs text-slate-500">
                                    <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                    </svg>
                                    <span x-text="'Vendedor: ' + (facActiva?.COD_VENDEDOR || '—')"></span>
                                </div>
                            </div>
                        </div>

                        {{-- Loader --}}
                        <div x-show="cargando" class="flex flex-col items-center justify-center py-16 gap-3">
                            <svg class="w-6 h-6 animate-spin text-blue-500" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"/>
                            </svg>
                            <p class="text-xs text-slate-400">Cargando productos...</p>
                        </div>

                        {{-- Tabla de ítems --}}
                        <div x-show="!cargando && facActiva" class="overflow-y-auto flex-1">
                            <template x-if="itemsActivos.length === 0">
                                <div class="flex flex-col items-center justify-center py-16 text-center">
                                    <svg class="w-8 h-8 text-slate-200 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                                    </svg>
                                    <p class="text-sm text-slate-400">Sin ítems registrados para esta factura</p>
                                </div>
                            </template>
                            <template x-if="itemsActivos.length > 0">
                                <table class="w-full text-sm">
                                    <thead class="sticky top-0 bg-white border-b border-slate-200 z-10">
                                        <tr>
                                            <th class="text-left px-6 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wide">Producto</th>
                                            <th class="text-center px-3 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wide w-20">Cant.</th>
                                            <th class="text-right px-3 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wide w-28">Precio unit.</th>
                                            <th class="text-right px-6 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wide w-28">Vlr. neto</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <template x-for="(item, idx) in itemsActivos" :key="item.COD_PRODUCTO + idx">
                                            <tr :class="idx % 2 === 0 ? 'bg-white' : 'bg-slate-50/60'">
                                                <td class="px-6 py-3">
                                                    <p class="font-medium text-slate-800 leading-snug"
                                                       x-text="item.NOMBRE_PRODUCTO || item.COD_PRODUCTO"></p>
                                                    <div class="flex items-center gap-2 mt-0.5 flex-wrap">
                                                        <span class="text-xs text-slate-400" x-text="item.COD_PRODUCTO"></span>
                                                        <template x-if="item.REFERENCIA && item.REFERENCIA !== item.COD_PRODUCTO">
                                                            <span class="text-xs text-slate-400" x-text="'· Ref: ' + item.REFERENCIA"></span>
                                                        </template>
                                                        <template x-if="item.UNIDAD">
                                                            <span class="inline-flex items-center px-1.5 py-0.5 rounded bg-slate-100 text-slate-500 text-xs"
                                                                  x-text="item.UNIDAD"></span>
                                                        </template>
                                                    </div>
                                                </td>
                                                <td class="px-3 py-3 text-center text-slate-700 font-medium tabular-nums"
                                                    x-text="Number(item.CANTIDAD).toLocaleString('es-CO')"></td>
                                                <td class="px-3 py-3 text-right text-slate-500 tabular-nums"
                                                    x-text="fmtCOP(item.PRECIO_UNIT)"></td>
                                                <td class="px-6 py-3 text-right font-semibold text-slate-900 tabular-nums"
                                                    x-text="fmtCOP(item.VLR_NETO)"></td>
                                            </tr>
                                        </template>
                                    </tbody>
                                </table>
                            </template>
                        </div>

                        {{-- Footer: desglose financiero --}}
                        <div x-show="!cargando && itemsActivos.length > 0"
                             class="px-6 py-4 border-t border-slate-100 bg-slate-50 rounded-b-2xl shrink-0">
                            <div class="flex flex-col gap-1.5 items-end">
                                <div class="flex items-center justify-between w-56">
                                    <span class="text-xs text-slate-500">Subtotal bruto</span>
                                    <span class="text-xs text-slate-600 tabular-nums"
                                          x-text="fmtCOP(itemsActivos.reduce((s,i)=>s+Number(i.VLR_BRUTO),0))"></span>
                                </div>
                                <div class="flex items-center justify-between w-56">
                                    <span class="text-xs text-slate-500">Impuestos</span>
                                    <span class="text-xs text-slate-600 tabular-nums"
                                          x-text="fmtCOP(itemsActivos.reduce((s,i)=>s+Number(i.VLR_IMP),0))"></span>
                                </div>
                                <div class="flex items-center justify-between w-56 pt-2 mt-1 border-t border-slate-200">
                                    <span class="text-sm font-semibold text-slate-700">Total neto</span>
                                    <span class="text-base font-bold text-slate-900 tabular-nums"
                                          x-text="fmtCOP(itemsActivos.reduce((s,i)=>s+Number(i.VLR_NETO),0))"></span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            </x-ui.card>
            @endif
        </div>
    </div>


    <script>
    function historialCompras(data) {
        return {
            todas: data,
            pagina: 1,
            porPagina: 12,
            modal: false,
            cargando: false,
            facActiva: null,
            detalles: {},
            get total()        { return this.todas.length; },
            get totalPaginas() { return Math.ceil(this.total / this.porPagina); },
            get paginadas() {
                const ini = (this.pagina - 1) * this.porPagina;
                return this.todas.slice(ini, ini + this.porPagina);
            },
            get itemsActivos() {
                return this.facActiva ? (this.detalles[this.facActiva.ROWID_FACTURA] || []) : [];
            },
            fmtCOP(v) {
                const n = Number(v);
                if (isNaN(n)) return '—';
                if (n >= 1_000_000_000) return '$' + (n / 1_000_000_000).toFixed(1) + 'B';
                if (n >= 1_000_000)     return '$' + (n / 1_000_000).toFixed(1) + 'M';
                if (n >= 1_000)         return '$' + Math.round(n / 1_000) + 'K';
                return '$' + n.toLocaleString('es-CO', {minimumFractionDigits: 0, maximumFractionDigits: 2});
            },
            irA(p) {
                if (p < 1 || p > this.totalPaginas) return;
                this.pagina = p;
            },
            async abrirModal(fac) {
                this.facActiva = fac;
                this.modal     = true;
                if (this.detalles[fac.ROWID_FACTURA] === undefined) {
                    this.cargando = true;
                    try {
                        const r = await this.$api('GET', `/api/erp/facturas/${fac.ROWID_FACTURA}/detalle`);
                        this.detalles[fac.ROWID_FACTURA] = r.data || [];
                    } catch {
                        this.detalles[fac.ROWID_FACTURA] = [];
                    }
                    this.cargando = false;
                }
            },
        };
    }
    </script>

    <script>
        function actividadChart(datasets, tipo = 'seguimientos') {
            return {
                periodo: 'mensual',
                hovered: null,
                datasets,
                tipo,
                fmtY(v) {
                    if (v >= 1_000_000_000) return '$' + (v / 1_000_000_000).toFixed(1) + 'B';
                    if (v >= 1_000_000)     return '$' + (v / 1_000_000).toFixed(1) + 'M';
                    if (v >= 1_000)         return '$' + Math.round(v / 1_000) + 'K';
                    return '$' + Math.round(v);
                },
                formatTotal(v) {
                    if (this.tipo !== 'ventas') return v + (v === 1 ? ' seg.' : ' segs.');
                    return this.fmtY(v);
                },
                get data()     { return this.datasets[this.periodo] || []; },
                get max()      { return Math.max(...this.data.map(d => d.total), 1); },
                get hayDatos() { return this.data.some(d => d.total > 0); },
                get puntos() {
                    const n = this.data.length;
                    if (n === 0) return [];
                    const xA = 0, xB = 800, yT = 10, yB = 150;
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
                    return `M${f.x.toFixed(1)},150 ` +
                           pts.map(p => `L${p.x.toFixed(1)},${p.y.toFixed(1)}`).join(' ') +
                           ` L${l.x.toFixed(1)},150 Z`;
                },
                get dotsPath() {
                    const r = 4;
                    return this.puntos
                        .filter(p => p.total > 0)
                        .map(p => `M ${(p.x-r).toFixed(1)} ${p.y.toFixed(1)} a ${r} ${r} 0 1 0 ${r*2} 0 a ${r} ${r} 0 1 0 ${-r*2} 0`)
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
