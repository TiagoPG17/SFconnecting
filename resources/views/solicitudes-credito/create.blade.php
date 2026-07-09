<x-layouts.app title="Nueva solicitud de crédito">
    <x-slot name="actions">
        <x-ui.button href="{{ route('solicitudes-credito.index') }}" variant="ghost" size="sm">
            <x-ui.icon name="arrow-left" class="w-4 h-4"/> Volver
        </x-ui.button>
    </x-slot>

    <div class="max-w-2xl mx-auto">
        <x-ui.card>
            <div class="p-6">
                <form
                    x-data="{
                        loading: false,
                        selectedId: {{ $negocio?->id ?? 'null' }},
                        query: {{ $negocio ? \Illuminate\Support\Js::from($negocio->nombre_negocio) : \Illuminate\Support\Js::from('') }},
                        clienteNombre: {{ $negocio?->cliente ? \Illuminate\Support\Js::from($negocio->cliente->razon_social) : 'null' }},
                        results: [], open: false, buscando: false, timer: null,
                        buscar() {
                            clearTimeout(this.timer);
                            if (this.query.length < 2) { this.results = []; this.open = false; return; }
                            this.timer = setTimeout(async () => {
                                this.buscando = true;
                                const r = await $api('GET', '/api/negocios?buscar=' + encodeURIComponent(this.query) + '&per_page=10');
                                this.results = r.data?.data ?? [];
                                this.open = this.results.length > 0;
                                this.buscando = false;
                            }, 300);
                        },
                        seleccionar(item) {
                            this.selectedId = item.id;
                            this.query = item.nombre_negocio;
                            this.clienteNombre = item.cliente?.razon_social ?? null;
                            this.open = false;
                        },
                        limpiar() { this.selectedId = null; this.query = ''; this.clienteNombre = null; this.results = []; this.open = false; }
                    }"
                    @submit.prevent="
                        loading = true;
                        const fd = new FormData($el);
                        const body = {};
                        fd.forEach((v, k) => { if (v !== '') body[k] = v; });
                        body.negocio_id = selectedId;
                        $api('POST', '/api/solicitudes-credito', body)
                            .then(r => {
                                if (r.success) {
                                    $store.toast.success(r.message);
                                    setTimeout(() => window.location.href = '{{ route('solicitudes-credito.index') }}', 800);
                                } else {
                                    $store.toast.error(r.message ?? 'Error al radicar la solicitud');
                                    loading = false;
                                }
                            }).catch(() => { $store.toast.error('Error de conexión'); loading = false; })
                    "
                    class="space-y-5"
                >
                    <h2 class="text-base font-semibold text-slate-900 mb-1">Datos de la solicitud</h2>

                    {{-- Autocomplete negocio --}}
                    <div class="relative" @click.outside="open = false">
                        <label class="block text-sm font-medium text-slate-700 mb-1.5">Negocio</label>
                        <div class="relative">
                            <input
                                type="text"
                                x-model="query"
                                @input="buscar()"
                                @focus="if (results.length) open = true"
                                placeholder="Escribe para buscar un negocio..."
                                autocomplete="off"
                                required
                                class="w-full px-3 py-2 pr-8 text-sm rounded-lg border border-slate-300 focus:outline-none focus:ring-2 focus:ring-blue-500"
                            >
                            <div class="absolute right-2 top-1/2 -translate-y-1/2">
                                <svg x-show="buscando" class="w-4 h-4 animate-spin text-slate-400" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"/>
                                </svg>
                                <button x-show="!buscando && selectedId" type="button" @click="limpiar()" class="text-slate-400 hover:text-slate-600">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                </button>
                            </div>
                        </div>
                        <div x-show="open" x-transition class="absolute z-50 w-full mt-1 bg-white rounded-lg border border-slate-200 shadow-lg max-h-52 overflow-y-auto">
                            <template x-for="item in results" :key="item.id">
                                <button type="button" @click="seleccionar(item)" class="w-full text-left px-3 py-2.5 text-sm hover:bg-slate-50 border-b border-slate-100 last:border-0 transition-colors">
                                    <span class="font-medium text-slate-800" x-text="item.nombre_negocio"></span>
                                    <span class="text-slate-400 text-xs ml-1.5" x-text="item.cliente?.razon_social ?? 'Sin cliente vinculado'"></span>
                                </button>
                            </template>
                        </div>
                        <p x-show="selectedId && clienteNombre" x-cloak class="text-xs text-slate-500 mt-1.5">
                            Cliente: <span class="font-medium" x-text="clienteNombre"></span>
                        </p>
                        <p class="text-xs text-amber-600 mt-1.5">* El negocio debe estar vinculado a un cliente con NIT en SIESA.</p>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                        <x-ui.input name="monto_solicitado" type="number" label="Monto solicitado ($)" required placeholder="0" min="0" step="0.01"/>
                        <x-ui.input name="plazo_solicitado_dias" type="number" label="Plazo solicitado (días)" placeholder="Ej: 30" min="1"/>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1.5">Justificación</label>
                        <textarea
                            name="justificacion"
                            rows="3"
                            placeholder="Explica el motivo de la solicitud..."
                            class="w-full px-3 py-2 text-sm rounded-lg border border-slate-300 focus:outline-none focus:ring-2 focus:ring-blue-500 resize-none"
                        ></textarea>
                    </div>

                    <div class="flex justify-end gap-3 pt-2">
                        <x-ui.button href="{{ route('solicitudes-credito.index') }}" variant="ghost">Cancelar</x-ui.button>
                        <x-ui.button type="submit" variant="primary" x-bind:disabled="loading || !selectedId">
                            <span x-show="!loading">Radicar solicitud</span>
                            <span x-show="loading">Guardando...</span>
                        </x-ui.button>
                    </div>
                </form>
            </div>
        </x-ui.card>
    </div>
</x-layouts.app>
