@props(['label' => null, 'error' => null, 'placeholder' => 'Seleccionar...'])

<div class="space-y-1.5">
    @if($label)
    <label class="block text-sm font-medium text-slate-700">{{ $label }}</label>
    @endif

    <select {{ $attributes->merge([
        'class' => 'block w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm text-slate-900
                    focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent
                    disabled:bg-slate-50'
    ]) }}>
        @if($placeholder)
            <option value="">{{ $placeholder }}</option>
        @endif
        {{ $slot }}
    </select>

    @if($error)
        <p class="text-xs text-red-600">{{ $error }}</p>
    @endif
</div>
