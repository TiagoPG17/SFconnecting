@if ($paginator->hasPages())
<nav class="flex items-center justify-between" aria-label="Paginación">
    <p class="text-xs text-slate-500">
        Mostrando {{ $paginator->firstItem() }} – {{ $paginator->lastItem() }}
        de {{ $paginator->total() }} resultados
    </p>

    <div class="flex items-center gap-1">
        {{-- Anterior --}}
        @if ($paginator->onFirstPage())
            <span class="inline-flex items-center px-2.5 py-1.5 text-xs text-slate-300 rounded-lg cursor-not-allowed select-none">
                ‹ Anterior
            </span>
        @else
            <a href="{{ $paginator->previousPageUrl() }}"
               class="inline-flex items-center px-2.5 py-1.5 text-xs text-slate-600 rounded-lg hover:bg-slate-100 transition-colors">
                ‹ Anterior
            </a>
        @endif

        {{-- Páginas --}}
        @foreach ($elements as $element)
            @if (is_string($element))
                <span class="inline-flex items-center px-2 py-1.5 text-xs text-slate-400 select-none">…</span>
            @endif

            @if (is_array($element))
                @foreach ($element as $page => $url)
                    @if ($page == $paginator->currentPage())
                        <span class="inline-flex items-center justify-center w-7 h-7 text-xs font-semibold bg-blue-600 text-white rounded-lg select-none">
                            {{ $page }}
                        </span>
                    @else
                        <a href="{{ $url }}"
                           class="inline-flex items-center justify-center w-7 h-7 text-xs text-slate-600 rounded-lg hover:bg-slate-100 transition-colors">
                            {{ $page }}
                        </a>
                    @endif
                @endforeach
            @endif
        @endforeach

        {{-- Siguiente --}}
        @if ($paginator->hasMorePages())
            <a href="{{ $paginator->nextPageUrl() }}"
               class="inline-flex items-center px-2.5 py-1.5 text-xs text-slate-600 rounded-lg hover:bg-slate-100 transition-colors">
                Siguiente ›
            </a>
        @else
            <span class="inline-flex items-center px-2.5 py-1.5 text-xs text-slate-300 rounded-lg cursor-not-allowed select-none">
                Siguiente ›
            </span>
        @endif
    </div>
</nav>
@endif
