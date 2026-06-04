@if ($paginator->hasPages())
<nav class="flex items-center gap-2" aria-label="Paginación simple">
    @if ($paginator->onFirstPage())
        <span class="inline-flex items-center px-2.5 py-1.5 text-xs text-slate-300 rounded-lg cursor-not-allowed">‹ Anterior</span>
    @else
        <a href="{{ $paginator->previousPageUrl() }}"
           class="inline-flex items-center px-2.5 py-1.5 text-xs text-slate-600 rounded-lg hover:bg-slate-100 transition-colors">
            ‹ Anterior
        </a>
    @endif

    @if ($paginator->hasMorePages())
        <a href="{{ $paginator->nextPageUrl() }}"
           class="inline-flex items-center px-2.5 py-1.5 text-xs text-slate-600 rounded-lg hover:bg-slate-100 transition-colors">
            Siguiente ›
        </a>
    @else
        <span class="inline-flex items-center px-2.5 py-1.5 text-xs text-slate-300 rounded-lg cursor-not-allowed">Siguiente ›</span>
    @endif
</nav>
@endif
