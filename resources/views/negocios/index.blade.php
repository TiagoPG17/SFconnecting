<x-layouts.app title="Negocios">
    <x-slot name="actions">
        <div class="flex items-center gap-2">
        <x-ui.button href="{{ route('negocios.kanban') }}" variant="secondary" size="sm">
            <x-ui.icon name="layout" class="w-4 h-4"/> Kanban
        </x-ui.button>
        @if(auth()->user()->can('create', \App\Domain\Negocios\Models\Negocio::class) && \App\Support\AccesoSgp::permitido(auth()->user()))
        <div x-data="candidatosNegocioSgp()">
        <button type="button" @click="abrir()"
                class="inline-flex items-center gap-1.5 text-sm px-3 py-1.5 rounded-lg bg-white text-indigo-700 border border-indigo-200 hover:bg-indigo-50 font-medium shadow-sm transition-colors">
            <x-ui.icon name="bar-chart" class="w-4 h-4"/>
            Cargar solicitudes de cotización
        </button>

        <x-ui.modal title="Solicitudes de cotización — cliente o prospecto existente" size="xl">
            {{-- Paso 1: lista de solicitudes --}}
            <div class="space-y-3" x-show="!solicitudActual">
                <div class="flex items-center justify-between gap-3">
                    <div class="relative flex-1">
                        <x-ui.icon name="search" class="w-4 h-4 absolute left-3 top-1/2 -translate-y-1/2 text-slate-400"/>
                        <input type="text" x-model="buscar" @input.debounce.400ms="cargar()"
                               placeholder="Buscar por NIT o nombre del cliente..."
                               class="w-full pl-9 pr-3 py-2 text-sm rounded-lg border border-slate-300 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <span class="shrink-0 text-xs text-slate-400">Últimos 3 meses</span>
                </div>

                <template x-if="cargando">
                    <p class="text-sm text-slate-400 text-center py-8">Cargando...</p>
                </template>
                <template x-if="!cargando && error">
                    <p class="text-sm text-red-600 text-center py-8" x-text="error"></p>
                </template>
                <template x-if="!cargando && !error && items.length === 0">
                    <p class="text-sm text-slate-400 text-center py-8">No hay solicitudes pendientes por convertir.</p>
                </template>

                <div class="rounded-lg border border-slate-200 overflow-hidden" x-show="!cargando && !error && items.length > 0">
                    <div class="max-h-96 overflow-y-auto">
                        <table class="w-full text-sm">
                            <thead class="sticky top-0 bg-slate-50 border-b border-slate-200">
                                <tr class="text-left text-xs font-semibold text-slate-500 uppercase tracking-wide">
                                    <th class="px-3 py-2.5">Cliente</th>
                                    <th class="px-3 py-2.5">Vínculo</th>
                                    <th class="px-3 py-2.5">Solicitud</th>
                                    <th class="px-3 py-2.5">Fecha</th>
                                    <th class="px-3 py-2.5">Comercial</th>
                                    <th class="px-3 py-2.5 text-right">Total cotizado</th>
                                    <th class="px-3 py-2.5"></th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 bg-white">
                                <template x-for="item in items" :key="item.nro_solicitud">
                                    <tr class="hover:bg-slate-50 transition-colors">
                                        <td class="px-3 py-2.5 max-w-[200px]">
                                            <p class="font-medium text-slate-900 truncate" x-text="item.cliente"></p>
                                            <p class="text-xs text-slate-400" x-text="'NIT: ' + (item.nit || '—')"></p>
                                        </td>
                                        <td class="px-3 py-2.5">
                                            <span class="text-[10px] font-semibold px-1.5 py-0.5 rounded-full border"
                                                  :class="item.vinculo_tipo === 'cliente' ? 'bg-emerald-100 text-emerald-700 border-emerald-200' : 'bg-blue-100 text-blue-700 border-blue-200'"
                                                  x-text="item.vinculo_tipo === 'cliente' ? 'Cliente' : 'Prospecto'"></span>
                                        </td>
                                        <td class="px-3 py-2.5 font-mono text-xs text-slate-600 whitespace-nowrap" x-text="item.nro_solicitud"></td>
                                        <td class="px-3 py-2.5 text-xs text-slate-500 whitespace-nowrap" x-text="item.fecha_solicitud"></td>
                                        <td class="px-3 py-2.5 text-xs text-slate-500 max-w-[130px] truncate" x-text="item.comercial"></td>
                                        <td class="px-3 py-2.5 text-right text-xs font-semibold text-slate-700 whitespace-nowrap"
                                            x-text="'$' + Number(item.valor_estimado).toLocaleString('es-CO')"></td>
                                        <td class="px-3 py-2.5 text-right">
                                            <button type="button" @click="verEscalas(item)"
                                                    class="text-xs font-semibold text-blue-700 hover:underline whitespace-nowrap">
                                                Ver escalas →
                                            </button>
                                        </td>
                                    </tr>
                                </template>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            {{-- Paso 2: escalas de la solicitud elegida --}}
            <div class="space-y-3" x-show="solicitudActual" x-cloak>
                <template x-if="solicitudActual">
                    <div class="flex items-center justify-between gap-3 rounded-lg bg-slate-50 border border-slate-200 px-3 py-2">
                        <div>
                            <p class="text-sm font-medium text-slate-900" x-text="solicitudActual.cliente"></p>
                            <p class="text-xs text-slate-500" x-text="'NIT: ' + (solicitudActual.nit || '—') + ' · Solicitud ' + solicitudActual.nro_solicitud"></p>
                        </div>
                        <button type="button" @click="volverALista()"
                                class="shrink-0 text-xs font-medium text-slate-500 hover:text-slate-700">
                            ← Volver a la lista
                        </button>
                    </div>
                </template>

                <template x-if="cargandoEscalas">
                    <p class="text-sm text-slate-400 text-center py-8">Cargando escalas...</p>
                </template>
                <template x-if="!cargandoEscalas && errorEscalas">
                    <p class="text-sm text-red-600 text-center py-8" x-text="errorEscalas"></p>
                </template>
                <template x-if="!cargandoEscalas && !errorEscalas && escalas.length === 0">
                    <p class="text-sm text-slate-400 text-center py-8">Esta solicitud no tiene escalas activas.</p>
                </template>

                <div class="rounded-lg border border-slate-200 overflow-hidden" x-show="!cargandoEscalas && !errorEscalas && escalas.length > 0">
                    <table class="w-full text-sm">
                        <thead class="bg-slate-50 border-b border-slate-200">
                            <tr class="text-left text-xs font-semibold text-slate-500 uppercase tracking-wide">
                                <th class="px-3 py-2.5 text-right">Escala</th>
                                <th class="px-3 py-2.5">Moneda</th>
                                <th class="px-3 py-2.5 text-right">Precio unit.</th>
                                <th class="px-3 py-2.5 text-right">Valor total</th>
                                <th class="px-3 py-2.5">Situación</th>
                                <th class="px-3 py-2.5"></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 bg-white">
                            <template x-for="esc in escalas" :key="esc.escala">
                                <tr class="hover:bg-slate-50 transition-colors">
                                    <td class="px-3 py-2.5 text-right font-semibold text-slate-800" x-text="esc.escala"></td>
                                    <td class="px-3 py-2.5 text-slate-600" x-text="esc.moneda"></td>
                                    <td class="px-3 py-2.5 text-right text-slate-700" x-text="Number(esc.precio_unitario).toLocaleString('es-CO', {minimumFractionDigits: 2})"></td>
                                    <td class="px-3 py-2.5 text-right font-semibold text-slate-800" x-text="'$' + Number(esc.valor_total_escala).toLocaleString('es-CO')"></td>
                                    <td class="px-3 py-2.5">
                                        <span class="text-[10px] font-semibold px-1.5 py-0.5 rounded-full border"
                                              :class="esc.situacion_escala === 'CON PRECIO' ? 'bg-emerald-100 text-emerald-700 border-emerald-200' : 'bg-amber-100 text-amber-700 border-amber-200'"
                                              x-text="esc.situacion_escala"></span>
                                    </td>
                                    <td class="px-3 py-2.5 text-right">
                                        <a :href="urlCrear(esc)"
                                           class="text-xs font-semibold text-blue-700 hover:underline whitespace-nowrap">
                                            Usar esta escala →
                                        </a>
                                    </td>
                                </tr>
                            </template>
                        </tbody>
                    </table>
                </div>
            </div>
        </x-ui.modal>
    </div>
        @endif
        <x-ui.button href="{{ route('negocios.create') }}" variant="primary" size="sm">
            <x-ui.icon name="plus" class="w-4 h-4"/> Nuevo negocio
        </x-ui.button>
        </div>
    </x-slot>

    {{-- Filtros --}}
    <x-ui.card class="p-4 mb-4"
        x-data="{
            search: '{{ request('buscar') }}',
            estado: '{{ request('pipeline_estado_id') }}',
            goFilter() {
                let url = new URL(window.location.href);
                this.search ? url.searchParams.set('buscar', this.search) : url.searchParams.delete('buscar');
                this.estado ? url.searchParams.set('pipeline_estado_id', this.estado) : url.searchParams.delete('pipeline_estado_id');
                window.location.href = url.toString();
            }
        }"
    >
        <div class="flex flex-wrap gap-3">
            <div class="flex-1 min-w-48">
                <div class="relative">
                    <x-ui.icon name="search" class="w-4 h-4 absolute left-3 top-1/2 -translate-y-1/2 text-slate-400"/>
                    <input
                        type="text"
                        x-model="search"
                        @keydown.enter="goFilter()"
                        placeholder="Buscar negocio..."
                        class="w-full pl-9 pr-3 py-2 text-sm rounded-lg border border-slate-300 focus:outline-none focus:ring-2 focus:ring-blue-500"
                    >
                </div>
            </div>
            <select x-model="estado" @change="goFilter()"
                class="px-3 py-2 text-sm rounded-lg border border-slate-300 focus:outline-none focus:ring-2 focus:ring-blue-500">
                <option value="">Todos los estados</option>
                @foreach($estados as $e)
                    <option value="{{ $e->id }}" {{ request('pipeline_estado_id') == $e->id ? 'selected' : '' }}>
                        {{ $e->nombre }}
                    </option>
                @endforeach
            </select>
            @if(request()->hasAny(['buscar', 'pipeline_estado_id']))
                <x-ui.button href="{{ route('negocios.index') }}" variant="ghost" size="sm">
                    <x-ui.icon name="x" class="w-4 h-4"/> Limpiar
                </x-ui.button>
            @endif
        </div>
    </x-ui.card>

    {{-- Tabla --}}
    <x-ui.card>
        @if($negocios->isEmpty())
            <x-ui.empty-state icon="briefcase" title="Sin negocios"
                description="Registra tu primer negocio para comenzar el seguimiento comercial.">
                <x-slot name="action">
                    <x-ui.button href="{{ route('negocios.create') }}" variant="primary" size="sm">
                        <x-ui.icon name="plus" class="w-4 h-4"/> Crear negocio
                    </x-ui.button>
                </x-slot>
            </x-ui.empty-state>
        @else
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-slate-100">
                        <th class="text-left py-3 px-4 text-xs font-semibold text-slate-500 uppercase tracking-wide">Negocio</th>
                        <th class="text-left py-3 px-4 text-xs font-semibold text-slate-500 uppercase tracking-wide">Estado</th>
                        <th class="text-left py-3 px-4 text-xs font-semibold text-slate-500 uppercase tracking-wide">Compañía</th>
                        <th class="text-left py-3 px-4 text-xs font-semibold text-slate-500 uppercase tracking-wide">Valor</th>
                        <th class="text-left py-3 px-4 text-xs font-semibold text-slate-500 uppercase tracking-wide">Forecast</th>
                        <th class="text-left py-3 px-4 text-xs font-semibold text-slate-500 uppercase tracking-wide">Cierre</th>
                        <th class="text-left py-3 px-4 text-xs font-semibold text-slate-500 uppercase tracking-wide">Asesor</th>
                        <th class="py-3 px-4"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach($negocios as $n)
                    <tr class="hover:bg-slate-50 transition-colors">
                        <td class="py-3 px-4">
                            <p class="font-medium text-slate-900">{{ $n->nombre_negocio }}</p>
                            @if($n->prospecto)
                                <p class="text-xs text-slate-400">{{ $n->prospecto->empresa }}</p>
                            @endif
                        </td>
                        <td class="py-3 px-4">
                            @if($n->pipelineEstado)
                                <x-ui.badge :color="$n->pipelineEstado->color">
                                    {{ $n->pipelineEstado->nombre }}
                                </x-ui.badge>
                            @endif
                        </td>
                        <td class="py-3 px-4">
                            @if($n->compania)
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold {{ $n->compania === 1 ? 'bg-red-100 text-red-700' : 'bg-blue-100 text-blue-700' }}">
                                    {{ $n->companiaNombre() }}
                                </span>
                            @else
                                <span class="text-slate-300 text-xs">—</span>
                            @endif
                        </td>
                        <td class="py-3 px-4 font-medium text-slate-900">
                            ${{ number_format($n->valor_estimado, 0, ',', '.') }}
                        </td>
                        <td class="py-3 px-4 text-slate-600">
                            ${{ number_format($n->valorForecast(), 0, ',', '.') }}
                            <span class="text-xs text-slate-400">({{ $n->probabilidadEfectiva() }}%)</span>
                        </td>
                        <td class="py-3 px-4 text-slate-600">
                            {{ $n->fecha_estimada_cierre?->format('d/m/Y') ?? '—' }}
                        </td>
                        <td class="py-3 px-4 text-slate-600">{{ $n->asesor?->name }}</td>
                        <td class="py-3 px-4">
                            <div class="flex items-center gap-1 justify-end">
                                <x-ui.button href="{{ route('negocios.show', $n) }}" variant="ghost" size="xs">
                                    <x-ui.icon name="eye" class="w-4 h-4"/>
                                </x-ui.button>
                                @can('update', $n)
                                <x-ui.button href="{{ route('negocios.edit', $n) }}" variant="ghost" size="xs">
                                    <x-ui.icon name="edit" class="w-4 h-4"/>
                                </x-ui.button>
                                @endcan
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @if($negocios->hasPages())
            <div class="px-4 py-3 border-t border-slate-100">
                {{ $negocios->withQueryString()->links() }}
            </div>
        @endif
        @endif
    </x-ui.card>

