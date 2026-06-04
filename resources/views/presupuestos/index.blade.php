<x-layouts.app title="Presupuestos · CRM">
<x-slot name="actions">
    <a href="{{ route('presupuestos.index', ['anio' => $anio]) }}"
       class="text-xs text-slate-400">Año {{ $anio }}</a>
</x-slot>

<div x-data="{ modalCrear: false, modalEditar: false, editando: null }">

    {{-- ===== Cabecera ===== --}}
    <div class="flex items-center justify-between mb-6">
        <div>
            <h2 class="text-sm font-bold text-slate-800 uppercase tracking-wider">Presupuestos anuales</h2>
            <p class="text-xs text-slate-400 mt-0.5">Metas de venta por asesor</p>
        </div>
        <div class="flex items-center gap-3">
            {{-- Selector de año --}}
            <form method="GET" action="{{ route('presupuestos.index') }}">
                <select name="anio" onchange="this.form.submit()"
                        class="text-sm border border-slate-200 rounded-lg px-3 py-1.5 bg-white text-slate-700 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    @foreach($anios as $a)
                        <option value="{{ $a }}" {{ $anio == $a ? 'selected' : '' }}>{{ $a }}</option>
                    @endforeach
                </select>
            </form>
            <button @click="modalCrear = true"
                    class="inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-blue-600 text-white text-sm font-semibold hover:bg-blue-700 transition-colors">
                <x-ui.icon name="plus" class="w-4 h-4"/>
                Nuevo presupuesto
            </button>
        </div>
    </div>

    {{-- ===== Alertas ===== --}}
    @if(session('success'))
    <div class="mb-4 px-4 py-3 bg-emerald-50 border border-emerald-200 text-emerald-700 rounded-xl text-sm">
        {{ session('success') }}
    </div>
    @endif

    @if($errors->any())
    <div class="mb-4 px-4 py-3 bg-red-50 border border-red-200 text-red-700 rounded-xl text-sm">
        {{ $errors->first() }}
    </div>
    @endif

    {{-- ===== Tabla ===== --}}
    <x-ui.card class="overflow-hidden">
        @if($presupuestos->isEmpty())
            <x-ui.empty-state icon="bar-chart"
                title="Sin presupuestos para {{ $anio }}"
                description="Crea el primer presupuesto del año haciendo clic en «Nuevo presupuesto»." />
        @else
        <table class="w-full text-sm">
            <thead class="bg-slate-50 border-b border-slate-100">
                <tr class="text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">
                    <th class="px-5 py-3">Asesor</th>
                    <th class="px-5 py-3 text-right">Presupuesto {{ $anio }}</th>
                    <th class="px-5 py-3 text-center">Compañía</th>
                    <th class="px-5 py-3"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @foreach($presupuestos as $p)
                <tr class="hover:bg-slate-50 transition-colors">
                    <td class="px-5 py-4">
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 rounded-full bg-blue-100 text-blue-700 flex items-center justify-center text-xs font-bold shrink-0">
                                {{ strtoupper(substr($p->asesor?->name ?? 'U', 0, 2)) }}
                            </div>
                            <div>
                                <p class="font-medium text-slate-900">{{ $p->asesor?->name ?? '—' }}</p>
                                <p class="text-xs text-slate-400">{{ $p->asesor?->email ?? '' }}</p>
                            </div>
                        </div>
                    </td>
                    <td class="px-5 py-4 text-right font-semibold text-slate-900">
                        ${{ number_format($p->presupuesto, 0, ',', '.') }}
                    </td>
                    <td class="px-5 py-4 text-center">
                        <span class="px-2 py-0.5 rounded-full text-xs bg-slate-100 text-slate-600 font-medium">
                            Cía. {{ $p->compania }}
                        </span>
                    </td>
                    <td class="px-5 py-4 text-right">
                        <div class="flex items-center justify-end gap-2">
                            <button @click="editando = {{ $p->toJson() }}; modalEditar = true"
                                    class="p-1.5 rounded-lg text-slate-400 hover:text-blue-600 hover:bg-blue-50 transition-colors">
                                <x-ui.icon name="edit" class="w-4 h-4"/>
                            </button>
                            <form method="POST" action="{{ route('presupuestos.destroy', $p) }}"
                                  onsubmit="return confirm('¿Eliminar presupuesto de {{ $p->asesor?->name }}?')">
                                @csrf @method('DELETE')
                                <button type="submit"
                                        class="p-1.5 rounded-lg text-slate-400 hover:text-red-600 hover:bg-red-50 transition-colors">
                                    <x-ui.icon name="trash" class="w-4 h-4"/>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
            <tfoot class="bg-slate-50 border-t border-slate-200">
                <tr>
                    <td class="px-5 py-3 text-sm font-semibold text-slate-700">
                        Total — {{ $presupuestos->count() }} asesores
                    </td>
                    <td class="px-5 py-3 text-right text-sm font-bold text-slate-900">
                        ${{ number_format($presupuestos->sum('presupuesto'), 0, ',', '.') }}
                    </td>
                    <td colspan="2"></td>
                </tr>
            </tfoot>
        </table>
        @endif
    </x-ui.card>

    {{-- ===== Modal Crear ===== --}}
    <div x-show="modalCrear" x-cloak
         class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 backdrop-blur-sm"
         @keydown.escape.window="modalCrear = false">
        <div @click.outside="modalCrear = false"
             class="bg-white rounded-2xl shadow-xl w-full max-w-md mx-4 p-6">
            <h3 class="text-base font-bold text-slate-900 mb-5">Nuevo presupuesto</h3>
            <form method="POST" action="{{ route('presupuestos.store') }}" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1.5">Asesor</label>
                    <select name="asesor_id" required
                            class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="">Selecciona un asesor...</option>
                        @foreach($asesores as $a)
                            <option value="{{ $a->id }}">{{ $a->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1.5">Año</label>
                    <select name="anio" required
                            class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                        @foreach($anios as $a)
                            <option value="{{ $a }}" {{ $anio == $a ? 'selected' : '' }}>{{ $a }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1.5">Presupuesto anual ($)</label>
                    <input type="number" name="presupuesto" min="0" step="1000000" required
                           placeholder="Ej: 500000000"
                           class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"/>
                    <p class="text-xs text-slate-400 mt-1">Ingresa el valor en pesos colombianos</p>
                </div>
                <div class="flex gap-3 pt-2">
                    <button type="button" @click="modalCrear = false"
                            class="flex-1 py-2 rounded-lg border border-slate-200 text-sm font-semibold text-slate-600 hover:bg-slate-50 transition-colors">
                        Cancelar
                    </button>
                    <button type="submit"
                            class="flex-1 py-2 rounded-lg bg-blue-600 text-white text-sm font-semibold hover:bg-blue-700 transition-colors">
                        Guardar
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- ===== Modal Editar ===== --}}
    <div x-show="modalEditar" x-cloak
         class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 backdrop-blur-sm"
         @keydown.escape.window="modalEditar = false">
        <div @click.outside="modalEditar = false"
             class="bg-white rounded-2xl shadow-xl w-full max-w-md mx-4 p-6">
            <h3 class="text-base font-bold text-slate-900 mb-1">Editar presupuesto</h3>
            <p class="text-sm text-slate-400 mb-5" x-text="editando?.asesor?.name ?? ''"></p>
            <template x-if="editando">
                <form :action="`/presupuestos/${editando.id}`" method="POST" class="space-y-4">
                    @csrf @method('PUT')
                    <div>
                        <label class="block text-xs font-semibold text-slate-600 mb-1.5">
                            Presupuesto <span x-text="editando.anio"></span> ($)
                        </label>
                        <input type="number" name="presupuesto" min="0" step="1000000" required
                               :value="editando.presupuesto"
                               class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"/>
                        <p class="text-xs text-slate-400 mt-1">Valor actual:
                            <span class="font-semibold" x-text="'$' + Number(editando.presupuesto).toLocaleString('es-CO')"></span>
                        </p>
                    </div>
                    <div class="flex gap-3 pt-2">
                        <button type="button" @click="modalEditar = false"
                                class="flex-1 py-2 rounded-lg border border-slate-200 text-sm font-semibold text-slate-600 hover:bg-slate-50 transition-colors">
                            Cancelar
                        </button>
                        <button type="submit"
                                class="flex-1 py-2 rounded-lg bg-blue-600 text-white text-sm font-semibold hover:bg-blue-700 transition-colors">
                            Actualizar
                        </button>
                    </div>
                </form>
            </template>
        </div>
    </div>

</div>
</x-layouts.app>
