<x-layouts.app title="Forecast Comercial">
    <x-slot name="actions">
        <x-ui.button href="{{ route('negocios.index') }}" variant="ghost" size="sm">
            <x-ui.icon name="list" class="w-4 h-4"/> Negocios
        </x-ui.button>
    </x-slot>

    {{-- Filtros --}}
    <x-ui.card class="p-4 mb-6"
        x-data="{
            mes: '{{ request('mes', now()->month) }}',
            anio: '{{ request('anio', now()->year) }}',
            goFilter() {
                let url = new URL(window.location.href);
                url.searchParams.set('mes', this.mes);
                url.searchParams.set('anio', this.anio);
                window.location.href = url.toString();
            }
        }"
    >
        <div class="flex flex-wrap gap-3 items-end">
            <div>
                <label class="block text-xs font-medium text-slate-500 mb-1">Mes</label>
                <select x-model="mes" @change="goFilter()"
                    class="px-3 py-2 text-sm rounded-lg border border-slate-300 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    @foreach(range(1, 12) as $m)
                        <option value="{{ $m }}">{{ \Carbon\Carbon::create()->month($m)->locale('es')->monthName }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs font-medium text-slate-500 mb-1">Año</label>
                <select x-model="anio" @change="goFilter()"
                    class="px-3 py-2 text-sm rounded-lg border border-slate-300 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    @foreach(range(now()->year - 2, now()->year + 2) as $y)
                        <option value="{{ $y }}">{{ $y }}</option>
                    @endforeach
                </select>
            </div>
        </div>
    </x-ui.card>

    {{-- KPIs --}}
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6">
        <x-ui.stat-card
            label="Pipeline total"
            :value="'$' . number_format($forecast['total_pipeline'] ?? 0, 0, ',', '.')"
            color="blue"
            icon="briefcase"
        />
        <x-ui.stat-card
            label="Forecast ponderado"
            :value="'$' . number_format($forecast['total_forecast'] ?? 0, 0, ',', '.')"
            color="green"
            icon="trending-up"
        />
        <x-ui.stat-card
            label="Negocios activos"
            :value="$forecast['cantidad'] ?? 0"
            color="purple"
            icon="bar-chart"
        />
    </div>

    {{-- Por estado --}}
    @if(!empty($forecast['por_estado']))
    <x-ui.card class="mb-6">
        <div class="p-6">
            <h3 class="text-sm font-semibold text-slate-900 mb-4">Distribución por estado</h3>
            <div class="space-y-3">
                @foreach($forecast['por_estado'] as $item)
                <div class="flex items-center gap-3">
                    <div class="w-2.5 h-2.5 rounded-full shrink-0" style="background-color: {{ $item->color ?? '#94a3b8' }}"></div>
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center justify-between mb-1">
                            <span class="text-sm text-slate-700">{{ $item->nombre }}</span>
                            <span class="text-sm font-medium text-slate-900">
                                ${{ number_format($item->valor_forecast ?? 0, 0, ',', '.') }}
                            </span>
                        </div>
                        @php
                            $pct = $forecast['total_forecast'] > 0
                                ? (($item->valor_forecast ?? 0) / $forecast['total_forecast']) * 100
                                : 0;
                        @endphp
                        <x-ui.progress :value="round($pct)" size="xs"/>
                    </div>
                    <span class="text-xs text-slate-400 shrink-0">{{ $item->cantidad }} neg.</span>
                </div>
                @endforeach
            </div>
        </div>
    </x-ui.card>
    @endif

    {{-- Por asesor --}}
    @if(!empty($forecast['por_asesor']))
    <x-ui.card>
        <div class="p-6">
            <h3 class="text-sm font-semibold text-slate-900 mb-4">Forecast por asesor</h3>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-slate-100">
                            <th class="text-left py-2 px-3 text-xs font-semibold text-slate-500 uppercase tracking-wide">Asesor</th>
                            <th class="text-right py-2 px-3 text-xs font-semibold text-slate-500 uppercase tracking-wide">Negocios</th>
                            <th class="text-right py-2 px-3 text-xs font-semibold text-slate-500 uppercase tracking-wide">Pipeline</th>
                            <th class="text-right py-2 px-3 text-xs font-semibold text-slate-500 uppercase tracking-wide">Forecast</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach($forecast['por_asesor'] as $row)
                        <tr class="hover:bg-slate-50">
                            <td class="py-2.5 px-3 font-medium text-slate-900">{{ $row->asesor }}</td>
                            <td class="py-2.5 px-3 text-right text-slate-600">{{ $row->cantidad }}</td>
                            <td class="py-2.5 px-3 text-right text-slate-600">${{ number_format($row->valor_pipeline ?? 0, 0, ',', '.') }}</td>
                            <td class="py-2.5 px-3 text-right font-semibold text-blue-600">${{ number_format($row->valor_forecast ?? 0, 0, ',', '.') }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </x-ui.card>
    @else
    <x-ui.card>
        <x-ui.empty-state icon="trending-up" title="Sin datos de forecast"
            description="No hay negocios activos en el periodo seleccionado."/>
    </x-ui.card>
    @endif
</x-layouts.app>
