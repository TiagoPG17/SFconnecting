<x-layouts.app title="Prospectos">
    <x-slot name="actions">
        <x-ui.button href="{{ route('prospectos.kanban') }}" variant="secondary" size="sm">
            <x-ui.icon name="layout" class="w-4 h-4"/> Kanban
        </x-ui.button>
        <x-ui.button href="{{ route('prospectos.create') }}" variant="primary" size="sm">
            <x-ui.icon name="plus" class="w-4 h-4"/> Nuevo prospecto
        </x-ui.button>
    </x-slot>

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
                    <x-ui.button href="{{ route('prospectos.create') }}" variant="primary" size="sm">
                        <x-ui.icon name="plus" class="w-4 h-4"/> Crear prospecto
                    </x-ui.button>
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
                                <x-ui.button href="{{ route('prospectos.edit', $p) }}" variant="ghost" size="xs">
                                    <x-ui.icon name="edit" class="w-4 h-4"/>
                                </x-ui.button>
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
</x-layouts.app>
