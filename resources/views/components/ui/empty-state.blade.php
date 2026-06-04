@props(['icon' => 'search', 'title', 'description' => null])

<div class="flex flex-col items-center justify-center py-16 text-center">
    <div class="w-14 h-14 rounded-2xl bg-slate-100 flex items-center justify-center mb-4">
        <x-ui.icon :name="$icon" class="w-7 h-7 text-slate-400"/>
    </div>
    <h3 class="text-sm font-semibold text-slate-900">{{ $title }}</h3>
    @if($description)
        <p class="mt-1 text-sm text-slate-500 max-w-xs">{{ $description }}</p>
    @endif
    @if(isset($action))
        <div class="mt-4">{{ $action }}</div>
    @endif
</div>
