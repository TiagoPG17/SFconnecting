<x-layouts.app title="Clientes huérfanos · CRM">

<div class="mb-6">
    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-sm font-bold text-slate-800 uppercase tracking-wider">Clientes huérfanos</h2>
            <p class="text-xs text-slate-400 mt-0.5">
                Clientes de Contiflex sin asesor asignado · CIA {{ $compania }}
                @if($esAsesor) <span class="text-blue-500">(solo tu compañía)</span> @endif
            </p>
        </div>
        @if($erpDisponible)
        <span class="inline-flex items-center gap-1.5 text-xs bg-emerald-100 text-emerald-700 px-3 py-1 rounded-full font-semibold border border-emerald-200">
            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
            {{ count($huerfanos) }} disponibles
        </span>
        @endif
    </div>
</div>

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

@if(!$erpDisponible)
<x-ui.card class="p-10 text-center">
    <p class="text-slate-500 text-sm">Sin conexión al ERP. No se pueden cargar los clientes huérfanos.</p>
</x-ui.card>

@elseif(empty($huerfanos))
<x-ui.card class="p-10 text-center">
    <p class="text-slate-500 text-sm">No hay clientes huérfanos disponibles en este momento.</p>
</x-ui.card>

@else
<x-ui.card class="overflow-hidden">
    <table class="w-full text-sm">
        <thead class="bg-slate-50 border-b border-slate-100">
            <tr class="text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">
                <th class="px-5 py-3">Cliente</th>
                <th class="px-5 py-3">Ciudad</th>
                <th class="px-5 py-3 text-right">Facturación histórica</th>
                <th class="px-5 py-3 text-center">Días sin comprar</th>
                <th class="px-5 py-3 text-center">Última compra</th>
                <th class="px-5 py-3"></th>
            </tr>
        </thead>
        <tbody class="divide-y divide-slate-100">
            @foreach($huerfanos as $c)
            @php
                $dias = (int) ($c['DIAS_DESDE_ULTIMA_COMPRA'] ?? 0);
                $diasColor = $dias > 180 ? 'text-red-600' : ($dias > 90 ? 'text-amber-600' : 'text-slate-600');
                $valor = (float) ($c['VLR_NETO_FACTURADO'] ?? 0);
            @endphp
            <tr class="hover:bg-slate-50 transition-colors">
                <td class="px-5 py-4">
                    <p class="font-semibold text-slate-900">{{ $c['RAZON_SOCIAL'] }}</p>
                    <p class="text-xs text-slate-400 font-mono mt-0.5">{{ $c['NIT'] }}</p>
                </td>
                <td class="px-5 py-4 text-slate-500 text-xs">{{ $c['CIUDAD'] ?? '—' }}</td>
                <td class="px-5 py-4 text-right font-semibold text-slate-800">
                    ${{ number_format($valor, 0, ',', '.') }}
                </td>
                <td class="px-5 py-4 text-center">
                    <span class="font-bold {{ $diasColor }}">{{ $dias }}</span>
                    <span class="text-xs text-slate-400 ml-0.5">días</span>
                </td>
                <td class="px-5 py-4 text-center text-xs text-slate-400">
                    {{ $c['ULTIMA_FACTURA'] ? \Carbon\Carbon::parse($c['ULTIMA_FACTURA'])->format('d/m/Y') : '—' }}
                </td>
                <td class="px-5 py-4 text-right">
                    <form method="POST" action="{{ route('clientes-huerfanos.reclamar', $c['NIT']) }}"
                          onsubmit="return confirm('¿Tomar a {{ addslashes($c['RAZON_SOCIAL']) }} para tu cartera?')">
                        @csrf
                        <input type="hidden" name="razon_social" value="{{ $c['RAZON_SOCIAL'] }}">
                        <input type="hidden" name="ciudad" value="{{ $c['CIUDAD'] ?? '' }}">
                        <button type="submit"
                                class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-blue-600 text-white text-xs font-semibold hover:bg-blue-700 transition-colors">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
                            </svg>
                            Tomar cliente
                        </button>
                    </form>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</x-ui.card>
@endif

</x-layouts.app>