@push('scripts')
<script>
    function candidatosNegocioSgp() {
        return {
            open: false,
            cargando: false,
            error: null,
            buscar: '',
            items: [],
            solicitudActual: null,
            cargandoEscalas: false,
            errorEscalas: null,
            escalas: [],
            abrir() {
                this.open = true;
                this.solicitudActual = null;
                this.cargar();
            },
            authHeaders() {
                const token = document.querySelector('meta[name="api-token"]')?.content;
                return {
                    'Accept': 'application/json',
                    ...(token ? { Authorization: `Bearer ${token}` } : {}),
                };
            },
            async cargar() {
                this.cargando = true;
                this.error = null;
                try {
                    const url = new URL('{{ route('api.negocios.candidatos-sgp') }}', window.location.origin);
                    if (this.buscar) url.searchParams.set('buscar', this.buscar);
                    const res = await fetch(url.toString(), { headers: this.authHeaders() });
                    const r = await res.json();
                    if (r.success) {
                        this.items = r.data;
                    } else {
                        this.error = r.message || 'No se pudieron cargar las solicitudes.';
                    }
                } catch (e) {
                    this.error = 'Error de conexión.';
                } finally {
                    this.cargando = false;
                }
            },
            async verEscalas(item) {
                this.solicitudActual = item;
                this.cargandoEscalas = true;
                this.errorEscalas = null;
                this.escalas = [];
                try {
                    const url = '{{ url('/api/negocios/candidatos-sgp') }}/' + item.nro_solicitud + '/escalas';
                    const res = await fetch(url, { headers: this.authHeaders() });
                    const r = await res.json();
                    if (r.success) {
                        this.escalas = r.data;
                    } else {
                        this.errorEscalas = r.message || 'No se pudieron cargar las escalas.';
                    }
                } catch (e) {
                    this.errorEscalas = 'Error de conexión.';
                } finally {
                    this.cargandoEscalas = false;
                }
            },
            volverALista() {
                this.solicitudActual = null;
                this.escalas = [];
            },
            urlCrear(escala) {
                const item = this.solicitudActual;
                const params = new URLSearchParams({
                    nombre_negocio: item.cliente || '',
                    valor_estimado: escala.valor_total_escala || 0,
                    descripcion: `Escala ${escala.escala} · ${item.tipo_cotizacion || ''} ${item.descripcion || ''}`.trim(),
                    nro_solicitud_cotizacion: item.nro_solicitud,
                });
                if (item.vinculo_tipo === 'prospecto') {
                    params.set('prospecto_id', item.prospecto_id);
                    params.set('prospecto_label', item.cliente + ' (' + item.nro_solicitud + ')');
                } else {
                    params.set('cliente_id', item.cliente_id);
                    params.set('cliente_label', item.cliente + (item.nit ? ' — ' + item.nit : ''));
                }
                return '{{ route('negocios.create') }}?' + params.toString();
            },
        };
    }
</script>
@endpush
</x-layouts.app>
