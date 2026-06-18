<x-layouts.app title="Pipeline" :hide-page-title="true">

<style>
.kanban-board::-webkit-scrollbar        { height: 10px; }
.kanban-board::-webkit-scrollbar-track  { background: #e2e8f0; border-radius: 9999px; }
.kanban-board::-webkit-scrollbar-thumb  { background: #64748b; border-radius: 9999px; }
.kanban-board::-webkit-scrollbar-thumb:hover { background: #334155; }
.kanban-board { scrollbar-width: thin; scrollbar-color: #64748b #e2e8f0; }
#kanban-prev, #kanban-next {
    transition: opacity .15s, box-shadow .15s;
}
</style>

    {{-- Encabezado --}}
    <div class="flex items-center gap-4 mb-5">
        <div>
            <p class="text-xs font-semibold uppercase tracking-widest text-slate-400 mb-0.5">Comercial</p>
            <h1 class="text-2xl font-bold text-slate-900">Pipeline Kanban</h1>
        </div>
        <div class="flex items-center gap-3 mt-4">
        @unlessrole('gerente')
        <a href="{{ route('negocios.create') }}"
           class="inline-flex items-center gap-1.5 px-4 py-2.5 text-sm font-semibold rounded-xl text-white shadow-sm transition-opacity hover:opacity-90"
           style="background:#0f766e">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
            </svg>
            Nuevo negocio
        </a>
        @endunlessrole
        <a href="{{ route('negocios.index') }}"
           class="inline-flex items-center gap-1.5 px-4 py-2.5 text-sm font-medium rounded-xl border border-slate-200 bg-white text-slate-600 hover:bg-slate-50 transition-colors shadow-sm">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.75">
                <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 10h16M4 14h16M4 18h16"/>
            </svg>
            Ver lista
        </a>
        </div>
    </div>

    <div class="relative">

    {{-- Flecha izquierda --}}
    <button id="kanban-prev"
            style="display:none; position:absolute; left:0; top:120px; z-index:30; width:36px; height:36px; border-radius:50%; background:#fff; box-shadow:0 2px 8px rgba(0,0,0,.15); border:1px solid #e2e8f0; align-items:center; justify-content:center; color:#475569; cursor:pointer;"
            onclick="document.getElementById('kanban-board').scrollBy({left:-320,behavior:'smooth'})">
        <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5">
            <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/>
        </svg>
    </button>

    {{-- Flecha derecha --}}
    <button id="kanban-next"
            style="display:none; position:absolute; right:0; top:120px; z-index:30; width:36px; height:36px; border-radius:50%; background:#fff; box-shadow:0 2px 8px rgba(0,0,0,.15); border:1px solid #e2e8f0; align-items:center; justify-content:center; color:#475569; cursor:pointer;"
            onclick="document.getElementById('kanban-board').scrollBy({left:320,behavior:'smooth'})">
        <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5">
            <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/>
        </svg>
    </button>

    <div
        id="kanban-board"
        class="kanban-board flex gap-4 overflow-x-auto pb-6"
        style="min-height: calc(100vh - 9rem)"
        x-data="kanban()"
        x-init="init()"
    >
        @foreach($columnas as $columna)
        @php $color = $columna['estado']->color; @endphp
        <div class="flex-none w-72 flex flex-col">

            {{-- Header columna --}}
            <div class="rounded-xl overflow-hidden mb-3 bg-white border border-slate-200 shadow-sm">
                <div class="h-1.5 w-full" style="background-color: {{ $color }}"></div>
                <div class="p-3">
                    <div class="flex items-center justify-between mb-2">
                        <h3 class="text-sm font-bold text-slate-800">{{ $columna['estado']->nombre }}</h3>
                        <div class="flex items-center gap-1.5">
                            <span class="text-xs font-semibold px-2 py-0.5 rounded-full text-white"
                                  style="background-color: {{ $color }}">
                                {{ count($columna['negocios']) }}
                            </span>
                            @unlessrole('gerente')
                            <a href="{{ route('negocios.create') }}?pipeline_estado_id={{ $columna['estado']->id }}"
                               class="w-6 h-6 rounded-full flex items-center justify-center text-slate-400 hover:text-white hover:bg-blue-500 transition-colors"
                               title="Agregar negocio en esta etapa">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/>
                                </svg>
                            </a>
                            @endunlessrole
                        </div>
                    </div>
                    <p class="text-base font-bold text-slate-900">
                        ${{ number_format($columna['valor_total'], 0, ',', '.') }}
                    </p>
                    <p class="text-xs text-slate-400 mt-0.5">
                        Forecast ${{ number_format($columna['valor_forecast'], 0, ',', '.') }}
                    </p>
                </div>
            </div>

            {{-- Cards --}}
            <div
                class="flex-1 space-y-2 rounded-xl bg-slate-100/70 p-2 min-h-24"
                x-sort:group="negocios"
                x-sort="mover($item, $position, '{{ $columna['estado']->id }}')"
                data-estado-id="{{ $columna['estado']->id }}"
            >
                @forelse($columna['negocios'] as $negocio)
                @php
                    $vencido = $negocio->fecha_estimada_cierre && $negocio->fecha_estimada_cierre->isPast();
                    $diasRestantes = $negocio->fecha_estimada_cierre
                        ? (int) now()->diffInDays($negocio->fecha_estimada_cierre, false)
                        : null;
                @endphp
                <div
                    x-sort:item="{{ $negocio->id }}"
                    class="bg-white rounded-xl p-3 shadow-sm border border-slate-200 cursor-grab active:cursor-grabbing hover:shadow-md transition-all"
                    style="border-left: 3px solid {{ $color }}"
                >
                    {{-- Nombre --}}
                    <div class="mb-1.5">
                        <a href="{{ route('negocios.show', $negocio) }}"
                           class="text-sm font-semibold text-slate-900 leading-tight hover:text-blue-600 transition-colors line-clamp-2">
                            {{ $negocio->nombre_negocio }}
                        </a>
                    </div>

                    {{-- Cliente --}}
                    <p class="text-xs text-slate-500 mb-2.5 truncate flex items-center gap-1">
                        <svg class="w-3 h-3 shrink-0 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-2 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                        </svg>
                        {{ $negocio->cliente?->razon_social ?? $negocio->prospecto?->empresa ?? '—' }}
                    </p>

                    {{-- Valor + fecha + asesor --}}
                    <div class="flex items-center justify-between">
                        <span class="text-sm font-bold text-slate-900">
                            ${{ number_format($negocio->valor_estimado, 0, ',', '.') }}
                        </span>
                        <div class="flex items-center gap-1.5">
                            @if($negocio->fecha_estimada_cierre)
                                <span class="text-xs px-1.5 py-0.5 rounded font-medium
                                    {{ $vencido
                                        ? 'bg-red-50 text-red-600'
                                        : ($diasRestantes !== null && $diasRestantes <= 7
                                            ? 'bg-amber-50 text-amber-600'
                                            : 'text-slate-400') }}">
                                    @if($vencido) ⚠ @endif{{ $negocio->fecha_estimada_cierre->format('d/m') }}
                                </span>
                            @endif
                            <div class="w-6 h-6 rounded-full flex items-center justify-center text-xs font-bold text-white shrink-0"
                                 style="background-color: {{ $color }}"
                                 title="{{ $negocio->asesor?->name }}">
                                {{ strtoupper(substr($negocio->asesor?->name ?? 'U', 0, 1)) }}
                            </div>
                        </div>
                    </div>

                    {{-- Barra de probabilidad --}}
                    <div class="mt-2.5">
                        <div class="flex items-center justify-between mb-1">
                            <span class="text-xs text-slate-400">Probabilidad</span>
                            <span class="text-xs font-semibold text-slate-600">{{ $negocio->probabilidadEfectiva() }}%</span>
                        </div>
                        <div class="h-1.5 bg-slate-100 rounded-full overflow-hidden">
                            <div class="h-full rounded-full"
                                 style="width: {{ $negocio->probabilidadEfectiva() }}%; background-color: {{ $color }}">
                            </div>
                        </div>
                    </div>
                </div>
                @empty
                @unlessrole('gerente')
                <a href="{{ route('negocios.create') }}?pipeline_estado_id={{ $columna['estado']->id }}"
                   class="flex flex-col items-center justify-center h-20 text-xs text-slate-400 gap-1.5 rounded-lg border-2 border-dashed border-slate-200 hover:border-blue-300 hover:text-blue-400 transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 4v16m8-8H4"/>
                    </svg>
                    Agregar negocio
                </a>
                @endunlessrole
                @endforelse
            </div>
        </div>
        @endforeach
    </div>
    </div>{{-- /kanban-wrapper --}}

    {{-- Modal motivo de pérdida --}}
    <div x-data x-show="$store.kanban.modalPerdido" x-cloak
         class="fixed inset-0 z-50 flex items-center justify-center p-4"
         x-transition:enter="transition ease-out duration-150"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100">
        <div class="absolute inset-0 bg-black/40 backdrop-blur-sm" @click="$store.kanban.cancelar()"></div>
        <div class="relative bg-white rounded-2xl shadow-xl w-full max-w-md p-6"
             x-transition:enter="transition ease-out duration-150"
             x-transition:enter-start="opacity-0 scale-95"
             x-transition:enter-end="opacity-100 scale-100">
            <div class="flex items-center gap-3 mb-4">
                <div class="w-9 h-9 rounded-full bg-red-100 flex items-center justify-center shrink-0">
                    <svg class="w-5 h-5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </div>
                <div>
                    <h3 class="text-base font-bold text-slate-900">Negocio perdido</h3>
                    <p class="text-xs text-slate-500">Indica el motivo para registrar el cierre.</p>
                </div>
            </div>

            <label class="block text-sm font-medium text-slate-700 mb-1.5">Motivo de pérdida <span class="text-red-500">*</span></label>
            <select x-model="$store.kanban.motivo"
                    class="w-full px-3 py-2 text-sm rounded-lg border border-slate-300 focus:outline-none focus:ring-2 focus:ring-red-400 bg-white">
                <option value="">Selecciona un motivo...</option>
                @foreach($motivos as $m)
                    <option value="{{ $m->id }}">{{ $m->nombre }}</option>
                @endforeach
            </select>

            <div class="mt-4">
                <label class="block text-sm font-medium text-slate-700 mb-1.5">Observación <span class="text-xs text-slate-400 font-normal">(opcional)</span></label>
                <textarea x-model="$store.kanban.observacion"
                          rows="3"
                          placeholder="Describe brevemente por qué se perdió este negocio..."
                          class="w-full px-3 py-2 text-sm rounded-lg border border-slate-300 focus:outline-none focus:ring-2 focus:ring-red-400 resize-none"></textarea>
            </div>

            <div class="flex justify-end gap-2 mt-4">
                <button @click="$store.kanban.cancelar()"
                        class="px-4 py-2 text-sm text-slate-600 border border-slate-200 rounded-lg hover:bg-slate-50 transition-colors">
                    Cancelar
                </button>
                <button @click="$store.kanban.confirmar()"
                        :disabled="!$store.kanban.motivo.trim()"
                        class="px-4 py-2 text-sm font-semibold text-white bg-red-600 rounded-lg hover:bg-red-700 disabled:opacity-40 disabled:cursor-not-allowed transition-colors">
                    Confirmar pérdida
                </button>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
    window.addEventListener('load', function () {
        const board = document.getElementById('kanban-board');
        const prev  = document.getElementById('kanban-prev');
        const next  = document.getElementById('kanban-next');
        if (!board || !prev || !next) return;

        function updateArrows() {
            const atStart = board.scrollLeft <= 4;
            const atEnd   = board.scrollLeft + board.clientWidth >= board.scrollWidth - 4;
            prev.style.display = atStart ? 'none' : 'flex';
            next.style.display = atEnd   ? 'none' : 'flex';
        }

        board.addEventListener('scroll', updateArrows, { passive: true });
        updateArrows();
    });

    document.addEventListener('alpine:init', () => {
        Alpine.store('kanban', {
            modalPerdido: false,
            motivo: '',
            observacion: '',
            pending: null,
            api: null,
            toast: null,

            abrir(itemId, estadoId, apiFn, toastStore) {
                this.pending     = { itemId, estadoId };
                this.motivo      = '';
                this.observacion = '';
                this.api         = apiFn;
                this.toast       = toastStore;
                this.modalPerdido = true;
            },

            cancelar() {
                this.modalPerdido = false;
                this.pending = null;
                window.location.reload();
            },

            async confirmar() {
                if (!this.motivo.trim() || !this.pending) return;
                const { itemId, estadoId } = this.pending;
                this.modalPerdido = false;
                await kanbanMover(this.api, this.toast, itemId, estadoId, this.motivo.trim(), this.observacion.trim() || null);
                this.pending = null;
            },
        });
    });

    async function kanbanMover(apiFn, toastStore, itemId, estadoId, motivo = null, observacion = null) {
        try {
            const payload = { pipeline_estado_id: parseInt(estadoId) };
            if (motivo) payload.motivo_perdida_id = parseInt(motivo);
            if (observacion) payload.observacion_perdida = observacion;
            const res = await apiFn('PUT', `/api/negocios/${itemId}`, payload);
            if (!res.success) {
                toastStore.error('Error al mover el negocio: ' + res.message);
            } else {
                toastStore.success('Negocio actualizado.');
            }
        } catch {
            toastStore.error('Error de conexión.');
        }
    }

    function kanban() {
        return {
            estadosPerdidos: [{{ collect($columnas)->filter(fn($c) => $c['estado']->es_perdido)->pluck('estado.id')->join(',') }}],

            init() {},

            async mover(itemId, position, estadoId) {
                if (this.estadosPerdidos.includes(parseInt(estadoId))) {
                    Alpine.store('kanban').abrir(itemId, estadoId, this.$api.bind(this), this.$store.toast);
                    return;
                }
                await kanbanMover(this.$api.bind(this), this.$store.toast, itemId, estadoId);
            },
        };
    }
    </script>
    @endpush
</x-layouts.app>
