<x-layouts.app title="Contabilidad — Clientes pendientes">
    <x-ui.card class="p-4 mb-4" x-data="{ search: '{{ request('buscar') }}', buscando: false }">
        <div class="flex flex-wrap gap-3">
            <div class="flex-1 min-w-48">
                <div class="relative">
                    <x-ui.icon name="search" class="w-4 h-4 absolute left-3 top-1/2 -translate-y-1/2 text-slate-400" x-show="!buscando"/>
                    <svg x-show="buscando" class="w-4 h-4 absolute left-3 top-1/2 -translate-y-1/2 text-blue-400 animate-spin" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"/>
                    </svg>
                    <input type="text" x-model="search"
                        @input="buscando = true"
                        @input.debounce.800ms="window.location.href = '{{ route('contabilidad.index') }}?buscar=' + encodeURIComponent(search)"
                        placeholder="Buscar razón social o NIT..."
                        class="w-full pl-9 pr-3 py-2 text-sm rounded-lg border border-slate-300 focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
            </div>
            @if(request('buscar'))
                <x-ui.button href="{{ route('contabilidad.index') }}" variant="ghost" size="sm">
                    <x-ui.icon name="x" class="w-4 h-4"/> Limpiar
                </x-ui.button>
            @endif
        </div>
    </x-ui.card>

    <x-ui.card>
        @if($clientes->isEmpty())
            <x-ui.empty-state icon="file-text" title="Sin clientes pendientes"
                description="{{ request('buscar') ? 'No hay resultados para la búsqueda.' : 'No hay clientes nuevos esperando registro en SIESA por el momento.' }}">
            </x-ui.empty-state>
        @else
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-slate-100">
                        <th class="text-left py-3 px-4 text-xs font-semibold text-slate-500 uppercase tracking-wide">Cliente</th>
                        <th class="text-left py-3 px-4 text-xs font-semibold text-slate-500 uppercase tracking-wide">NIT</th>
                        <th class="text-left py-3 px-4 text-xs font-semibold text-slate-500 uppercase tracking-wide">Compañía</th>
                        <th class="text-left py-3 px-4 text-xs font-semibold text-slate-500 uppercase tracking-wide">Comercial</th>
                        <th class="text-left py-3 px-4 text-xs font-semibold text-slate-500 uppercase tracking-wide">Fecha</th>
                        <th class="py-3 px-4"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach($clientes as $c)
                    <tr class="hover:bg-slate-50 transition-colors">
                        <td class="py-3 px-4 font-medium text-slate-900">{{ $c->razon_social }}</td>
                        <td class="py-3 px-4 font-mono text-slate-500">{{ $c->nit }}</td>
                        <td class="py-3 px-4 text-slate-600">{{ $c->companiaNombre() ?? '—' }}</td>
                        <td class="py-3 px-4 text-slate-600">{{ $c->asesor?->name }}</td>
                        <td class="py-3 px-4 text-slate-600">{{ $c->created_at->format('d/m/Y') }}</td>
                        <td class="py-3 px-4">
                            <div class="flex items-center gap-1 justify-end">
                                <x-ui.button href="{{ route('contabilidad.show', $c) }}" variant="ghost" size="xs">
                                    <x-ui.icon name="eye" class="w-4 h-4"/>
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
