<x-layouts.app title="Nuevo Usuario">
    <x-slot:actions>
        <a href="{{ route('usuarios.index') }}"
           class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs text-slate-600 border border-slate-200 rounded-lg hover:bg-slate-50 transition-colors">
            <x-ui.icon name="arrow-left" class="w-3.5 h-3.5"/>
            Volver
        </a>
    </x-slot:actions>

    <div class="max-w-lg" x-data="{
        form: { name: '', email: '', password: '', password_confirmation: '', rol: '' },
        loading: false,
        async guardar() {
            this.loading = true;
            try {
                const res = await $api('POST', '/api/usuarios', this.form);
                if (res.success) {
                    const esComercial = this.form.rol === 'comercial';
                    $store.toast.success(esComercial ? 'Usuario creado. Ahora mapea su vendedor SIESA.' : 'Usuario creado exitosamente.');
                    const dest = esComercial ? @js(route('mapeo-vendedores.index')) : @js(route('usuarios.index'));
                    setTimeout(() => window.location.href = dest, 900);
                } else {
                    const msgs = Object.values(res.errors ?? {}).flat();
                    $store.toast.error(msgs[0] ?? res.message);
                }
            } finally {
                this.loading = false;
            }
        }
    }">
        <x-ui.card>
            <h2 class="text-base font-semibold text-slate-900 mb-6">Datos del nuevo usuario</h2>

            <div class="space-y-4">
                <div>
                    <label class="block text-xs font-medium text-slate-700 mb-1">Nombre completo <span class="text-red-500">*</span></label>
                    <x-ui.input x-model="form.name" placeholder="Nombre del usuario"/>
                </div>

                <div>
                    <label class="block text-xs font-medium text-slate-700 mb-1">Email <span class="text-red-500">*</span></label>
                    <x-ui.input type="email" x-model="form.email" placeholder="correo@empresa.com"/>
                </div>

                <div>
                    <label class="block text-xs font-medium text-slate-700 mb-1">Contraseña <span class="text-red-500">*</span></label>
                    <x-ui.input type="password" x-model="form.password" placeholder="Mínimo 8 caracteres"/>
                </div>

                <div>
                    <label class="block text-xs font-medium text-slate-700 mb-1">Confirmar contraseña <span class="text-red-500">*</span></label>
                    <x-ui.input type="password" x-model="form.password_confirmation" placeholder="Repite la contraseña"/>
                </div>

                <div>
                    <label class="block text-xs font-medium text-slate-700 mb-1">Rol <span class="text-red-500">*</span></label>
                    <x-ui.select x-model="form.rol">
                        <option value="">Selecciona un rol…</option>
                        @foreach ($roles as $rol)
                            <option value="{{ $rol }}">{{ ucfirst($rol) }}</option>
                        @endforeach
                    </x-ui.select>
                </div>
            </div>

            <div class="flex justify-end gap-2 mt-6 pt-4 border-t border-slate-100">
                <a href="{{ route('usuarios.index') }}"
                   class="px-4 py-2 text-sm text-slate-600 border border-slate-200 rounded-lg hover:bg-slate-50 transition-colors">
                    Cancelar
                </a>
                <x-ui.button @click="guardar" x-bind:disabled="loading" x-text="loading ? 'Guardando...' : 'Crear usuario'">
                    Crear usuario
                </x-ui.button>
            </div>
        </x-ui.card>
    </div>
</x-layouts.app>
