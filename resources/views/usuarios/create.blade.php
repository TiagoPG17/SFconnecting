<x-layouts.app title="Nuevo Usuario">
    <x-slot:actions>
        <a href="{{ route('usuarios.index') }}"
           class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs text-slate-600 border border-slate-200 rounded-lg hover:bg-slate-50 transition-colors">
            <x-ui.icon name="arrow-left" class="w-3.5 h-3.5"/>
            Volver
        </a>
    </x-slot:actions>

    <div class="max-w-xl" x-data="{
        form: { name: '', email: '', password: '', password_confirmation: '', rol: '' },
        errors: {},
        loading: false,
        async guardar() {
            this.errors = {};
            this.loading = true;
            try {
                const res = await $api('POST', '/api/usuarios', this.form);
                if (res.success) {
                    const esComercial = this.form.rol === 'comercial';
                    $store.toast.success(esComercial
                        ? 'Usuario creado. Ahora mapea su vendedor SIESA.'
                        : 'Usuario creado exitosamente.');
                    const dest = esComercial
                        ? @js(route('mapeo-vendedores.index'))
                        : @js(route('usuarios.index'));
                    setTimeout(() => window.location.href = dest, 900);
                } else {
                    this.errors = res.errors ?? {};
                    const msgs = Object.values(this.errors).flat();
                    $store.toast.error(
                        msgs.length > 1
                            ? `Corrige los ${msgs.length} errores marcados.`
                            : (msgs[0] ?? res.message)
                    );
                }
            } finally {
                this.loading = false;
            }
        }
    }">
        <x-ui.card class="p-6">
            {{-- Encabezado --}}
            <div class="flex items-center gap-3 mb-7">
                <div class="w-9 h-9 rounded-lg bg-blue-50 flex items-center justify-center shrink-0">
                    <x-ui.icon name="user-plus" class="w-5 h-5 text-blue-600"/>
                </div>
                <div>
                    <h2 class="text-base font-semibold text-slate-900 leading-tight">Nuevo usuario</h2>
                    <p class="text-xs text-slate-500 mt-0.5">Completa los datos para crear la cuenta.</p>
                </div>
            </div>

            {{-- Sección: Información personal --}}
            <p class="text-[11px] font-semibold text-slate-400 uppercase tracking-wider mb-3">Información personal</p>
            <div class="space-y-4">
                <x-ui.input
                    label="Nombre completo"
                    required
                    x-model="form.name"
                    @input="delete errors.name"
                    x-error="errors.name"
                    placeholder="Nombre del usuario"/>

                <x-ui.input
                    type="email"
                    label="Correo electrónico"
                    required
                    x-model="form.email"
                    @input="delete errors.email"
                    x-error="errors.email"
                    placeholder="correo@empresa.com"/>
            </div>

            {{-- Sección: Seguridad --}}
            <div class="mt-6 pt-5 border-t border-slate-100">
                <p class="text-[11px] font-semibold text-slate-400 uppercase tracking-wider mb-3">Seguridad</p>
                <div class="grid grid-cols-2 gap-4">
                    <x-ui.input
                        type="password"
                        label="Contraseña"
                        required
                        x-model="form.password"
                        @input="delete errors.password"
                        x-error="errors.password"
                        placeholder="Mínimo 8 caracteres"/>

                    <x-ui.input
                        type="password"
                        label="Confirmar contraseña"
                        required
                        x-model="form.password_confirmation"
                        @input="delete errors.password"
                        x-error="errors.password"
                        placeholder="Repite la contraseña"/>
                </div>
            </div>

            {{-- Sección: Permisos --}}
            <div class="mt-6 pt-5 border-t border-slate-100">
                <p class="text-[11px] font-semibold text-slate-400 uppercase tracking-wider mb-3">Permisos</p>
                <x-ui.select
                    label="Rol del usuario"
                    x-model="form.rol"
                    @change="delete errors.rol"
                    x-error="errors.rol"
                    :placeholder="'Selecciona un rol…'">
                    @foreach ($roles as $rol)
                        <option value="{{ $rol }}">{{ ucwords(str_replace('_', ' ', $rol)) }}</option>
                    @endforeach
                </x-ui.select>
            </div>

            {{-- Acciones --}}
            <div class="flex justify-end gap-2 mt-7 pt-5 border-t border-slate-100">
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
