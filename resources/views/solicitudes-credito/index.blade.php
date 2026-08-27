<x-layouts.app title="Solicitudes de Crédito">
    <x-slot name="actions">
        @can('create', \App\Domain\SolicitudesCredito\Models\SolicitudCredito::class)
        <x-ui.button href="{{ route('solicitudes-credito.create') }}" variant="primary" size="sm">
            <x-ui.icon name="plus" class="w-4 h-4"/> Nueva solicitud
        </x-ui.button>
        @endcan
    </x-slot>

    {{-- Filtros --}}
    <x-ui.card class="p-4 mb-4"
        x-data="{
            estado: '{{ request('pipeline_estado_id') }}',
            goFilter() {
                let url = new URL(window.location.href);
                this.estado ? url.searchParams.set('pipeline_estado_id', this.estado) : url.searchParams.delete('pipeline_estado_id');
                window.location.href = url.toString();
            }
        }"
    >
        <div class="flex flex-wrap gap-3">
            <select x-model="estado" @change="goFilter()"
                class="px-3 py-2 text-sm rounded-lg border border-slate-300 focus:outline-none focus:ring-2 focus:ring-blue-500">
                <option value="">Todos los estados</option>
                @foreach($estados as $e)
                    <option value="{{ $e->id }}" {{ request('pipeline_estado_id') == $e->id ? 'selected' : '' }}>
                        {{ $e->nombre }}
                    </option>
                @endforeach
            </select>
            @if(request()->hasAny(['pipeline_estado_id']))
                <x-ui.button href="{{ route('solicitudes-credito.index') }}" variant="ghost" size="sm">
                    <x-ui.icon name="x" class="w-4 h-4"/> Limpiar
                </x-ui.button>
            @endif
        </div>
    </x-ui.card>

    {{-- Tabla --}}
    <x-ui.card>
        @if($solicitudes->isEmpty())
            <x-ui.empty-state icon="file-text" title="Sin solicitudes de crédito"
                description="Radica tu primera solicitud de crédito desde un negocio ganado o en negociación.">
                <x-slot name="action">
                    @can('create', \App\Domain\SolicitudesCredito\Models\SolicitudCredito::class)
                    <x-ui.button href="{{ route('solicitudes-credito.create') }}" variant="primary" size="sm">
                        <x-ui.icon name="plus" class="w-4 h-4"/> Nueva solicitud
                    </x-ui.button>
                    @endcan
                </x-slot>
            </x-ui.empty-state>
        @else
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-slate-100">
                        <th class="text-left py-3 px-4 text-xs font-semibold text-slate-500 uppercase tracking-wide">Negocio</th>
                        <th class="text-left py-3 px-4 text-xs font-semibold text-slate-500 uppercase tracking-wide">Cliente</th>
                        <th class="text-left py-3 px-4 text-xs font-semibold text-slate-500 uppercase tracking-wide">Monto</th>
                        <th class="text-left py-3 px-4 text-xs font-semibold text-slate-500 uppercase tracking-wide">Estado</th>
                        <th class="text-left py-3 px-4 text-xs font-semibold text-slate-500 uppercase tracking-wide">Asesor</th>
                        <th class="text-left py-3 px-4 text-xs font-semibold text-slate-500 uppercase tracking-wide">Fecha</th>
                        <th class="py-3 px-4"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach($solicitudes as $s)
                    <tr class="hover:bg-slate-50 transition-colors">
                        <td class="py-3 px-4 font-medium text-slate-900">
                            {{ $s->negocio?->nombre_negocio ?? 'Cupo inicial (sin negocio)' }}
                        </td>
                        <td class="py-3 px-4">
                            <p class="text-slate-900">{{ $s->cliente?->razon_social }}</p>
                            <p class="text-xs font-mono text-slate-400">{{ $s->cliente?->nit }}</p>
                        </td>
                        <td class="py-3 px-4 font-medium text-slate-900">
                            ${{ number_format($s->monto_solicitado, 0, ',', '.') }}
                        </td>
                        <td class="py-3 px-4">
                            @if($s->pipelineEstado)
                                <x-ui.badge :color="$s->pipelineEstado->color">
                                    {{ $s->pipelineEstado->nombre }}
                                </x-ui.badge>
                            @endif
                        </td>
                        <td class="py-3 px-4 text-slate-600">{{ $s->asesor?->name }}</td>
                        <td class="py-3 px-4 text-slate-600">{{ $s->created_at->format('d/m/Y') }}</td>
                        <td class="py-3 px-4">
                            <div class="flex items-center gap-1 justify-end">
                                <x-ui.button href="{{ route('solicitudes-credito.show', $s) }}" variant="ghost" size="xs">
                                    <x-ui.icon name="eye" class="w-4 h-4"/>
                                </x-ui.button>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @if($solicitudes->hasPages())
            <div class="px-4 py-3 border-t border-slate-100">
                {{ $solicitudes->withQueryString()->links() }}
            </div>
        @endif
        @endif
    </x-ui.card>
</x-layouts.app>
