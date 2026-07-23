@if ($paginator->hasPages())
<nav role="navigation" aria-label="Pagination" class="flex items-center justify-between mt-6 px-1">

    {{-- Results summary --}}
    <div class="text-sm text-slate-400">
        عرض
        <span class="font-semibold text-white">{{ $paginator->firstItem() }}</span>
        -
        <span class="font-semibold text-white">{{ $paginator->lastItem() }}</span>
        من
        <span class="font-semibold text-white">{{ $paginator->total() }}</span>
        نتيجة
    </div>

    {{-- Pagination Links --}}
    <div class="flex items-center gap-1">

        {{-- Previous Page --}}
        @if ($paginator->onFirstPage())
            <span class="inline-flex items-center justify-center w-9 h-9 rounded-lg bg-slate-800 text-slate-600 cursor-not-allowed border border-slate-700 text-sm">
                <i class="fas fa-chevron-right"></i>
            </span>
        @else
            <a href="{{ $paginator->previousPageUrl() }}"
               class="inline-flex items-center justify-center w-9 h-9 rounded-lg bg-slate-800 text-slate-400 border border-slate-700 hover:bg-slate-700 hover:text-white transition-all duration-150 text-sm"
               aria-label="الصفحة السابقة">
                <i class="fas fa-chevron-right"></i>
            </a>
        @endif

        {{-- Page Numbers --}}
        @foreach ($elements as $element)
            {{-- Dots --}}
            @if (is_string($element))
                <span class="inline-flex items-center justify-center w-9 h-9 text-slate-500 text-sm">{{ $element }}</span>
            @endif

            {{-- Links --}}
            @if (is_array($element))
                @foreach ($element as $page => $url)
                    @if ($page == $paginator->currentPage())
                        <span aria-current="page"
                              class="inline-flex items-center justify-center w-9 h-9 rounded-lg bg-amber-500 text-slate-900 font-bold border border-amber-400 text-sm shadow-lg shadow-amber-500/20">
                            {{ $page }}
                        </span>
                    @else
                        <a href="{{ $url }}"
                           class="inline-flex items-center justify-center w-9 h-9 rounded-lg bg-slate-800 text-slate-400 border border-slate-700 hover:bg-slate-700 hover:text-white hover:border-slate-600 transition-all duration-150 text-sm"
                           aria-label="الصفحة {{ $page }}">
                            {{ $page }}
                        </a>
                    @endif
                @endforeach
            @endif
        @endforeach

        {{-- Next Page --}}
        @if ($paginator->hasMorePages())
            <a href="{{ $paginator->nextPageUrl() }}"
               class="inline-flex items-center justify-center w-9 h-9 rounded-lg bg-slate-800 text-slate-400 border border-slate-700 hover:bg-slate-700 hover:text-white transition-all duration-150 text-sm"
               aria-label="الصفحة التالية">
                <i class="fas fa-chevron-left"></i>
            </a>
        @else
            <span class="inline-flex items-center justify-center w-9 h-9 rounded-lg bg-slate-800 text-slate-600 cursor-not-allowed border border-slate-700 text-sm">
                <i class="fas fa-chevron-left"></i>
            </span>
        @endif

    </div>
</nav>
@endif
