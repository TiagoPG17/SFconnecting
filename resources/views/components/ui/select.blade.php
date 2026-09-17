@props(['label' => null, 'error' => null, 'placeholder' => 'Seleccionar...'])

@php
$xError = $attributes->get('x-error');
$borderClass = $xError
    ? ''
    : ($error ? 'border-red-400 focus:ring-red-400' : 'border-slate-300 focus:ring-blue-500');
$id = $attributes->get('id') ?: 'field-' . ($attributes->get('name')
        ? \Illuminate\Support\Str::slug($attributes->get('name'))
        : \Illuminate\Support\Str::random(6));
$errorId = $id . '-error';
@endphp

<div class="space-y-1.5">
    @if($label)
    <label for="{{ $id }}" class="block text-sm font-medium text-slate-700">{{ $label }}</label>
    @endif

    <select {{ $attributes->except('x-error')->merge([
        'id' => $id,
        'class' => 'block w-full rounded-lg border bg-white px-3 py-2 text-sm text-slate-900
                    focus:outline-none focus:ring-2 focus:border-transparent
                    disabled:bg-slate-50
                    ' . $borderClass
    ]) }}
    @if($error || $xError) aria-describedby="{{ $errorId }}" @endif
    @if($xError) x-bind:class="{{ $xError }} ? 'border-red-400 focus:ring-red-400' : 'border-slate-300 focus:ring-blue-500'" @endif
    >
        @if($placeholder)
            <option value="">{{ $placeholder }}</option>
        @endif
        {{ $slot }}
    </select>

    @if($error)
        <p id="{{ $errorId }}" role="alert" class="text-xs text-red-600">{{ $error }}</p>
    @elseif($xError)
        <p id="{{ $errorId }}" role="alert" x-show="{{ $xError }}" x-text="{{ $xError }}?.[0]" class="text-xs text-red-600" style="display:none"></p>
    @endif
</div>
