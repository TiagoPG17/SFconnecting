<x-layouts.app title="Seguimientos">

    {{-- Banner cliente seleccionado --}}
    @if($clienteSeleccionado)
    <div class="mb-4 flex items-center gap-3 px-4 py-2.5 bg-blue-50 border border-blue-200 rounded-xl text-sm text-blue-700">
        <x-ui.icon name="users" class="w-4 h-4 shrink-0"/>
        <span>Filtrando por cliente: <strong>{{ $clienteSeleccionado->razon_social }}</strong></span>
        <a href="{{ route('seguimientos.index') }}" class="ml-auto text-blue-500 hover:text-blue-700">
            <x-ui.icon name="x" class="w-4 h-4"/>
        </a>
    </div>
    @endif

    {{-- Filtros --}}
    <x-ui.card class="p-4 mb-4"
        x-data="{
            tipo: '{{ request('tipo') }}',
            resultado: '{{ request('resultado') }}',
            desde: '{{ request('fecha_desde') }}',
            hasta: '{{ request('fecha_hasta') }}',
            goFilter() {
                let url = new URL(window.location.href);
                this.tipo     ? url.searchParams.set('tipo', this.tipo)         : url.searchParams.delete('tipo');
                this.resultado ? url.searchParams.set('resultado', this.resultado) : url.searchParams.delete('resultado');
                this.desde    ? url.searchParams.set('fecha_desde', this.desde) : url.searchParams.delete('fecha_desde');
                this.hasta    ? url.searchParams.set('fecha_hasta', this.hasta) : url.searchParams.delete('fecha_hasta');
                window.location.href = url.toString();
            }
        }"
    >
        <div class="flex flex-wrap gap-3">
            <select x-model="tipo" @change="goFilter()"
                class="px-3 py-2 text-sm rounded-lg border border-slate-300 focus:outline-none focus:ring-2 focus:ring-blue-500">
                <option value="">Todos los tipos</option>
                <option value="llamada">Llamada</option>
                <option value="reunion">Reunión</option>
                <option value="email">Email</option>
                <option value="visita">Visita</option>
                <option value="otro">Otro</option>
            </select>
            <select x-model="resultado" @change="goFilter()"
                class="px-3 py-2 text-sm rounded-lg border border-slate-300 focus:outline-none focus:ring-2 focus:ring-blue-500">
                <option value="">Todos los resultados</option>
                <option value="exitoso">Exitoso</option>
                <option value="pendiente">Pendiente</option>
                <option value="fallido">Fallido</option>
            </select>
            <input type="date" x-model="desde" @change="goFilter()"
                class="px-3 py-2 text-sm rounded-lg border border-slate-300 focus:outline-none focus:ring-2 focus:ring-blue-500"
                placeholder="Desde">
            <input type="date" x-model="hasta" @change="goFilter()"
                class="px-3 py-2 text-sm rounded-lg border border-slate-300 focus:outline-none focus:ring-2 focus:ring-blue-500"
                placeholder="Hasta">
            @if(request()->hasAny(['tipo', 'resultado', 'fecha_desde', 'fecha_hasta']))
                <x-ui.button href="{{ route('seguimientos.index', request()->only('cliente_id')) }}" variant="ghost" size="sm">
                    <x-ui.icon name="x" class="w-4 h-4"/> Limpiar
                </x-ui.button>
            @endif
        </div>
    </x-ui.card>

    {{-- Tabla --}}
    <x-ui.card>
        @if($seguimientos->isEmpty())
            <x-ui.empty-state icon="clock" title="Sin seguimientos"
                description="Los seguimientos se registran desde la ficha de cada cliente."/>
        @else
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-slate-100">
                        <th class="text-left py-3 px-4 text-xs font-semibold text-slate-500 uppercase tracking-wide">Cliente</th>
                        <th class="text-left py-3 px-4 text-xs font-semibold text-slate-500 uppercase tracking-wide">Tipo</th>
                        <th class="text-left py-3 px-4 text-xs font-semibold text-slate-500 uppercase tracking-wide">Resultado</th>
                        <th class="text-left py-3 px-4 text-xs font-semibold text-slate-500 uppercase tracking-wide">Descripción</th>
                        <th class="text-left py-3 px-4 text-xs font-semibold text-slate-500 uppercase tracking-wide">Fecha</th>
                        <th class="text-left py-3 px-4 text-xs font-semibold text-slate-500 uppercase tracking-wide">Próxima</th>
                        <th class="text-left py-3 px-4 text-xs font-semibold text-slate-500 uppercase tracking-wide">Asesor</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach($seguimientos as $seg)
                    <tr class="hover:bg-slate-50 transition-colors">
                        <td class="py-3 px-4">
                            @if($seg->cliente)
                                <a href="{{ route('clientes.show', $seg->cliente) }}"
                                    class="font-medium text-blue-600 hover:underline">
                                    {{ $seg->cliente->razon_social }}
                                </a>
                            @else
                                <span class="text-slate-400">—</span>
                            @endif
                        </td>
                        <td class="py-3 px-4">
                            <x-ui.badge color="blue" class="text-xs">{{ ucfirst($seg->tipo) }}</x-ui.badge>
                        </td>
                        <td class="py-3 px-4">
                            @php
                                $colorRes = match($seg->resultado) {
                                    'exitoso'  => 'green',
                                    'fallido'  => 'red',
                                    default    => 'yellow',
                                };
                            @endphp
                            <x-ui.badge :color="$colorRes" class="text-xs">{{ ucfirst($seg->resultado) }}</x-ui.badge>
                        </td>
                        <td class="py-3 px-4 text-slate-700 max-w-xs truncate">{{ $seg->descripcion }}</td>
                        <td class="py-3 px-4 text-slate-600 whitespace-nowrap">
                            {{ $seg->fecha_seguimiento->format('d/m/Y H:i') }}
                        </td>
                        <td class="py-3 px-4 text-slate-600 whitespace-nowrap">
                            @if($seg->proxima_fecha)
                                <span class="{{ $seg->proxima_fecha->isPast() ? 'text-red-500 font-medium' : '' }}">
                                    {{ $seg->proxima_fecha->format('d/m/Y H:i') }}
                                </span>
                            @else
                                <span class="text-slate-400">—</span>
                            @endif
                        </td>
                        <td class="py-3 px-4 text-slate-600">{{ $seg->asesor?->name ?? '—' }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @if($seguimientos->hasPages())
            <div class="px-4 py-3 border-t border-slate-100">
                {{ $seguimientos->withQueryString()->links() }}
            </div>
        @endif
        @endif
    </x-ui.card>
</x-layouts.app>
