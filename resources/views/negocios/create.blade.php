<x-layouts.app title="Nuevo negocio">
    <x-slot name="actions">
        <x-ui.button href="{{ route('negocios.index') }}" variant="ghost" size="sm">
            <x-ui.icon name="arrow-left" class="w-4 h-4"/> Volver
        </x-ui.button>
    </x-slot>

    <div class="max-w-2xl mx-auto">
        <x-ui.card>
            <div class="p-6">
                <h2 class="text-base font-semibold text-slate-900 mb-6">Datos del negocio</h2>

                <form
                    x-data="{ loading: false }"
                    @submit.prevent="
                        loading = true;
                        const fd = new FormData($el);
                        const body = {};
                        fd.forEach((v, k) => { if (v !== '') body[k] = v; });
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
                    <x-ui.input name="nombre_negocio" label="Nombre del negocio" required placeholder="Ej: Proyecto ERP Acme"/>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                        <x-ui.select name="prospecto_id" label="Prospecto vinculado">
                            <option value="">Sin prospecto</option>
                            @foreach($prospectos as $p)
                                <option value="{{ $p->id }}">{{ $p->empresa }} ({{ $p->codigo }})</option>
                            @endforeach
                        </x-ui.select>

                        <x-ui.select name="cliente_id" label="Cliente vinculado">
                            <option value="">Sin cliente</option>
                            @foreach($clientes as $c)
                                <option value="{{ $c->id }}">{{ $c->razon_social }}</option>
                            @endforeach
                        </x-ui.select>
                    </div>

                    <p class="text-xs text-amber-600">* El negocio debe estar vinculado a un prospecto o un cliente.</p>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                        <x-ui.select name="pipeline_estado_id" label="Estado pipeline" required>
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
