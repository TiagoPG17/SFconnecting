<x-layouts.app title="Reportes">

    {{-- Selector de reporte --}}
    <div class="flex gap-2 mb-6 flex-wrap">
        @foreach(['forecast' => 'Forecast', 'prospectos' => 'Prospectos', 'negocios' => 'Negocios', 'conversion' => 'Conversión'] as $key => $label)
        <a
            href="{{ request()->fullUrlWithQuery(['tipo' => $key]) }}"
            class="px-4 py-2 rounded-lg text-sm font-medium transition-colors
                {{ $tipo === $key
                    ? 'bg-blue-600 text-white'
                    : 'bg-white border border-slate-200 text-slate-700 hover:bg-slate-50' }}"
        >
            {{ $label }}
        </a>
        @endforeach
    </div>

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
                        <option value="{{ $m }}" {{ request('mes', now()->month) == $m ? 'selected' : '' }}>
                            {{ \Carbon\Carbon::create()->month($m)->locale('es')->monthName }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs font-medium text-slate-500 mb-1">Año</label>
                <select x-model="anio" @change="goFilter()"
                    class="px-3 py-2 text-sm rounded-lg border border-slate-300 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    @foreach(range(now()->year - 2, now()->year + 2) as $y)
                        <option value="{{ $y }}" {{ request('anio', now()->year) == $y ? 'selected' : '' }}>{{ $y }}</option>
                    @endforeach
                </select>
            </div>
        </div>
    </x-ui.card>

    {{-- Contenido según tipo --}}
    @if($tipo === 'forecast')
        @include('reportes.partials.forecast')
    @elseif($tipo === 'prospectos')
        @include('reportes.partials.prospectos')
    @elseif($tipo === 'negocios')
        @include('reportes.partials.negocios')
    @elseif($tipo === 'conversion')
        @include('reportes.partials.conversion')
    @endif

</x-layouts.app>
