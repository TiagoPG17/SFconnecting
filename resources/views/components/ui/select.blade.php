@props(['label' => null, 'error' => null, 'placeholder' => 'Seleccionar...'])

@php
$xError = $attributes->get('x-error');
$borderClass = $xError
    ? ''
    : ($error ? 'border-red-400 focus:ring-red-400' : 'border-slate-300 focus:ring-blue-500');
@endphp

<div class="space-y-1.5">
    @if($label)
    <label class="block text-sm font-medium text-slate-700">{{ $label }}</label>
    @endif

    <select {{ $attributes->except('x-error')->merge([
        'class' => 'block w-full rounded-lg border bg-white px-3 py-2 text-sm text-slate-900
                    focus:outline-none focus:ring-2 focus:border-transparent
                    disabled:bg-slate-50
                    ' . $borderClass
    ]) }}
    @if($xError) x-bind:class="{{ $xError }} ? 'border-red-400 focus:ring-red-400' : 'border-slate-300 focus:ring-blue-500'" @endif
    >
        @if($placeholder)
            <option value="">{{ $placeholder }}</option>
        @endif
        {{ $slot }}
    </select>

    @if($error)
        <p class="text-xs text-red-600">{{ $error }}</p>
    @elseif($xError)
        <p x-show="{{ $xError }}" x-text="{{ $xError }}?.[0]" class="text-xs text-red-600" style="display:none"></p>
    @endif
</div>
