<x-layouts.app title="Pipeline de Prospectos">
    <x-slot name="actions">
        <x-ui.button href="{{ route('prospectos.index') }}" variant="ghost" size="sm">
            <x-ui.icon name="list" class="w-4 h-4"/> Lista
        </x-ui.button>
        @unlessrole('gerente')
        <x-ui.button href="{{ route('prospectos.create') }}" variant="primary" size="sm">
            <x-ui.icon name="plus" class="w-4 h-4"/> Nuevo prospecto
        </x-ui.button>
        @endunlessrole
    </x-slot>

    <div
        x-data="{
            moving: null,
            async mover(prospectoId, estadoId) {
                const r = await $api('PUT', '/api/prospectos/' + prospectoId, { estado_pipeline_id: estadoId });
                if (!r.success) $store.toast.error(r.message ?? 'Error al mover');
            }
        }"
        class="flex gap-4 overflow-x-auto pb-4"
        style="min-height: calc(100vh - 10rem);"
    >
        @foreach($columnas as $columna)
        <div class="flex-none w-72 flex flex-col">
            {{-- Header columna --}}
            <div class="flex items-center justify-between mb-3 px-1">
                <div class="flex items-center gap-2">
                    <div class="w-2.5 h-2.5 rounded-full" style="background-color: {{ $columna['estado']->color ?? '#94a3b8' }}"></div>
                    <span class="text-sm font-semibold text-slate-700">{{ $columna['estado']->nombre }}</span>
                    <span class="text-xs text-slate-400 bg-slate-100 px-1.5 py-0.5 rounded-full">{{ $columna['total'] }}</span>
                </div>
                @if($columna['valor'] > 0)
                <span class="text-xs text-slate-500">${{ number_format($columna['valor'], 0, ',', '.') }}</span>
                @endif
            </div>

            {{-- Columna droppable --}}
            <div
                x-sort:group="prospectos"
                x-sort="(itemId, position, { from, to }) => {
                    if (from !== to) mover(itemId, {{ $columna['estado']->id }})
                }"
                class="flex-1 flex flex-col gap-2 min-h-24 p-2 rounded-xl bg-slate-100/60"
            >
                @forelse($columna['prospectos'] as $p)
                <div
                    x-sort:item="{{ $p->id }}"
                    class="bg-white rounded-lg p-3 shadow-sm border border-slate-200 hover:border-blue-300 cursor-grab active:cursor-grabbing transition-colors"
                >
                    <div class="flex items-start justify-between gap-2 mb-2">
                        <div class="min-w-0">
                            <p class="text-sm font-medium text-slate-900 truncate">{{ $p->empresa }}</p>
                            <p class="text-xs text-slate-400">{{ $p->contacto }}</p>
                        </div>
                        @if($p->prioridad)
                            <x-ui.badge :color="$p->prioridad->color" class="shrink-0 text-xs">
                                {{ $p->prioridad->nombre }}
                            </x-ui.badge>
                        @endif
                    </div>

                    @if($p->valor_estimado)
                    <p class="text-xs text-slate-600 font-medium mb-2">
                        ${{ number_format($p->valor_estimado, 0, ',', '.') }}
                    </p>
                    @endif

                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-1.5">
                            <x-ui.progress :value="$p->probabilidadEfectiva()" size="xs" class="w-14"/>
                            <span class="text-xs text-slate-400">{{ $p->probabilidadEfectiva() }}%</span>
                        </div>
                        <a href="{{ route('prospectos.show', $p) }}"
                            class="text-xs text-blue-600 hover:text-blue-700"
                            @click.stop
                        >Ver</a>
                    </div>
                </div>
                @empty
                <div class="text-center py-6 text-xs text-slate-400">Sin prospectos</div>
                @endforelse
            </div>
        </div>
        @endforeach
    </div>
</x-layouts.app>
