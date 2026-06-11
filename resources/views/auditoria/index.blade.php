<x-layouts.app title="Auditoría">

    {{-- Filtros --}}
    <form method="GET" class="mb-4 flex flex-wrap gap-2">

        <select name="usuario_id" class="px-3 py-1.5 text-sm border border-slate-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
            <option value="">Todos los usuarios</option>
            @foreach ($usuarios as $u)
                <option value="{{ $u->id }}" @selected(($filtros['usuario_id'] ?? '') == $u->id)>
                    {{ $u->name }}
                </option>
            @endforeach
        </select>

        <select name="modulo" class="px-3 py-1.5 text-sm border border-slate-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
            <option value="">Todos los módulos</option>
            @foreach (['Autenticación','Clientes','Contactos','Prospectos','Negocios','Seguimientos','Usuarios','Presupuestos','Reportes','Dashboard','Mi desempeño','Visión gerencial'] as $mod)
                <option value="{{ $mod }}" @selected(($filtros['modulo'] ?? '') === $mod)>{{ $mod }}</option>
            @endforeach
        </select>

        <select name="accion" class="px-3 py-1.5 text-sm border border-slate-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
            <option value="">Todas las acciones</option>
            @foreach (['login','logout','ver','crear','editar','eliminar'] as $ac)
                <option value="{{ $ac }}" @selected(($filtros['accion'] ?? '') === $ac)>{{ ucfirst($ac) }}</option>
            @endforeach
        </select>

        <input type="date" name="desde" value="{{ $filtros['desde'] ?? '' }}"
               class="px-3 py-1.5 text-sm border border-slate-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">

        <input type="date" name="hasta" value="{{ $filtros['hasta'] ?? '' }}"
               class="px-3 py-1.5 text-sm border border-slate-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">

        <button type="submit"
                class="px-3 py-1.5 bg-slate-800 text-white text-xs font-medium rounded-lg hover:bg-slate-700 transition-colors">
            Filtrar
        </button>

        @if (array_filter($filtros))
            <a href="{{ route('auditoria.index') }}"
               class="px-3 py-1.5 text-xs text-slate-500 hover:text-slate-700 rounded-lg hover:bg-slate-100 transition-colors">
                Limpiar
            </a>
        @endif
    </form>

    <x-ui.card padding="none">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-100 text-sm">
                <thead>
                    <tr class="bg-slate-50">
                        <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Fecha</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Usuario</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Acción</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Módulo</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Descripción</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">IP</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 bg-white">
                    @forelse ($registros as $log)
                        <tr class="hover:bg-slate-50 transition-colors">

                            <td class="px-4 py-3 text-xs text-slate-500 whitespace-nowrap">
                                {{ $log->created_at->format('d/m/Y H:i:s') }}
                            </td>

                            <td class="px-4 py-3">
                                @if ($log->usuario)
                                    <div class="flex items-center gap-2">
                                        <div class="w-6 h-6 rounded-full bg-blue-100 flex items-center justify-center text-blue-700 text-xs font-semibold shrink-0">
                                            {{ strtoupper(substr($log->usuario->name, 0, 2)) }}
                                        </div>
                                        <span class="text-slate-700 text-xs font-medium">{{ $log->usuario->name }}</span>
                                    </div>
                                @else
                                    <span class="text-slate-400 text-xs">—</span>
                                @endif
                            </td>

                            <td class="px-4 py-3">
                                @php
                                    $colores = [
                                        'login'    => 'blue',
                                        'logout'   => 'slate',
                                        'ver'      => 'slate',
                                        'crear'    => 'green',
                                        'editar'   => 'amber',
                                        'eliminar' => 'red',
                                    ];
                                    $color = $colores[$log->accion] ?? 'slate';
                                @endphp
                                <x-ui.badge :color="$color">{{ ucfirst($log->accion) }}</x-ui.badge>
                            </td>

                            <td class="px-4 py-3 text-slate-600 text-xs">
                                {{ $log->modulo }}
                            </td>

                            <td class="px-4 py-3 text-slate-500 text-xs max-w-xs truncate" title="{{ $log->descripcion }}">
                                {{ $log->descripcion }}
                            </td>

                            <td class="px-4 py-3 text-slate-400 text-xs font-mono">
                                {{ $log->ip ?? '—' }}
                            </td>

                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-4 py-12">
                                <x-ui.empty-state icon="clipboard" title="No hay registros de actividad con los filtros aplicados."/>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($registros->hasPages())
            <div class="px-4 py-3 border-t border-slate-100">
                {{ $registros->links() }}
            </div>
        @endif
    </x-ui.card>

</x-layouts.app>
