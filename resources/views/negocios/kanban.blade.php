<x-layouts.app title="Pipeline Kanban">
    <x-slot name="actions">
        <x-ui.button href="{{ route('negocios.index') }}" variant="secondary" size="sm">
            <x-ui.icon name="bar-chart" class="w-4 h-4"/> Lista
        </x-ui.button>
        <x-ui.button href="{{ route('negocios.create') }}" variant="primary" size="sm">
            <x-ui.icon name="plus" class="w-4 h-4"/> Nuevo negocio
        </x-ui.button>
    </x-slot>

    {{-- Kanban board --}}
    <div
        class="flex gap-4 overflow-x-auto pb-4 min-h-[calc(100vh-10rem)]"
        x-data="kanban()"
        x-init="init()"
    >
        @foreach($columnas as $columna)
        <div class="flex-none w-72 flex flex-col">
            {{-- Header columna --}}
            <div class="flex items-center gap-2 mb-3 px-1">
                <div class="w-2.5 h-2.5 rounded-full shrink-0"
                     style="background-color: {{ $columna['estado']->color }}"></div>
                <h3 class="text-sm font-semibold text-slate-700 flex-1 truncate">
                    {{ $columna['estado']->nombre }}
                </h3>
                <span class="text-xs font-medium text-slate-500 bg-slate-100 rounded-full px-2 py-0.5">
                    {{ count($columna['negocios']) }}
                </span>
            </div>

            {{-- Valor columna --}}
            <div class="mb-3 px-1">
                <p class="text-xs text-slate-500">
                    ${{ number_format($columna['valor_total'], 0, ',', '.') }} pipeline
                    · ${{ number_format($columna['valor_forecast'], 0, ',', '.') }} forecast
                </p>
            </div>

            {{-- Cards --}}
            <div
                class="flex-1 space-y-2 rounded-xl bg-slate-100/60 p-2 min-h-24"
                x-sort:group="negocios"
                x-sort="mover($item, $position, '{{ $columna['estado']->id }}')"
                data-estado-id="{{ $columna['estado']->id }}"
            >
                @forelse($columna['negocios'] as $negocio)
                <div
                    x-sort:item="{{ $negocio->id }}"
                    class="bg-white rounded-xl p-3 shadow-sm border border-slate-200 cursor-grab active:cursor-grabbing hover:shadow-md transition-shadow"
                >
                    <div class="flex items-start justify-between gap-2 mb-2">
                        <p class="text-sm font-medium text-slate-900 leading-tight">
                            {{ $negocio->nombre_negocio }}
                        </p>
                        @if($negocio->pipelineEstado)
                            <x-ui.badge :color="$negocio->pipelineEstado->color" size="xs">
                                {{ $negocio->pipelineEstado->porcentaje_cierre }}%
                            </x-ui.badge>
                        @endif
                    </div>

                    <p class="text-xs text-slate-500 mb-2 truncate">
                        {{ $negocio->cliente?->razon_social ?? $negocio->prospecto?->empresa ?? '—' }}
                    </p>

                    <div class="flex items-center justify-between">
                        <p class="text-sm font-semibold text-slate-900">
                            ${{ number_format($negocio->valor_estimado, 0, ',', '.') }}
                        </p>
                        <div class="flex items-center gap-1">
                            @if($negocio->fecha_estimada_cierre)
                                <span class="text-xs text-slate-400">
                                    {{ $negocio->fecha_estimada_cierre->format('d/m') }}
                                </span>
                            @endif
                            <div class="w-6 h-6 rounded-full bg-blue-100 text-blue-700 flex items-center justify-center text-xs font-semibold"
                                 title="{{ $negocio->asesor?->name }}">
                                {{ substr($negocio->asesor?->name ?? 'U', 0, 1) }}
                            </div>
                        </div>
                    </div>

                    <div class="mt-2">
                        <x-ui.progress :value="$negocio->probabilidadEfectiva()" size="xs"/>
                    </div>
                </div>
                @empty
                <div class="flex items-center justify-center h-16 text-xs text-slate-400 italic">
                    Sin negocios
                </div>
                @endforelse
            </div>
        </div>
        @endforeach
    </div>

    @push('scripts')
    <script>
    function kanban() {
        return {
            init() {},
            async mover(itemId, position, estadoId) {
                try {
                    const res = await this.$api('PUT', `/api/negocios/${itemId}`, {
                        pipeline_estado_id: parseInt(estadoId),
                    });
                    if (!res.success) {
                        this.$store.toast.error('Error al mover el negocio: ' + res.message);
                    } else {
                        this.$store.toast.success('Negocio actualizado.');
                    }
                } catch (e) {
                    this.$store.toast.error('Error de conexión.');
                }
            }
        };
    }
    </script>
    @endpush
</x-layouts.app>
