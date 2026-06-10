<x-layouts.app title="Editar Usuario">
    <x-slot:actions>
        <a href="{{ route('usuarios.index') }}"
           class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs text-slate-600 border border-slate-200 rounded-lg hover:bg-slate-50 transition-colors">
            <x-ui.icon name="arrow-left" class="w-3.5 h-3.5"/>
            Volver
        </a>
    </x-slot:actions>

    <div class="max-w-lg" x-data="{
        form: {
            name: '{{ addslashes($usuario->name) }}',
            email: '{{ $usuario->email }}',
            password: '',
            password_confirmation: '',
            rol: '{{ $usuario->getRoleNames()->first() ?? '' }}',
        },
        errors: {},
        loading: false,
        async guardar() {
            this.errors = {};
            this.loading = true;
            try {
                const payload = { ...this.form };
                if (!payload.password) {
                    delete payload.password;
                    delete payload.password_confirmation;
                }
                const res = await $api('PUT', '/api/usuarios/{{ $usuario->id }}', payload);
                if (res.success) {
                    $store.toast.success('Usuario actualizado.');
                    setTimeout(() => window.location.href = '{{ route('usuarios.index') }}', 800);
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
        <x-ui.card>
            <div class="flex items-center gap-3 mb-6">
                <div class="w-10 h-10 rounded-full bg-blue-100 flex items-center justify-center text-blue-700 font-semibold">
                    {{ strtoupper(substr($usuario->name, 0, 2)) }}
                </div>
                <div>
                    <h2 class="text-base font-semibold text-slate-900">{{ $usuario->name }}</h2>
                    <p class="text-xs text-slate-500">{{ $usuario->email }}</p>
                </div>
                @if ($usuario->activo)
                    <x-ui.badge color="green" class="ml-auto">Activo</x-ui.badge>
                @else
                    <x-ui.badge color="slate" class="ml-auto">Inactivo</x-ui.badge>
                @endif
            </div>

            <div class="space-y-4">
                <div>
                    <label class="block text-xs font-medium text-slate-700 mb-1">
                        Nombre completo <span class="text-red-500">*</span>
                    </label>
                    <x-ui.input
                        x-model="form.name"
                        @input="delete errors.name"
                        x-error="errors.name"/>
                </div>

                <div>
                    <label class="block text-xs font-medium text-slate-700 mb-1">
                        Email <span class="text-red-500">*</span>
                    </label>
                    <x-ui.input
                        type="email"
                        x-model="form.email"
                        @input="delete errors.email"
                        x-error="errors.email"/>
                </div>

                <div class="border-t border-slate-100 pt-4">
                    <p class="text-xs text-slate-500 mb-3">Deja en blanco para no cambiar la contraseña.</p>
                    <div class="space-y-3">
                        <div>
                            <label class="block text-xs font-medium text-slate-700 mb-1">Nueva contraseña</label>
                            <x-ui.input
                                type="password"
                                x-model="form.password"
                                @input="delete errors.password"
                                x-error="errors.password"
                                placeholder="Nueva contraseña (mínimo 8 caracteres)"/>
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-slate-700 mb-1">Confirmar nueva contraseña</label>
                            <x-ui.input
                                type="password"
                                x-model="form.password_confirmation"
                                @input="delete errors.password"
                                x-error="errors.password"
                                placeholder="Repite la nueva contraseña"/>
                        </div>
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-medium text-slate-700 mb-1">
                        Rol <span class="text-red-500">*</span>
                    </label>
                    <x-ui.select
                        x-model="form.rol"
                        @change="delete errors.rol"
                        x-error="errors.rol"
                        :placeholder="null">
                        @foreach ($roles as $rol)
                            <option value="{{ $rol }}" @selected($usuario->hasRole($rol))>{{ ucfirst($rol) }}</option>
                        @endforeach
                    </x-ui.select>
                </div>
            </div>

            <div class="flex items-center justify-between mt-6 pt-4 border-t border-slate-100">
                @if ($usuario->id !== auth()->id())
                    <button
                        x-data
                        @click="
                            const res = await $api('PATCH', '/api/usuarios/{{ $usuario->id }}/toggle');
                            if (res.success) {
                                $store.toast.success(res.message);
                                setTimeout(() => location.reload(), 800);
                            } else {
                                $store.toast.error(res.message);
                            }
                        "
                        class="text-xs text-{{ $usuario->activo ? 'red' : 'emerald' }}-600 hover:underline">
                        {{ $usuario->activo ? 'Desactivar usuario' : 'Activar usuario' }}
                    </button>
                @else
                    <span class="text-xs text-slate-400">No puedes modificar tu propia cuenta.</span>
                @endif

                <div class="flex gap-2">
                    <a href="{{ route('usuarios.index') }}"
                       class="px-4 py-2 text-sm text-slate-600 border border-slate-200 rounded-lg hover:bg-slate-50 transition-colors">
                        Cancelar
                    </a>
                    <x-ui.button @click="guardar" :loading="loading">
                        Guardar cambios
                    </x-ui.button>
                </div>
            </div>
        </x-ui.card>
    </div>
</x-layouts.app>
