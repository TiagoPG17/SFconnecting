<x-layouts.app :title="'Editar: ' . $cliente->razon_social">
    <x-slot name="actions">
        <x-ui.button href="{{ route('clientes.show', $cliente) }}" variant="ghost" size="sm">
            <x-ui.icon name="arrow-left" class="w-4 h-4"/> Volver
        </x-ui.button>
    </x-slot>

    <div class="max-w-2xl mx-auto">
        <x-ui.card>
            <div class="p-6">
                <h2 class="text-base font-semibold text-slate-900 mb-6">Editar cliente</h2>

                <form
                    x-data="{ loading: false }"
                    @submit.prevent="
                        loading = true;
                        const fd = new FormData($el);
                        const body = {};
                        fd.forEach((v, k) => { if (v !== '') body[k] = v; });
                        $api('PUT', '/api/clientes/{{ $cliente->id }}', body)
                            .then(r => {
                                if (r.success) {
                                    $store.toast.success(r.message);
                                    setTimeout(() => window.location.href = '{{ route('clientes.show', $cliente) }}', 800);
                                } else {
                                    $store.toast.error(r.message ?? 'Error al actualizar');
                                    loading = false;
                                }
                            }).catch(() => { $store.toast.error('Error de conexión'); loading = false; })
                    "
                    class="space-y-5"
                >
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                        <x-ui.input name="razon_social" label="Razón social" :value="$cliente->razon_social" required/>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1.5">NIT</label>
                            <input type="text" value="{{ $cliente->nit }}" disabled
                                class="w-full px-3 py-2 text-sm rounded-lg border border-slate-200 bg-slate-50 text-slate-500 cursor-not-allowed"/>
                            <p class="mt-1 text-xs text-slate-400">El NIT no puede modificarse</p>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                        <x-ui.input name="email" type="email" label="Email" :value="$cliente->email"/>
                        <x-ui.input name="telefono" label="Teléfono" :value="$cliente->telefono"/>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                        <x-ui.input name="ciudad" label="Ciudad" :value="$cliente->ciudad"/>
                        <x-ui.select name="estado" label="Estado">
                            <option value="activo"    {{ $cliente->estado === 'activo'    ? 'selected' : '' }}>Activo</option>
                            <option value="prospecto" {{ $cliente->estado === 'prospecto' ? 'selected' : '' }}>Prospecto</option>
                            <option value="inactivo"  {{ $cliente->estado === 'inactivo'  ? 'selected' : '' }}>Inactivo</option>
                        </x-ui.select>
                    </div>

                    <x-ui.input name="direccion" label="Dirección" :value="$cliente->direccion"/>

                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1.5">Notas</label>
                        <textarea
                            name="notas"
                            rows="3"
                            class="w-full px-3 py-2 text-sm rounded-lg border border-slate-300 focus:outline-none focus:ring-2 focus:ring-blue-500 resize-none"
                        >{{ $cliente->notas }}</textarea>
                    </div>

                    <div class="flex justify-end gap-3 pt-2">
                        <x-ui.button href="{{ route('clientes.show', $cliente) }}" variant="ghost">Cancelar</x-ui.button>
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
