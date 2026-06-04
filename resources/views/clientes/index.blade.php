<x-layouts.app title="Clientes">
    <x-slot name="actions">
        <x-ui.button href="{{ route('clientes.create') }}" variant="primary" size="sm">
            <x-ui.icon name="plus" class="w-4 h-4"/> Nuevo cliente
        </x-ui.button>
    </x-slot>

    {{-- Filtros --}}
    <x-ui.card class="p-4 mb-4"
        x-data="{
            search: '{{ request('buscar') }}',
            estado: '{{ request('estado') }}',
            goFilter() {
                let url = new URL(window.location.href);
                this.search ? url.searchParams.set('buscar', this.search) : url.searchParams.delete('buscar');
                this.estado ? url.searchParams.set('estado', this.estado) : url.searchParams.delete('estado');
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
                        placeholder="Buscar razón social, NIT, email..."
                        class="w-full pl-9 pr-3 py-2 text-sm rounded-lg border border-slate-300 focus:outline-none focus:ring-2 focus:ring-blue-500"
                    >
                </div>
            </div>
            <select x-model="estado" @change="goFilter()"
                class="px-3 py-2 text-sm rounded-lg border border-slate-300 focus:outline-none focus:ring-2 focus:ring-blue-500">
                <option value="">Todos los estados</option>
                <option value="activo" {{ request('estado') === 'activo' ? 'selected' : '' }}>Activo</option>
                <option value="inactivo" {{ request('estado') === 'inactivo' ? 'selected' : '' }}>Inactivo</option>
                <option value="prospecto" {{ request('estado') === 'prospecto' ? 'selected' : '' }}>Prospecto</option>
            </select>
            @if(request()->hasAny(['buscar', 'estado']))
                <x-ui.button href="{{ route('clientes.index') }}" variant="ghost" size="sm">
                    <x-ui.icon name="x" class="w-4 h-4"/> Limpiar
                </x-ui.button>
            @endif
        </div>
    </x-ui.card>

    {{-- Tabla --}}
    <x-ui.card>
        @if($clientes->isEmpty())
            <x-ui.empty-state icon="users" title="Sin clientes"
                description="Registra tu primer cliente o conviértelo desde un prospecto.">
                <x-slot name="action">
                    <x-ui.button href="{{ route('clientes.create') }}" variant="primary" size="sm">
                        <x-ui.icon name="plus" class="w-4 h-4"/> Crear cliente
                    </x-ui.button>
                </x-slot>
            </x-ui.empty-state>
        @else
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-slate-100">
                        <th class="text-left py-3 px-4 text-xs font-semibold text-slate-500 uppercase tracking-wide">Razón social</th>
                        <th class="text-left py-3 px-4 text-xs font-semibold text-slate-500 uppercase tracking-wide">NIT</th>
                        <th class="text-left py-3 px-4 text-xs font-semibold text-slate-500 uppercase tracking-wide">Contacto</th>
                        <th class="text-left py-3 px-4 text-xs font-semibold text-slate-500 uppercase tracking-wide">Ciudad</th>
                        <th class="text-left py-3 px-4 text-xs font-semibold text-slate-500 uppercase tracking-wide">Estado</th>
                        <th class="text-left py-3 px-4 text-xs font-semibold text-slate-500 uppercase tracking-wide">Asesor</th>
                        <th class="py-3 px-4"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach($clientes as $c)
                    <tr class="hover:bg-slate-50 transition-colors">
                        <td class="py-3 px-4">
                            <p class="font-medium text-slate-900">{{ $c->razon_social }}</p>
                        </td>
                        <td class="py-3 px-4 text-slate-600 font-mono text-xs">{{ $c->nit }}</td>
                        <td class="py-3 px-4">
                            @if($c->email)
                                <p class="text-slate-700">{{ $c->email }}</p>
                            @endif
                            @if($c->telefono)
                                <p class="text-xs text-slate-400">{{ $c->telefono }}</p>
                            @endif
                        </td>
                        <td class="py-3 px-4 text-slate-600">{{ $c->ciudad ?? '—' }}</td>
                        <td class="py-3 px-4">
                            @php
                                $colorEstado = match($c->estado) {
                                    'activo'    => 'green',
                                    'inactivo'  => 'red',
                                    default     => 'yellow',
                                };
                            @endphp
                            <x-ui.badge :color="$colorEstado">{{ ucfirst($c->estado) }}</x-ui.badge>
                        </td>
                        <td class="py-3 px-4 text-slate-600">{{ $c->asesor?->name ?? '—' }}</td>
                        <td class="py-3 px-4">
                            <div class="flex items-center gap-1 justify-end">
                                <x-ui.button href="{{ route('clientes.show', $c) }}" variant="ghost" size="xs">
                                    <x-ui.icon name="eye" class="w-4 h-4"/>
                                </x-ui.button>
                                <x-ui.button href="{{ route('clientes.edit', $c) }}" variant="ghost" size="xs">
                                    <x-ui.icon name="edit" class="w-4 h-4"/>
                                </x-ui.button>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @if($clientes->hasPages())
            <div class="px-4 py-3 border-t border-slate-100">
                {{ $clientes->withQueryString()->links() }}
            </div>
        @endif
        @endif
    </x-ui.card>
</x-layouts.app>
