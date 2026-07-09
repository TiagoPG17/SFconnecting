<x-layouts.app title="Nuevo negocio">
    <x-slot name="actions">
        <x-ui.button href="{{ route('negocios.index') }}" variant="ghost" size="sm">
            <x-ui.icon name="arrow-left" class="w-4 h-4"/> Volver
        </x-ui.button>
    </x-slot>

    <div class="max-w-2xl mx-auto">
        <x-ui.card>
            <div class="p-6">
                <form
                    x-data="{
                        loading: false,
                        tipoVinculo: 'prospecto',
                        estadoId: null,
                        estadosPerdidos: @js($estadosPerdidoIds),
                        get esPerdido() { return this.estadosPerdidos.includes(parseInt(this.estadoId)); }
                    }"
                    @submit.prevent="
                        loading = true;
                        const fd = new FormData($el);
                        const body = {};
                        fd.forEach((v, k) => { if (v !== '') body[k] = v; });
                        if (tipoVinculo === 'prospecto') delete body.cliente_id;
                        if (tipoVinculo === 'cliente') delete body.prospecto_id;
                        $api('POST', '/api/negocios', body)
                            .then(r => {
                                if (r.success) {
                                    $store.toast.success(r.message);
                                    setTimeout(() => window.location.href = '{{ route('negocios.index') }}', 800);
                                } else {
                                    $store.toast.error(r.message ?? 'Error al crear');
                                    loading = false;
                                }
                            }).catch(() => { $store.toast.error('Error de conexión'); loading = false; })
                    "
                    class="space-y-5"
                >
                    {{-- Encabezado con título y toggle --}}
                    <div class="flex items-center justify-between mb-1">
                        <h2 class="text-base font-semibold text-slate-900">Datos del negocio</h2>
                        <div class="inline-flex rounded-lg border border-slate-300 overflow-hidden">
                            <button
                                type="button"
                                @click="tipoVinculo = 'prospecto'; $dispatch('ac-reset-cliente')"
                                :class="tipoVinculo === 'prospecto' ? 'bg-blue-600 text-white' : 'bg-white text-slate-600 hover:bg-slate-50'"
                                class="px-4 py-1.5 text-sm font-medium transition-colors duration-150 focus:outline-none"
                            >Prospecto</button>
                            <button
                                type="button"
                                @click="tipoVinculo = 'cliente'; $dispatch('ac-reset-prospecto')"
                                :class="tipoVinculo === 'cliente' ? 'bg-blue-600 text-white' : 'bg-white text-slate-600 hover:bg-slate-50'"
                                class="px-4 py-1.5 text-sm font-medium border-l border-slate-300 transition-colors duration-150 focus:outline-none"
                            >Cliente</button>
                        </div>
                    </div>

                    {{-- Autocomplete prospecto --}}
                    <div
                        x-show="tipoVinculo === 'prospecto'"
                        x-transition
                        x-data="{
                            query: '', selectedId: null, results: [], open: false, loading: false, timer: null,
                            buscar() {
                                clearTimeout(this.timer);
                                if (this.query.length < 2) { this.results = []; this.open = false; return; }
                                this.timer = setTimeout(async () => {
                                    this.loading = true;
                                    const r = await $api('GET', '/api/prospectos?buscar=' + encodeURIComponent(this.query) + '&per_page=10');
                                    this.results = r.data?.data ?? [];
                                    this.open = this.results.length > 0;
                                    this.loading = false;
                                }, 300);
                            },
                            seleccionar(item) {
                                this.selectedId = item.id;
                                this.query = item.empresa + ' (' + item.codigo + ')';
                                this.open = false;
                            },
                            limpiar() { this.selectedId = null; this.query = ''; this.results = []; this.open = false; }
                        }"
                        @ac-reset-prospecto.window="limpiar()"
                        @click.outside="open = false"
                        class="relative"
                    >
                        <label class="block text-sm font-medium text-slate-700 mb-1.5">Prospecto vinculado</label>
                        <input type="hidden" name="prospecto_id" :value="selectedId ?? ''">
                        <div class="relative">
                            <input
                                type="text"
                                x-model="query"
                                @input="buscar()"
                                @focus="if (results.length) open = true"
                                placeholder="Escribe para buscar un prospecto..."
                                autocomplete="off"
                                class="w-full px-3 py-2 pr-8 text-sm rounded-lg border border-slate-300 focus:outline-none focus:ring-2 focus:ring-blue-500"
                            >
                            <div class="absolute right-2 top-1/2 -translate-y-1/2">
                                <svg x-show="loading" class="w-4 h-4 animate-spin text-slate-400" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"/>
                                </svg>
                                <button x-show="!loading && selectedId" type="button" @click="limpiar()" class="text-slate-400 hover:text-slate-600">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                </button>
                            </div>
                        </div>
                        <div x-show="open" x-transition class="absolute z-50 w-full mt-1 bg-white rounded-lg border border-slate-200 shadow-lg max-h-52 overflow-y-auto">
                            <template x-for="item in results" :key="item.id">
                                <button type="button" @click="seleccionar(item)" class="w-full text-left px-3 py-2.5 text-sm hover:bg-slate-50 border-b border-slate-100 last:border-0 transition-colors">
                                    <span class="font-medium text-slate-800" x-text="item.empresa"></span>
                                    <span class="text-slate-400 text-xs ml-1.5" x-text="'(' + item.codigo + ')'"></span>
                                </button>
                            </template>
                        </div>
                    </div>

                    {{-- Autocomplete cliente --}}
                    <div
                        x-show="tipoVinculo === 'cliente'"
                        x-transition
                        x-data="{
                            query: '', selectedId: null, results: [], open: false, loading: false, timer: null,
                            buscar() {
                                clearTimeout(this.timer);
                                if (this.query.length < 2) { this.results = []; this.open = false; return; }
                                this.timer = setTimeout(async () => {
                                    this.loading = true;
                                    const r = await $api('GET', '/api/clientes?buscar=' + encodeURIComponent(this.query) + '&per_page=10');
                                    this.results = r.data?.data ?? [];
                                    this.open = this.results.length > 0;
                                    this.loading = false;
                                }, 300);
                            },
                            seleccionar(item) {
                                this.selectedId = item.id;
                                this.query = item.razon_social + ' — ' + item.nit;
                                this.open = false;
                            },
                            limpiar() { this.selectedId = null; this.query = ''; this.results = []; this.open = false; }
                        }"
                        @ac-reset-cliente.window="limpiar()"
                        @click.outside="open = false"
                        class="relative"
                    >
                        <label class="block text-sm font-medium text-slate-700 mb-1.5">Cliente vinculado</label>
                        <input type="hidden" name="cliente_id" :value="selectedId ?? ''">
                        <div class="relative">
                            <input
                                type="text"
                                x-model="query"
                                @input="buscar()"
                                @focus="if (results.length) open = true"
                                placeholder="Escribe nombre o NIT del cliente..."
                                autocomplete="off"
                                class="w-full px-3 py-2 pr-8 text-sm rounded-lg border border-slate-300 focus:outline-none focus:ring-2 focus:ring-blue-500"
                            >
                            <div class="absolute right-2 top-1/2 -translate-y-1/2">
                                <svg x-show="loading" class="w-4 h-4 animate-spin text-slate-400" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"/>
                                </svg>
                                <button x-show="!loading && selectedId" type="button" @click="limpiar()" class="text-slate-400 hover:text-slate-600">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                </button>
                            </div>
                        </div>
                        <div x-show="open" x-transition class="absolute z-50 w-full mt-1 bg-white rounded-lg border border-slate-200 shadow-lg max-h-52 overflow-y-auto">
                            <template x-for="item in results" :key="item.id">
                                <button type="button" @click="seleccionar(item)" class="w-full text-left px-3 py-2.5 text-sm hover:bg-slate-50 border-b border-slate-100 last:border-0 transition-colors">
                                    <span class="font-medium text-slate-800" x-text="item.razon_social"></span>
                                    <span class="text-slate-400 text-xs ml-1.5" x-text="item.nit"></span>
                                </button>
                            </template>
                        </div>
                    </div>

                    <x-ui.input name="nombre_negocio" label="Nombre del negocio" required placeholder="Ej: Implementación ERP Empresa X"/>

                    @if(count($companiasAsesor) > 1)
                    <x-ui.select name="compania" label="Compañía" required>
                        <option value="">Seleccionar compañía...</option>
                        @foreach($companiasAsesor as $cia)
                            <option value="{{ $cia }}">{{ $cia === 1 ? 'Formacol' : 'Contiflex' }}</option>
                        @endforeach
                    </x-ui.select>
                    @endif

                    <p class="text-xs text-amber-600">* El negocio debe estar vinculado a un prospecto o un cliente.</p>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                        <x-ui.select name="pipeline_estado_id" label="Estado pipeline" required x-model="estadoId">
                            <option value="">Seleccionar estado...</option>
                            @foreach($estados as $e)
                                <option value="{{ $e->id }}">{{ $e->nombre }}</option>
                            @endforeach
                        </x-ui.select>

                        <x-ui.select name="tipo_negocio_id" label="Tipo de negocio">
                            <option value="">Sin tipo</option>
                            @foreach($tipos as $t)
                                <option value="{{ $t->id }}">{{ $t->nombre }}</option>
                            @endforeach
                        </x-ui.select>
                    </div>

                    <div x-show="esPerdido" x-cloak class="space-y-4 p-4 rounded-xl border border-red-200 bg-red-50/50">
                        <p class="text-xs font-semibold text-red-600 uppercase tracking-wide">Información de cierre perdido</p>
                        <x-ui.select name="motivo_perdida_id" label="Motivo de pérdida">
                            <option value="">Sin motivo</option>
                            @foreach($motivos as $m)
                                <option value="{{ $m->id }}">{{ $m->nombre }}</option>
                            @endforeach
                        </x-ui.select>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1.5">¿Por qué se perdió? <span class="text-slate-400 font-normal">(opcional)</span></label>
                            <textarea
                                name="observacion_perdida"
                                rows="3"
                                placeholder="Describe aquí el detalle de por qué se perdió el negocio..."
                                class="w-full px-3 py-2 text-sm rounded-lg border border-slate-300 focus:outline-none focus:ring-2 focus:ring-red-400 resize-none"
                            ></textarea>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                        <x-ui.input name="valor_estimado" type="number" label="Valor estimado ($)" placeholder="0" min="0" step="0.01" value="0"/>
                        <x-ui.input name="probabilidad_cierre" type="number" label="Probabilidad (%)" placeholder="Automática del estado" min="0" max="100"/>
                    </div>

                    <x-ui.input name="fecha_estimada_cierre" type="date" label="Fecha estimada de cierre"/>

                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1.5">Descripción del producto cotizado</label>
                        <textarea
                            name="descripcion"
                            rows="3"
                            placeholder="Descripción del negocio..."
                            class="w-full px-3 py-2 text-sm rounded-lg border border-slate-300 focus:outline-none focus:ring-2 focus:ring-blue-500 resize-none"
                        ></textarea>
                    </div>

                    <div class="flex justify-end gap-3 pt-2">
                        <x-ui.button href="{{ route('negocios.index') }}" variant="ghost">Cancelar</x-ui.button>
                        <x-ui.button type="submit" variant="primary" x-bind:disabled="loading">
                            <span x-show="!loading">Crear negocio</span>
                            <span x-show="loading">Guardando...</span>
                        </x-ui.button>
                    </div>
                </form>
            </div>
        </x-ui.card>
    </div>
</x-layouts.app>
