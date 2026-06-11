<div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6">
    <x-ui.stat-card label="Total negocios" :value="$datos['total'] ?? 0" color="blue" icon="briefcase"/>
    <x-ui.stat-card label="Valor en negociación" :value="'$' . number_format($datos['valor_total'] ?? 0, 0, ',', '.')" color="green" icon="trending-up"/>
    <x-ui.stat-card label="Ganados" :value="$datos['ganados'] ?? 0" color="purple" icon="check"/>
</div>

@if(!empty($datos['por_estado']))
<x-ui.card>
    <div class="p-4 border-b border-slate-100">
        <h3 class="text-sm font-semibold text-slate-900">Por etapa de negociación</h3>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="border-b border-slate-100">
                    <th class="text-left py-2 px-4 text-xs font-semibold text-slate-500 uppercase tracking-wide">Estado</th>
                    <th class="text-right py-2 px-4 text-xs font-semibold text-slate-500 uppercase tracking-wide">Cantidad</th>
                    <th class="text-right py-2 px-4 text-xs font-semibold text-slate-500 uppercase tracking-wide">Valor</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @foreach($datos['por_estado'] as $row)
                <tr class="hover:bg-slate-50">
                    <td class="py-2.5 px-4 text-slate-800">{{ $row['estado'] }}</td>
                    <td class="py-2.5 px-4 text-right text-slate-600">{{ $row['total'] }}</td>
                    <td class="py-2.5 px-4 text-right font-semibold text-slate-900">${{ number_format($row['valor'] ?? 0, 0, ',', '.') }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</x-ui.card>
@else
<x-ui.card>
    <x-ui.empty-state icon="briefcase" title="Sin datos" description="No hay negocios en el periodo seleccionado."/>
</x-ui.card>
@endif
