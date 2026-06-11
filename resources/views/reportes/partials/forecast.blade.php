<div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6">
    <x-ui.stat-card label="Total en negociación" :value="'$' . number_format($datos['total_pipeline'] ?? 0, 0, ',', '.')" color="blue" icon="briefcase"/>
    <x-ui.stat-card label="Valor esperado del pipeline" :value="'$' . number_format($datos['total_forecast'] ?? 0, 0, ',', '.')" color="green" icon="trending-up"/>
    <x-ui.stat-card label="Negocios activos" :value="$datos['cantidad'] ?? 0" color="purple" icon="bar-chart"/>
</div>

@if(!empty($datos['por_asesor']))
<x-ui.card>
    <div class="p-4 border-b border-slate-100">
        <h3 class="text-sm font-semibold text-slate-900">Proyección por asesor</h3>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="border-b border-slate-100">
                    <th class="text-left py-2 px-4 text-xs font-semibold text-slate-500 uppercase tracking-wide">Asesor</th>
                    <th class="text-right py-2 px-4 text-xs font-semibold text-slate-500 uppercase tracking-wide">Negocios</th>
                    <th class="text-right py-2 px-4 text-xs font-semibold text-slate-500 uppercase tracking-wide">En negociación</th>
                    <th class="text-right py-2 px-4 text-xs font-semibold text-slate-500 uppercase tracking-wide">Valor esperado</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @foreach($datos['por_asesor'] as $row)
                <tr class="hover:bg-slate-50">
                    <td class="py-2.5 px-4 font-medium text-slate-900">{{ $row['asesor'] ?? '—' }}</td>
                    <td class="py-2.5 px-4 text-right text-slate-600">{{ $row['cantidad'] }}</td>
                    <td class="py-2.5 px-4 text-right text-slate-600">${{ number_format($row['valor_pipeline'] ?? 0, 0, ',', '.') }}</td>
                    <td class="py-2.5 px-4 text-right font-semibold text-blue-600">${{ number_format($row['valor_forecast'] ?? 0, 0, ',', '.') }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</x-ui.card>
@else
<x-ui.card>
    <x-ui.empty-state icon="trending-up" title="Sin datos" description="No hay negocios en el periodo seleccionado."/>
</x-ui.card>
@endif
