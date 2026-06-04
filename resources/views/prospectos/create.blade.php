<x-layouts.app title="Nuevo prospecto">
    <x-slot name="actions">
        <x-ui.button href="{{ route('prospectos.index') }}" variant="ghost" size="sm">
            <x-ui.icon name="arrow-left" class="w-4 h-4"/> Volver
        </x-ui.button>
    </x-slot>

    <div class="max-w-2xl mx-auto">
        <x-ui.card>
            <div class="p-6">
                <h2 class="text-base font-semibold text-slate-900 mb-6">Datos del prospecto</h2>

                <form
                    x-data="{ loading: false }"
                    @submit.prevent="
                        loading = true;
                        const fd = new FormData($el);
                        const body = Object.fromEntries(fd.entries());
                        $api('POST', '/api/prospectos', body)
                            .then(r => {
                                if (r.success) {
                                    $store.toast.success(r.message);
                                    setTimeout(() => window.location.href = '{{ route('prospectos.index') }}', 800);
                                } else {
                                    $store.toast.error(r.message ?? 'Error al crear');
                                    loading = false;
                                }
                            }).catch(() => { $store.toast.error('Error de conexión'); loading = false; })
                    "
                    class="space-y-5"
                >
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                        <x-ui.input name="empresa" label="Empresa" required placeholder="Nombre de la empresa"/>
                        <x-ui.input name="contacto" label="Contacto principal" required placeholder="Nombre del contacto"/>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                        <x-ui.input name="email" type="email" label="Email" placeholder="correo@empresa.com"/>
                        <x-ui.input name="telefono" label="Teléfono" placeholder="+57 300 000 0000"/>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                        <x-ui.select name="estado_pipeline_id" label="Estado pipeline" required>
                            <option value="">Seleccionar estado...</option>
                            @foreach($estados as $e)
                                <option value="{{ $e->id }}">{{ $e->nombre }}</option>
                            @endforeach
                        </x-ui.select>

                        <x-ui.select name="origen_id" label="Origen">
                            <option value="">Sin origen</option>
                            @foreach($origenes as $o)
                                <option value="{{ $o->id }}">{{ $o->nombre }}</option>
                            @endforeach
                        </x-ui.select>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                        <x-ui.select name="prioridad_id" label="Prioridad">
                            <option value="">Sin prioridad</option>
                            @foreach($prioridades as $p)
                                <option value="{{ $p->id }}">{{ $p->nombre }}</option>
                            @endforeach
                        </x-ui.select>

                        <x-ui.input name="valor_estimado" type="number" label="Valor estimado ($)" placeholder="0" min="0" step="1000"/>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                        <x-ui.input name="probabilidad_cierre" type="number" label="Probabilidad (%)" placeholder="Automática del estado" min="0" max="100"/>
                        <x-ui.input name="fecha_proximo_contacto" type="date" label="Próximo contacto"/>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1.5">Observaciones</label>
                        <textarea
                            name="observaciones"
                            rows="3"
                            placeholder="Notas adicionales..."
                            class="w-full px-3 py-2 text-sm rounded-lg border border-slate-300 focus:outline-none focus:ring-2 focus:ring-blue-500 resize-none"
                        ></textarea>
                    </div>

                    <div class="flex justify-end gap-3 pt-2">
                        <x-ui.button href="{{ route('prospectos.index') }}" variant="ghost">Cancelar</x-ui.button>
                        <x-ui.button type="submit" variant="primary" x-bind:disabled="loading">
                            <span x-show="!loading">Crear prospecto</span>
                            <span x-show="loading">Guardando...</span>
                        </x-ui.button>
                    </div>
                </form>
            </div>
        </x-ui.card>
    </div>
</x-layouts.app>
