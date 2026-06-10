@props(['label' => null, 'error' => null, 'hint' => null])

@php
$xError = $attributes->get('x-error');
// Si hay x-error, omitir border estático — Alpine lo maneja
$borderClass = $xError
    ? ''
    : ($error ? 'border-red-400 focus:ring-red-400' : 'border-slate-300 focus:ring-blue-500');
@endphp

<div class="space-y-1.5">
    @if($label)
    <label class="block text-sm font-medium text-slate-700">
        {{ $label }}
        @if($attributes->has('required'))
            <span class="text-red-500 ml-0.5">*</span>
        @endif
    </label>
    @endif

    <input {{ $attributes->except('x-error')->merge([
        'class' => 'block w-full rounded-lg border bg-white px-3 py-2 text-sm text-slate-900
                    placeholder:text-slate-400
                    focus:outline-none focus:ring-2 focus:border-transparent
                    disabled:bg-slate-50 disabled:text-slate-500
                    ' . $borderClass
    ]) }}
    @if($xError) x-bind:class="{{ $xError }} ? 'border-red-400 focus:ring-red-400' : 'border-slate-300 focus:ring-blue-500'" @endif
    >

    @if($error)
        <p class="text-xs text-red-600">{{ $error }}</p>
    @elseif($xError)
        <p x-show="{{ $xError }}" x-text="{{ $xError }}?.[0]" class="text-xs text-red-600" style="display:none"></p>
    @elseif($hint)
        <p class="text-xs text-slate-500">{{ $hint }}</p>
    @endif
</div>
