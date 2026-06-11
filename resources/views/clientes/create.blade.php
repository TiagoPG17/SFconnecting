<x-layouts.app title="Nuevo cliente">
    <x-slot name="actions">
        <x-ui.button href="{{ route('clientes.index') }}" variant="ghost" size="sm">
            <x-ui.icon name="arrow-left" class="w-4 h-4"/> Volver
        </x-ui.button>
    </x-slot>

    <div class="max-w-2xl mx-auto" x-data="{
        form: {
            razon_social: '', nit: '', email: '', telefono: '',
            ciudad: '', direccion: '', estado: 'prospecto', notas: ''
        },
        errors: {},
        loading: false,
        async guardar() {
            this.errors = {};
            this.loading = true;
            try {
                const body = Object.fromEntries(
                    Object.entries(this.form).filter(([, v]) => v !== '')
                );
                const r = await $api('POST', '/api/clientes', body);
                if (r.success) {
                    $store.toast.success(r.message);
                    setTimeout(() => window.location.href = '{{ route('clientes.index') }}', 800);
                } else {
                    this.errors = r.errors ?? {};
                    const msgs = Object.values(this.errors).flat();
                    $store.toast.error(
                        msgs.length > 1
                            ? `Corrige los ${msgs.length} errores marcados.`
                            : (msgs[0] ?? r.message ?? 'Error al crear')
                    );
                }
            } catch {
                $store.toast.error('Error de conexión');
            } finally {
                this.loading = false;
            }
        }
    }">
        <x-ui.card>
            <div class="p-8">
                <h2 class="text-base font-semibold text-slate-900 mb-8">Datos del cliente</h2>

                <div class="space-y-6">
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1.5">
                                Razón social <span class="text-red-500">*</span>
                            </label>
                            <x-ui.input
                                x-model="form.razon_social"
                                @input="delete errors.razon_social"
                                x-error="errors.razon_social"
                                placeholder="Nombre de la empresa"/>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1.5">
                                NIT / Identificación <span class="text-red-500">*</span>
                            </label>
                            <x-ui.input
                                x-model="form.nit"
                                @input="delete errors.nit"
                                x-error="errors.nit"
                                placeholder="900123456"/>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1.5">Email</label>
                            <x-ui.input
                                type="email"
                                x-model="form.email"
                                @input="delete errors.email"
                                x-error="errors.email"
                                placeholder="contacto@empresa.com"/>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1.5">Teléfono</label>
                            <x-ui.input
                                x-model="form.telefono"
                                @input="delete errors.telefono"
                                x-error="errors.telefono"
                                placeholder="+57 300 000 0000"/>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1.5">Ciudad</label>
                            <x-ui.input
                                x-model="form.ciudad"
                                @input="delete errors.ciudad"
                                x-error="errors.ciudad"
                                placeholder="Bogotá"/>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1.5">Estado</label>
                            <x-ui.select
                                x-model="form.estado"
                                @change="delete errors.estado"
                                x-error="errors.estado"
                                :placeholder="null">
                                <option value="activo">Activo</option>
                                <option value="prospecto" selected>Prospecto</option>
                                <option value="inactivo">Inactivo</option>
                            </x-ui.select>
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1.5">Dirección</label>
                        <x-ui.input
                            x-model="form.direccion"
                            @input="delete errors.direccion"
                            x-error="errors.direccion"
                            placeholder="Calle 123 # 45-67"/>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1.5">Notas</label>
                        <textarea
                            x-model="form.notas"
                            rows="3"
                            placeholder="Información adicional del cliente..."
                            class="w-full px-3 py-2 text-sm rounded-lg border border-slate-300 focus:outline-none focus:ring-2 focus:ring-blue-500 resize-none"
                        ></textarea>
                    </div>

                    <div class="flex justify-end gap-3 pt-6 border-t border-slate-100 mt-2">
                        <x-ui.button href="{{ route('clientes.index') }}" variant="secondary">Cancelar</x-ui.button>
                        <x-ui.button @click="guardar" variant="primary" x-bind:disabled="loading">
                            <span x-show="!loading">Crear cliente</span>
                            <span x-show="loading" x-cloak>Guardando...</span>
                        </x-ui.button>
                    </div>
                </div>
            </div>
        </x-ui.card>
    </div>
</x-layouts.app>
