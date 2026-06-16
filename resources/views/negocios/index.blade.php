<x-layouts.app title="Negocios">
    <x-slot name="actions">
        <x-ui.button href="{{ route('negocios.kanban') }}" variant="secondary" size="sm">
            <x-ui.icon name="layout" class="w-4 h-4"/> Kanban
        </x-ui.button>
        <x-ui.button href="{{ route('negocios.create') }}" variant="primary" size="sm">
            <x-ui.icon name="plus" class="w-4 h-4"/> Nuevo negocio
        </x-ui.button>
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
</x-layouts.app>
