<x-layouts.app title="Nuevo cliente">
    <x-slot name="actions">
        <x-ui.button href="{{ route('clientes.index') }}" variant="ghost" size="sm">
            <x-ui.icon name="arrow-left" class="w-4 h-4"/> Volver
        </x-ui.button>
    </x-slot>

    <div class="max-w-2xl mx-auto">
        <x-ui.card>
            <div class="p-6">
                <h2 class="text-base font-semibold text-slate-900 mb-6">Datos del cliente</h2>

                <form
                    x-data="{ loading: false }"
                    @submit.prevent="
                        loading = true;
                        const fd = new FormData($el);
                        const body = {};
                        fd.forEach((v, k) => { if (v !== '') body[k] = v; });
                        $api('POST', '/api/clientes', body)
                            .then(r => {
                                if (r.success) {
                                    $store.toast.success(r.message);
                                    setTimeout(() => window.location.href = '{{ route('clientes.index') }}', 800);
                                } else {
                                    $store.toast.error(r.message ?? 'Error al crear');
                                    loading = false;
                                }
                            }).catch(() => { $store.toast.error('Error de conexión'); loading = false; })
                    "
                    class="space-y-5"
                >
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                        <x-ui.input name="razon_social" label="Razón social" required placeholder="Nombre de la empresa"/>
                        <x-ui.input name="nit" label="NIT / Identificación" required placeholder="900123456-7"/>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                        <x-ui.input name="email" type="email" label="Email" placeholder="contacto@empresa.com"/>
                        <x-ui.input name="telefono" label="Teléfono" placeholder="+57 300 000 0000"/>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                        <x-ui.input name="ciudad" label="Ciudad" placeholder="Bogotá"/>
                        <x-ui.select name="estado" label="Estado">
                            <option value="activo">Activo</option>
                            <option value="prospecto" selected>Prospecto</option>
                            <option value="inactivo">Inactivo</option>
                        </x-ui.select>
                    </div>

                    <x-ui.input name="direccion" label="Dirección" placeholder="Calle 123 # 45-67"/>

                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1.5">Notas</label>
                        <textarea
                            name="notas"
                            rows="3"
                            placeholder="Información adicional del cliente..."
                            class="w-full px-3 py-2 text-sm rounded-lg border border-slate-300 focus:outline-none focus:ring-2 focus:ring-blue-500 resize-none"
                        ></textarea>
                    </div>

                    <div class="flex justify-end gap-3 pt-2">
                        <x-ui.button href="{{ route('clientes.index') }}" variant="ghost">Cancelar</x-ui.button>
                        <x-ui.button type="submit" variant="primary" x-bind:disabled="loading">
                            <span x-show="!loading">Crear cliente</span>
                            <span x-show="loading">Guardando...</span>
                        </x-ui.button>
                    </div>
                </form>
            </div>
        </x-ui.card>
    </div>
</x-layouts.app>
