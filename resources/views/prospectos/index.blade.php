<x-layouts.app title="Prospectos">
    <x-slot name="actions">
        <x-ui.button href="{{ route('prospectos.kanban') }}" variant="secondary" size="sm">
            <x-ui.icon name="layout" class="w-4 h-4"/> Kanban
        </x-ui.button>
        @unlessrole('gerente')
        <x-ui.button href="{{ route('prospectos.create') }}" variant="primary" size="sm">
            <x-ui.icon name="plus" class="w-4 h-4"/> Nuevo prospecto
        </x-ui.button>
        @endunlessrole
    </x-slot>

    @can('create', \App\Domain\Prospectos\Models\Prospecto::class)
    <div class="mb-4" x-data="candidatosSgp()">
        <button type="button" @click="abrir()"
                class="inline-flex items-center gap-1.5 text-xs px-3 py-2 rounded-xl border border-slate-200 bg-white text-slate-600 hover:bg-slate-50 font-medium transition-colors">
            <x-ui.icon name="bar-chart" class="w-3.5 h-3.5"/>
            Cargar solicitudes de cotización (SGP)
        </button>

        <x-ui.modal title="Solicitudes de cotización sin cliente asignado" size="xl">
            <div class="space-y-3">
                <div class="flex items-center justify-between gap-3">
                    <div class="relative flex-1">
                        <x-ui.icon name="search" class="w-4 h-4 absolute left-3 top-1/2 -translate-y-1/2 text-slate-400"/>
                        <input type="text" x-model="buscar" @input.debounce.400ms="cargar()"
                               placeholder="Buscar por NIT o nombre del cliente..."
                               class="w-full pl-9 pr-3 py-2 text-sm rounded-lg border border-slate-300 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <span class="shrink-0 text-xs text-slate-400">Últimos 30 días</span>
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
                                    <th class="px-3 py-2.5">Solicitud</th>
                                    <th class="px-3 py-2.5">Fecha</th>
                                    <th class="px-3 py-2.5">Comercial</th>
                                    <th class="px-3 py-2.5"></th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 bg-white">
                                <template x-for="item in items" :key="item.nro_solicitud">
                                    <tr class="hover:bg-slate-50 transition-colors">
                                        <td class="px-3 py-2.5 max-w-[220px]">
                                            <div class="flex items-center gap-1.5">
                                                <p class="font-medium text-slate-900 truncate" x-text="item.cliente || 'Sin nombre'"></p>
                                                <span x-show="item.posible_duplicado_prospecto"
                                                      class="shrink-0 text-[10px] font-semibold px-1.5 py-0.5 rounded-full bg-amber-100 text-amber-700 border border-amber-200">
                                                    Duplicado
                                                </span>
                                            </div>
                                            <p class="text-xs text-slate-400" x-text="'NIT: ' + (item.nit || '—')"></p>
                                        </td>
                                        <td class="px-3 py-2.5 font-mono text-xs text-slate-600 whitespace-nowrap" x-text="item.nro_solicitud"></td>
                                        <td class="px-3 py-2.5 text-xs text-slate-500 whitespace-nowrap" x-text="item.fecha_solicitud"></td>
                                        <td class="px-3 py-2.5 text-xs text-slate-500 max-w-[140px] truncate" x-text="item.comercial"></td>
                                        <td class="px-3 py-2.5 text-right">
                                            <a :href="urlCrear(item)"
                                               class="text-xs font-semibold text-blue-700 hover:underline whitespace-nowrap">
                                                Usar este →
                                            </a>
                                        </td>
                                    </tr>
                                </template>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </x-ui.modal>
    </div>
    @endcan

    {{-- Filtros --}}
    <x-ui.card class="p-4 mb-4"
        x-data="{
            search: '{{ request('buscar') }}',
            estado: '{{ request('estado_pipeline_id') }}',
            goFilter() {
                let url = new URL(window.location.href);
                this.search ? url.searchParams.set('buscar', this.search) : url.searchParams.delete('buscar');
                this.estado ? url.searchParams.set('estado_pipeline_id', this.estado) : url.searchParams.delete('estado_pipeline_id');
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
                        placeholder="Buscar empresa, contacto..."
                        class="w-full pl-9 pr-3 py-2 text-sm rounded-lg border border-slate-300 focus:outline-none focus:ring-2 focus:ring-blue-500"
                    >
                </div>
            </div>
            <select x-model="estado" @change="goFilter()"
                class="px-3 py-2 text-sm rounded-lg border border-slate-300 focus:outline-none focus:ring-2 focus:ring-blue-500">
                <option value="">Todos los estados</option>
                @foreach($estados as $e)
                    <option value="{{ $e->id }}" {{ request('estado_pipeline_id') == $e->id ? 'selected' : '' }}>
                        {{ $e->nombre }}
                    </option>
                @endforeach
            </select>
            @if(request()->hasAny(['buscar', 'estado_pipeline_id']))
                <x-ui.button href="{{ route('prospectos.index') }}" variant="ghost" size="sm">
                    <x-ui.icon name="x" class="w-4 h-4"/> Limpiar
                </x-ui.button>
            @endif
        </div>
    </x-ui.card>

    {{-- Tabla --}}
    <x-ui.card>
        @if($prospectos->isEmpty())
            <x-ui.empty-state icon="user-plus" title="Sin prospectos"
                description="Crea tu primer prospecto para comenzar el pipeline comercial.">
                <x-slot name="action">
                    @unlessrole('gerente')
                    <x-ui.button href="{{ route('prospectos.create') }}" variant="primary" size="sm">
                        <x-ui.icon name="plus" class="w-4 h-4"/> Crear prospecto
                    </x-ui.button>
                    @endunlessrole
                </x-slot>
            </x-ui.empty-state>
        @else
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-slate-100">
                        <th class="text-left py-3 px-4 text-xs font-semibold text-slate-500 uppercase tracking-wide">Empresa</th>
                        <th class="text-left py-3 px-4 text-xs font-semibold text-slate-500 uppercase tracking-wide">Contacto</th>
                        <th class="text-left py-3 px-4 text-xs font-semibold text-slate-500 uppercase tracking-wide">Estado</th>
                        <th class="text-left py-3 px-4 text-xs font-semibold text-slate-500 uppercase tracking-wide">Valor</th>
                        <th class="text-left py-3 px-4 text-xs font-semibold text-slate-500 uppercase tracking-wide">Prob.</th>
                        <th class="text-left py-3 px-4 text-xs font-semibold text-slate-500 uppercase tracking-wide">Asesor</th>
                        <th class="py-3 px-4"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach($prospectos as $p)
                    <tr class="hover:bg-slate-50 transition-colors">
                        <td class="py-3 px-4">
                            <div>
                                <p class="font-medium text-slate-900">{{ $p->empresa }}</p>
                                <p class="text-xs text-slate-500">{{ $p->codigo }}</p>
                            </div>
                        </td>
                        <td class="py-3 px-4">
                            <p class="text-slate-700">{{ $p->contacto }}</p>
                            @if($p->email)
                                <p class="text-xs text-slate-400">{{ $p->email }}</p>
                            @endif
                        </td>
                        <td class="py-3 px-4">
                            @if($p->estadoPipeline)
                                <x-ui.badge :color="$p->estadoPipeline->color">
                                    {{ $p->estadoPipeline->nombre }}
                                </x-ui.badge>
                            @endif
                        </td>
                        <td class="py-3 px-4 font-medium text-slate-900">
                            {{ $p->valor_estimado ? '$' . number_format($p->valor_estimado, 0, ',', '.') : '—' }}
                        </td>
                        <td class="py-3 px-4">
                            <div class="flex items-center gap-2">
                                <x-ui.progress :value="$p->probabilidadEfectiva()" size="xs" class="w-16"/>
                                <span class="text-xs text-slate-500">{{ $p->probabilidadEfectiva() }}%</span>
                            </div>
                        </td>
                        <td class="py-3 px-4 text-slate-600">{{ $p->asesor?->name }}</td>
                        <td class="py-3 px-4">
                            <div class="flex items-center gap-1 justify-end">
                                <x-ui.button href="{{ route('prospectos.show', $p) }}" variant="ghost" size="xs">
                                    <x-ui.icon name="eye" class="w-4 h-4"/>
                                </x-ui.button>
                                @can('update', $p)
                                <x-ui.button href="{{ route('prospectos.edit', $p) }}" variant="ghost" size="xs">
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
        @if($prospectos->hasPages())
            <div class="px-4 py-3 border-t border-slate-100">
                {{ $prospectos->withQueryString()->links() }}
            </div>
        @endif
        @endif
    </x-ui.card>

@push('scripts')
<script>
    function candidatosSgp() {
        return {
            open: false,
            cargando: false,
            error: null,
            buscar: '',
            items: [],
            abrir() {
                this.open = true;
                this.cargar();
            },
            async cargar() {
                this.cargando = true;
                this.error = null;
                try {
                    const url = new URL('{{ route('api.prospectos.candidatos-sgp') }}', window.location.origin);
                    if (this.buscar) url.searchParams.set('buscar', this.buscar);
                    const token = document.querySelector('meta[name="api-token"]')?.content;
                    const res = await fetch(url.toString(), {
                        headers: {
                            'Accept': 'application/json',
                            ...(token ? { Authorization: `Bearer ${token}` } : {}),
                        },
                    });
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
            urlCrear(item) {
                const params = new URLSearchParams({
                    empresa: item.cliente || '',
                    nro_solicitud_cotizacion: item.nro_solicitud,
                });
                return '{{ route('prospectos.create') }}?' + params.toString();
            },
        };
    }
</script>
@endpush
</x-layouts.app>
