<x-layouts.app :title="'Editar: ' . $negocio->nombre_negocio">
    <x-slot name="actions">
        <x-ui.button href="{{ route('negocios.show', $negocio) }}" variant="ghost" size="sm">
            <x-ui.icon name="arrow-left" class="w-4 h-4"/> Volver
        </x-ui.button>
    </x-slot>

    <div class="max-w-2xl mx-auto">
        <x-ui.card>
            <div class="p-6">
                <h2 class="text-base font-semibold text-slate-900 mb-6">Editar negocio</h2>

                <form
                    x-data="{
                        loading: false,
                        estadoId: {{ $negocio->pipeline_estado_id ?? 'null' }},
                        estadosPerdidos: @js($estadosPerdidoIds),
                        get esPerdido() { return this.estadosPerdidos.includes(parseInt(this.estadoId)); }
                    }"
                    @submit.prevent="
                        loading = true;
                        const fd = new FormData($el);
                        const body = {};
                        fd.forEach((v, k) => { if (v !== '') body[k] = v; });
                        $api('PUT', '/api/negocios/{{ $negocio->id }}', body)
                            .then(r => {
                                if (r.success) {
                                    $store.toast.success(r.message);
                                    setTimeout(() => window.location.href = '{{ route('negocios.show', $negocio) }}', 800);
                                } else {
                                    $store.toast.error(r.message ?? 'Error al actualizar');
                                    loading = false;
                                }
                            }).catch(() => { $store.toast.error('Error de conexión'); loading = false; })
                    "
                    class="space-y-5"
                >
                    <x-ui.input name="nombre_negocio" label="Nombre del negocio" :value="$negocio->nombre_negocio" required/>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                        <x-ui.select name="pipeline_estado_id" label="Estado pipeline" required x-model="estadoId">
                            @foreach($estados as $e)
                                <option value="{{ $e->id }}" {{ $negocio->pipeline_estado_id == $e->id ? 'selected' : '' }}>
                                    {{ $e->nombre }}
                                </option>
                            @endforeach
                        </x-ui.select>

                        <x-ui.select name="tipo_negocio_id" label="Tipo de negocio">
                            <option value="">Sin tipo</option>
                            @foreach($tipos as $t)
                                <option value="{{ $t->id }}" {{ $negocio->tipo_negocio_id == $t->id ? 'selected' : '' }}>
                                    {{ $t->nombre }}
                                </option>
                            @endforeach
                        </x-ui.select>

                        <x-ui.select name="sector_id" label="Sector">
                            <option value="">Sin sector</option>
                            @foreach($sectores as $s)
                                <option value="{{ $s->id }}" {{ $negocio->sector_id == $s->id ? 'selected' : '' }}>
                                    {{ $s->nombre }}
                                </option>
                            @endforeach
                        </x-ui.select>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                        <div x-data="pesoInput({{ (int) ($negocio->valor_estimado ?? 0) }})">
                            <label class="block text-sm font-medium text-slate-700 mb-1.5">Valor estimado ($)</label>
                            <div class="flex items-center border border-slate-300 rounded-lg overflow-hidden focus-within:ring-2 focus-within:ring-blue-500">
                                <span class="px-3 text-slate-400 text-sm shrink-0 border-r border-slate-300 bg-slate-50 py-2 select-none pointer-events-none">$</span>
                                <input type="text" inputmode="numeric"
                                       x-model="display" @input="onInput($event)" @keydown="onKeydown($event)"
                                       placeholder="0"
                                       class="flex-1 px-3 py-2 text-sm focus:outline-none bg-transparent"/>
                            </div>
                            <input type="hidden" name="valor_estimado" :value="raw"/>
                        </div>
                        <x-ui.input name="probabilidad_cierre" type="number" label="Probabilidad (%)"
                            :value="$negocio->probabilidad_cierre" placeholder="Automática del estado" min="0" max="100"/>
                    </div>

                    <x-ui.input name="fecha_estimada_cierre" type="date" label="Fecha estimada de cierre"
                        :value="$negocio->fecha_estimada_cierre?->format('Y-m-d')"/>

                    <div x-show="esPerdido" x-cloak class="space-y-4 p-4 rounded-xl border border-red-200 bg-red-50/50">
                        <p class="text-xs font-semibold text-red-600 uppercase tracking-wide">Información de cierre perdido</p>
                        <x-ui.select name="motivo_perdida_id" label="Motivo de pérdida">
                            <option value="">Sin motivo</option>
                            @foreach($motivos as $m)
                                <option value="{{ $m->id }}" {{ $negocio->motivo_perdida_id == $m->id ? 'selected' : '' }}>
                                    {{ $m->nombre }}
                                </option>
                            @endforeach
                        </x-ui.select>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1.5">¿Por qué se perdió? <span class="text-slate-400 font-normal">(opcional)</span></label>
                            <textarea
                                name="observacion_perdida"
                                rows="3"
                                placeholder="Describe aquí el detalle de por qué se perdió el negocio..."
                                class="w-full px-3 py-2 text-sm rounded-lg border border-slate-300 focus:outline-none focus:ring-2 focus:ring-red-400 resize-none"
                            >{{ old('observacion_perdida', $negocio->observacion_perdida) }}</textarea>
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1.5">Descripción</label>
                        <textarea
                            name="descripcion"
                            rows="3"
                            class="w-full px-3 py-2 text-sm rounded-lg border border-slate-300 focus:outline-none focus:ring-2 focus:ring-blue-500 resize-none"
                        >{{ $negocio->descripcion }}</textarea>
                    </div>

                    <div class="flex justify-end gap-3 pt-2">
                        <x-ui.button href="{{ route('negocios.show', $negocio) }}" variant="ghost">Cancelar</x-ui.button>
                        <x-ui.button type="submit" variant="primary" x-bind:disabled="loading">
                            <span x-show="!loading">Guardar cambios</span>
                            <span x-show="loading">Guardando...</span>
                        </x-ui.button>
                    </div>
                </form>
            </div>
        </x-ui.card>
    </div>
</x-layouts.app>
