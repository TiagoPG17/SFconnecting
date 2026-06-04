<x-layouts.app title="Usuarios">
    <x-slot:actions>
        <a href="{{ route('usuarios.create') }}"
           class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-blue-600 text-white text-xs font-medium rounded-lg hover:bg-blue-700 transition-colors">
            <x-ui.icon name="user-plus" class="w-3.5 h-3.5"/>
            Nuevo usuario
        </a>
    </x-slot:actions>

    {{-- Filtros --}}
    <form method="GET" class="mb-4 flex flex-wrap gap-2">
        <input
            type="text"
            name="buscar"
            value="{{ $filtros['buscar'] ?? '' }}"
            placeholder="Buscar nombre o email…"
            class="px-3 py-1.5 text-sm border border-slate-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 w-56"
        >
        <select name="rol" class="px-3 py-1.5 text-sm border border-slate-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
            <option value="">Todos los roles</option>
            @foreach ($roles as $rol)
                <option value="{{ $rol }}" @selected(($filtros['rol'] ?? '') === $rol)>{{ ucfirst($rol) }}</option>
            @endforeach
        </select>
        <select name="activo" class="px-3 py-1.5 text-sm border border-slate-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
            <option value="">Todos los estados</option>
            <option value="1" @selected(($filtros['activo'] ?? '') === '1')>Activos</option>
            <option value="0" @selected(($filtros['activo'] ?? '') === '0')>Inactivos</option>
        </select>
        <button type="submit" class="px-3 py-1.5 bg-slate-800 text-white text-xs font-medium rounded-lg hover:bg-slate-700 transition-colors">
            Filtrar
        </button>
        @if (array_filter($filtros))
            <a href="{{ route('usuarios.index') }}" class="px-3 py-1.5 text-xs text-slate-500 hover:text-slate-700 rounded-lg hover:bg-slate-100 transition-colors">
                Limpiar
            </a>
        @endif
    </form>

    <x-ui.card padding="none">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-100 text-sm">
                <thead>
                    <tr class="bg-slate-50">
                        <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Usuario</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Email</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Rol</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Estado</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Registrado</th>
                        <th class="px-4 py-3"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 bg-white">
                    @forelse ($usuarios as $usuario)
                        <tr class="hover:bg-slate-50 transition-colors" x-data>
                            <td class="px-4 py-3">
                                <div class="flex items-center gap-3">
                                    <div class="w-8 h-8 rounded-full bg-blue-100 flex items-center justify-center text-blue-700 text-xs font-semibold shrink-0">
                                        {{ strtoupper(substr($usuario->name, 0, 2)) }}
                                    </div>
                                    <span class="font-medium text-slate-900">{{ $usuario->name }}</span>
                                </div>
                            </td>
                            <td class="px-4 py-3 text-slate-600">{{ $usuario->email }}</td>
                            <td class="px-4 py-3">
                                @foreach ($usuario->getRoleNames() as $rol)
                                    <x-ui.badge :color="$rol === 'admin' ? 'red' : ($rol === 'gerente' ? 'amber' : 'blue')">
                                        {{ ucfirst($rol) }}
                                    </x-ui.badge>
                                @endforeach
                            </td>
                            <td class="px-4 py-3">
                                @if ($usuario->activo)
                                    <x-ui.badge color="green">Activo</x-ui.badge>
                                @else
                                    <x-ui.badge color="slate">Inactivo</x-ui.badge>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-slate-500 text-xs">
                                {{ $usuario->created_at->format('d/m/Y') }}
                            </td>
                            <td class="px-4 py-3">
                                <div class="flex items-center gap-2 justify-end">
                                    <a href="{{ route('usuarios.edit', $usuario) }}"
                                       class="p-1.5 text-slate-400 hover:text-blue-600 hover:bg-blue-50 rounded-lg transition-colors"
                                       title="Editar">
                                        <x-ui.icon name="edit" class="w-4 h-4"/>
                                    </a>
                                    @if ($usuario->id !== auth()->id())
                                        <button
                                            @click="
                                                const res = await $api('PATCH', '/api/usuarios/{{ $usuario->id }}/toggle');
                                                if (res.success) {
                                                    $store.toast.success(res.message);
                                                    setTimeout(() => location.reload(), 800);
                                                } else {
                                                    $store.toast.error(res.message);
                                                }
                                            "
                                            class="p-1.5 text-slate-400 hover:text-amber-600 hover:bg-amber-50 rounded-lg transition-colors"
                                            title="{{ $usuario->activo ? 'Desactivar' : 'Activar' }}">
                                            <x-ui.icon name="{{ $usuario->activo ? 'x-circle' : 'check-circle' }}" class="w-4 h-4"/>
                                        </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-4 py-12">
                                <x-ui.empty-state icon="users" message="No hay usuarios que coincidan con los filtros."/>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($usuarios->hasPages())
            <div class="px-4 py-3 border-t border-slate-100">
                {{ $usuarios->links() }}
            </div>
        @endif
    </x-ui.card>
</x-layouts.app>
