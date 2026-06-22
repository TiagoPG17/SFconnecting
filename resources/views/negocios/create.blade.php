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
                                @click="
                                    tipoVinculo = 'prospecto';
                                    $el.closest('form').querySelector('[name=cliente_id]').value = '';
                                "
                                :class="tipoVinculo === 'prospecto'
                                    ? 'bg-blue-600 text-white'
                                    : 'bg-white text-slate-600 hover:bg-slate-50'"
                                class="px-4 py-1.5 text-sm font-medium transition-colors duration-150 focus:outline-none"
                            >
                                Prospecto
                            </button>
                            <button
                                type="button"
                                @click="
                                    tipoVinculo = 'cliente';
                                    $el.closest('form').querySelector('[name=prospecto_id]').value = '';
                                "
                                :class="tipoVinculo === 'cliente'
                                    ? 'bg-blue-600 text-white'
                                    : 'bg-white text-slate-600 hover:bg-slate-50'"
                                class="px-4 py-1.5 text-sm font-medium border-l border-slate-300 transition-colors duration-150 focus:outline-none"
                            >
                                Cliente
                            </button>
                        </div>
                    </div>

                    {{-- Select de prospecto --}}
                    <div x-show="tipoVinculo === 'prospecto'" x-transition>
                        <x-ui.select
                            name="prospecto_id"
                            label="Prospecto vinculado"
                            placeholder="Seleccionar prospecto..."
                        >
                            @foreach($prospectos as $p)
                                <option value="{{ $p->id }}">{{ $p->empresa }} ({{ $p->codigo }})</option>
                            @endforeach
                        </x-ui.select>
                    </div>

                    {{-- Select de cliente --}}
                    <div x-show="tipoVinculo === 'cliente'" x-transition>
                        <x-ui.select
                            name="cliente_id"
                            label="Cliente vinculado"
                            placeholder="Seleccionar cliente..."
                        >
                            @foreach($clientes as $c)
                                <option value="{{ $c->id }}">{{ $c->razon_social }}</option>
                            @endforeach
                        </x-ui.select>
                    </div>

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
                        <x-ui.input name="valor_estimado" type="number" label="Valor estimado ($)" placeholder="0" min="0" step="1000" value="0"/>
                        <x-ui.input name="probabilidad_cierre" type="number" label="Probabilidad (%)" placeholder="Automática del estado" min="0" max="100"/>
                    </div>

                    <x-ui.input name="fecha_estimada_cierre" type="date" label="Fecha estimada de cierre"/>

                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1.5">Descripción</label>
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
