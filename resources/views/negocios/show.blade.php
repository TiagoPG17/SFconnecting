<x-layouts.app :title="$negocio->nombre_negocio">
    <x-slot name="actions">
        <x-ui.button href="{{ route('negocios.index') }}" variant="ghost" size="sm">
            <x-ui.icon name="arrow-left" class="w-4 h-4"/> Volver
        </x-ui.button>
        @can('update', $negocio)
        <x-ui.button href="{{ route('negocios.edit', $negocio) }}" variant="secondary" size="sm">
            <x-ui.icon name="edit" class="w-4 h-4"/> Editar
        </x-ui.button>
        @endcan
    </x-slot>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        {{-- Info principal --}}
        <div class="lg:col-span-2 space-y-6">
            <x-ui.card>
                <div class="p-6">
                    <div class="flex items-start justify-between mb-4">
                        <div>
                            <h2 class="text-xl font-semibold text-slate-900">{{ $negocio->nombre_negocio }}</h2>
                            @if($negocio->tipoNegocio)
                                <p class="text-sm text-slate-500 mt-1">{{ $negocio->tipoNegocio->nombre }}</p>
                            @endif
                        </div>
                        <div class="flex flex-col items-end gap-2">
                            @if($negocio->pipelineEstado)
                                <x-ui.badge :color="$negocio->pipelineEstado->color" class="text-sm px-3 py-1">
                                    {{ $negocio->pipelineEstado->nombre }}
                                </x-ui.badge>
                            @endif
                            @if($negocio->estaGanado())
                                <span class="text-xs px-2 py-1 bg-emerald-50 text-emerald-700 border border-emerald-200 rounded-full font-medium">Ganado</span>
                            @elseif($negocio->estaPerdido())
                                <span class="text-xs px-2 py-1 bg-red-50 text-red-700 border border-red-200 rounded-full font-medium">Perdido</span>
                            @endif
                        </div>
                    </div>

                    <dl class="grid grid-cols-2 gap-4 text-sm">
                        <div>
                            <dt class="text-slate-500 font-medium">Asesor</dt>
                            <dd class="text-slate-900 mt-1">{{ $negocio->asesor?->name ?? '—' }}</dd>
                        </div>
                        @if($negocio->prospecto)
                        <div>
                            <dt class="text-slate-500 font-medium">Prospecto</dt>
                            <dd class="mt-1">
                                <a href="{{ route('prospectos.show', $negocio->prospecto) }}" class="text-blue-600 hover:underline">
                                    {{ $negocio->prospecto->empresa }}
                                </a>
                            </dd>
                        </div>
                        @endif
                        @if($negocio->fecha_estimada_cierre)
                        <div>
                            <dt class="text-slate-500 font-medium">Fecha estimada cierre</dt>
                            <dd class="text-slate-900 mt-1">{{ $negocio->fecha_estimada_cierre->format('d/m/Y') }}</dd>
                        </div>
                        @endif
                        @if($negocio->fecha_cierre_real)
                        <div>
                            <dt class="text-slate-500 font-medium">Fecha cierre real</dt>
                            <dd class="text-slate-900 mt-1">{{ $negocio->fecha_cierre_real->format('d/m/Y') }}</dd>
                        </div>
                        @endif
                        @if($negocio->motivoPerdida)
                        <div>
                            <dt class="text-slate-500 font-medium">Motivo pérdida</dt>
                            <dd class="mt-1"><x-ui.badge color="red">{{ $negocio->motivoPerdida->nombre }}</x-ui.badge></dd>
                        </div>
                        @endif
                    </dl>

                    @if($negocio->observacion_perdida)
                    <div class="mt-4 pt-4 border-t border-red-100 bg-red-50 rounded-lg px-4 py-3">
                        <p class="text-xs font-semibold text-red-500 uppercase tracking-wide mb-1">Observación de pérdida</p>
                        <p class="text-sm text-slate-700">{{ $negocio->observacion_perdida }}</p>
                    </div>
                    @endif

                    @if($negocio->descripcion)
                    <div class="mt-4 pt-4 border-t border-slate-100">
                        <p class="text-sm font-medium text-slate-500 mb-1">Descripción</p>
                        <p class="text-sm text-slate-700">{{ $negocio->descripcion }}</p>
                    </div>
                    @endif
                </div>
            </x-ui.card>

            {{-- Auditoría --}}
            @if($negocio->auditoria->isNotEmpty())
            <x-ui.card>
                <div class="p-6">
                    <h3 class="text-sm font-semibold text-slate-900 mb-4">Historial de cambios</h3>
                    <div class="space-y-3">
                        @foreach($negocio->auditoria->sortByDesc('created_at') as $audit)
                        <div class="flex gap-3 text-sm">
                            <div class="w-2 h-2 rounded-full mt-1.5 shrink-0
                                @if($audit->evento === 'negocio_ganado') bg-emerald-400
                                @elseif($audit->evento === 'negocio_perdido') bg-red-400
                                @else bg-blue-400
                                @endif
                            "></div>
                            <div>
                                <p class="text-slate-700">
                                    <span class="font-medium">{{ $audit->usuario?->name ?? 'Sistema' }}</span>
                                    — {{ ucfirst(str_replace('_', ' ', $audit->evento)) }}
                                    @if($audit->estado_anterior && $audit->estado_nuevo)
                                        : <span class="text-slate-500">{{ $audit->estado_anterior }}</span>
                                        → <span class="text-slate-900 font-medium">{{ $audit->estado_nuevo }}</span>
                                    @endif
                                </p>
                                <p class="text-xs text-slate-400 mt-0.5">{{ $audit->created_at->format('d/m/Y H:i') }}</p>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
            </x-ui.card>
            @endif
        </div>

        {{-- Sidebar métricas --}}
        <div class="space-y-4">
            <x-ui.card class="p-5">
                <p class="text-xs font-semibold text-slate-500 uppercase tracking-wide mb-3">Valor estimado</p>
                <p class="text-2xl font-bold text-slate-900">
                    ${{ number_format($negocio->valor_estimado, 0, ',', '.') }}
                </p>
            </x-ui.card>

            <x-ui.card class="p-5">
                <p class="text-xs font-semibold text-slate-500 uppercase tracking-wide mb-2">Forecast</p>
                <p class="text-2xl font-bold text-blue-600 mb-1">
                    ${{ number_format($negocio->valorForecast(), 0, ',', '.') }}
                </p>
                <div class="flex items-center gap-2">
                    <x-ui.progress :value="$negocio->probabilidadEfectiva()" size="sm"/>
                    <span class="text-sm text-slate-500">{{ $negocio->probabilidadEfectiva() }}%</span>
                </div>
            </x-ui.card>
        </div>
    </div>
</x-layouts.app>
